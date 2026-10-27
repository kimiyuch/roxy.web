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
