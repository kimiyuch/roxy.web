# Deploy

`./deploy.sh` uploads the committed state to `~/www/roxy.kimiyu.ch` on Hostpoint (rsync over
SSH, like croc.web), runs the migrations and refreshes the caches. It never touches the
server's `.env`, `public/.htaccess`, `storage/` (the screenshots) or `database/database.sqlite`.

`public/.htaccess` is only uploaded when the server has none: after that it belongs to the server,
because the Hostpoint panel writes the PHP version into it ("Use php-fpm latest"). If the repo's
`public/.htaccess` changes (Laravel's rewrite rules, the 5-minute cache for `widget.js`), copy the
change into the server's file by hand.

Before deploying: `npm run build`, commit, tests green (deploy.sh checks all three).

## First time

1. **Hostpoint panel**
   - Domain `roxy.kimiyu.ch` with the document root `www/roxy.kimiyu.ch/public`, Let's Encrypt on.
   - PHP 8.4 for it, with Imagick (Hostpoint has it; check with `php -m | grep imagick`).
   - Mailbox `roxy@kimiyu.ch` (for invitations and new-feedback emails).
   - Cron job, every minute: `cd ~/www/roxy.kimiyu.ch && /path/to/php8.4 artisan schedule:run >/dev/null 2>&1`
     (it also sends the queued emails). The plain `php` on the server is older than 8.4; `./deploy.sh`
     finds PHP 8.4 and prints the exact cron command at the end.
2. **SSH key loaded**: `ssh-add --apple-use-keychain ~/.ssh/id_ed25519` (or whichever key Hostpoint knows).
3. `./deploy.sh`: uploads the code, then stops because there's no `.env` yet, and prints:
   ```sh
   scp .env.production.example pumutuxu@pumutuxu.myhostpoint.ch:/home/pumutuxu/www/roxy.kimiyu.ch/.env
   ssh pumutuxu@pumutuxu.myhostpoint.ch "cd /home/pumutuxu/www/roxy.kimiyu.ch && /path/to/php8.4 artisan key:generate --force"
   ```
   Then put the mailbox password into `MAIL_PASSWORD` in the server's `.env`.
4. `./deploy.sh` again: creates the SQLite database, migrates, caches.
5. Sign up at `https://roxy.kimiyu.ch/register`, or on the server:
   `php artisan roxy:user you@example.com --name=You`.
6. Agent token: `php artisan roxy:mcp-token you@example.com` on the server prints the `claude mcp add` command.

## Every time

`npm run build`, commit, `./deploy.sh`.
