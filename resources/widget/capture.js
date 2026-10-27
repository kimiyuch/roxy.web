// The last console errors and failed requests on the page. Only installed for signed-in
// reviewers (and on the way back from a magic link), never for other visitors.

const MAX = 20;
const errors = [];
const requests = [];
let paused = false;
let ignore = () => false;

const push = (list, entry) => {
    if (paused) return;
    list.push({ ...entry, at: new Date().toISOString() });
    if (list.length > MAX) list.shift();
};

const text = (value) => {
    if (value instanceof Error) return `${value.name}: ${value.message}`;
    if (typeof value === 'string') return value;
    try {
        return JSON.stringify(value);
    } catch {
        return String(value);
    }
};

const withoutQuery = (url) => String(url).split(/[?#]/)[0];

export function installCapture(isOwnRequest) {
    ignore = isOwnRequest;

    const error = console.error;
    console.error = (...args) => {
        push(errors, { level: 'error', message: args.map(text).join(' ').slice(0, 1000) });
        return error.apply(console, args);
    };

    addEventListener('error', (event) => {
        if (event.target && event.target !== window) {
            // A script, image or stylesheet that didn't load
            const url = event.target.src || event.target.href;
            if (url) push(requests, { method: 'GET', url: withoutQuery(url), error: `${event.target.localName} failed to load` });
            return;
        }
        push(errors, { level: 'error', message: event.message, source: event.filename ? `${event.filename}:${event.lineno}:${event.colno}` : undefined });
    }, true);

    addEventListener('unhandledrejection', (event) => {
        push(errors, { level: 'error', message: `Unhandled rejection: ${text(event.reason)}`.slice(0, 1000) });
    });

    const originalFetch = window.fetch;
    window.fetch = async function (input, init) {
        const url = input instanceof Request ? input.url : String(input);
        const method = (init?.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
        try {
            const response = await originalFetch.apply(this, arguments);
            if (response.status >= 400 && !ignore(url)) push(requests, { method, url: withoutQuery(new URL(url, location.href)), status: response.status });
            return response;
        } catch (failure) {
            if (!ignore(url)) push(requests, { method, url: withoutQuery(new URL(url, location.href)), error: text(failure) });
            throw failure;
        }
    };

    const open = XMLHttpRequest.prototype.open;
    const send = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) {
        this.__roxy = { method: String(method).toUpperCase(), url: withoutQuery(new URL(url, location.href)) };
        return open.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function () {
        this.addEventListener('loadend', () => {
            const request = this.__roxy;
            if (!request || ignore(request.url)) return;
            if (this.status >= 400) push(requests, { ...request, status: this.status });
            else if (this.status === 0) push(requests, { ...request, error: 'Network error' });
        });
        return send.apply(this, arguments);
    };
}

/** Taking a screenshot fetches fonts and images; its failures aren't the page's. */
export function pauseCapture(value) {
    paused = value;
}

export const captured = () => ({ console: [...errors], network: [...requests] });
