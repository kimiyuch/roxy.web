const relative = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

/** "3 minutes ago", "yesterday", or a date once it's more than a week old. */
export function timeAgo(iso) {
    const seconds = Math.round((new Date(iso) - Date.now()) / 1000);
    const units = [
        ['minute', 60],
        ['hour', 3600],
        ['day', 86400],
    ];

    if (Math.abs(seconds) < 60) return 'just now';
    if (Math.abs(seconds) > 7 * 86400) return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

    const [unit, size] = units.findLast(([, size]) => Math.abs(seconds) >= size);
    return relative.format(Math.round(seconds / size), unit);
}

const dots = ['bg-galah-400', 'bg-amber-400', 'bg-emerald-500', 'bg-roxy-500', 'bg-violet-400', 'bg-orange-400', 'bg-teal-400'];

/** A colour dot per site that stays the same however the list is sorted. */
export function siteDot(id) {
    const hash = [...String(id)].reduce((sum, char) => (sum * 31 + char.charCodeAt(0)) >>> 0, 0);
    return dots[hash % dots.length];
}

/** "Safari 19", "Chrome 141", … from a user agent; null if it's something else. */
export function browserName(userAgent) {
    if (!userAgent) return null;

    // In this order: Edge also says Chrome, and Chrome also says Safari.
    for (const [name, pattern] of [
        ['Edge', /Edg\/(\d+)/],
        ['Firefox', /Firefox\/(\d+)/],
        ['Chrome', /Chrome\/(\d+)/],
        ['Safari', /Version\/(\d+).*Safari/],
    ]) {
        const match = userAgent.match(pattern);
        if (match) return `${name} ${match[1]}`;
    }

    return null;
}
