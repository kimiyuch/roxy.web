#!/bin/zsh
# Deploys the committed state of this repository to roxy.kimiyu.ch (Hostpoint) over SSH,
# using your own SSH key. Run from your Mac: ./deploy.sh
#
# Assets (dashboard and widget) are built locally and committed (npm run build). The server's
# .env, storage (screenshots!), caches and the SQLite database are never overwritten or deleted.
# First-time server setup: see docs/deploy.md.
set -euo pipefail
cd "$(dirname "$0")"

HOST=pumutuxu@pumutuxu.myhostpoint.ch
DEST=/home/pumutuxu/www/roxy.kimiyu.ch

# The server's plain `php` on the command line is older than the site's (set per domain in the
# Hostpoint panel), so artisan runs with PHP 8.4 explicitly. Override with ROXY_PHP=/path/to/php.
PHP_ON_SERVER=${ROXY_PHP:-$(ssh "$HOST" 'for p in php8.4 php84 /usr/local/php84/bin/php /opt/php84/bin/php php; do command -v "$p" >/dev/null 2>&1 && "$p" -r "exit(PHP_VERSION_ID >= 80401 ? 0 : 1);" 2>/dev/null && { command -v "$p"; exit 0; }; done; exit 1')} || {
  echo "No PHP 8.4 found on the server. Find it with: ssh $HOST 'ls -d /usr/local/php* /opt/php* 2>/dev/null; compgen -c php'"
  echo "then run: ROXY_PHP=/path/to/php8.4 ./deploy.sh"
  exit 1
}
echo "Using $PHP_ON_SERVER on the server."

if [[ -n $(git status --porcelain) ]]; then
  echo "You have uncommitted changes. Commit them first; only committed code is deployed."
  exit 1
fi
if [[ ! -f public/build/manifest.json || ! -f public/widget.js ]]; then
  echo "public/build or public/widget.js is missing. Run 'npm run build' and commit the result."
  exit 1
fi

echo "Running tests…"
php artisan test

STAGE=$(mktemp -d)
trap 'rm -rf "$STAGE"' EXIT
# mktemp creates the folder as 700; rsync would copy that onto the site folder and lock out the web server.
chmod 755 "$STAGE"

echo "Preparing $(git rev-parse --short HEAD)…"
git archive HEAD | tar -x -C "$STAGE"
composer install --working-dir="$STAGE" --no-interaction --no-progress --no-dev --optimize-autoloader --quiet

echo "Uploading…"
rsync -az --delete \
  --exclude '/.github' --exclude '/node_modules' --exclude '/tests' --exclude '/artwork' \
  --exclude '/.env' --exclude '/public/hot' --exclude '/deploy.sh' \
  --filter 'protect /storage/**' \
  --filter 'protect /bootstrap/cache/*.php' \
  --filter 'protect /database/database.sqlite' \
  "$STAGE/" "$HOST:$DEST/"

if ! ssh "$HOST" "test -f '$DEST/.env'"; then
  echo
  echo "The code is uploaded, but the server has no .env yet. First-time setup (docs/deploy.md):"
  echo "  scp .env.production.example $HOST:$DEST/.env"
  echo "  ssh $HOST \"cd $DEST && $PHP_ON_SERVER artisan key:generate --force\""
  echo "  then fill in MAIL_PASSWORD in $DEST/.env and run ./deploy.sh again."
  exit 1
fi

echo "Migrating and refreshing caches…"
ssh "$HOST" "cd '$DEST' && touch database/database.sqlite && $PHP_ON_SERVER artisan migrate --force && $PHP_ON_SERVER artisan optimize:clear >/dev/null && $PHP_ON_SERVER artisan optimize >/dev/null"

echo "Deployed $(git rev-parse --short HEAD) to https://roxy.kimiyu.ch"
echo "Cron job (every minute): cd $DEST && $PHP_ON_SERVER artisan schedule:run >/dev/null 2>&1"
