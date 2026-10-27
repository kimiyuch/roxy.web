import { t } from './strings.js';

// The widget API on Roxy's server. The token lives in the site's localStorage, per site key.

export function createApi(base, key) {
    const storageKey = `roxy:${key}`;
    const root = `${base}/api/widget/${key}`;

    const token = {
        get: () => {
            try {
                return localStorage.getItem(storageKey);
            } catch {
                return null;
            }
        },
        set: (value) => {
            try {
                value ? localStorage.setItem(storageKey, value) : localStorage.removeItem(storageKey);
            } catch {
                // Private mode or storage blocked: the reviewer signs in again next time.
            }
        },
    };

    async function request(method, path, body) {
        const headers = { Accept: 'application/json' };
        if (token.get()) headers.Authorization = `Bearer ${token.get()}`;
        if (body && !(body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }

        const response = await fetch(`${root}/${path}`, { method, headers, body, credentials: 'omit' });
        if (response.status === 401) token.set(null);

        const data = response.status === 204 || response.status === 202 ? null : await response.json().catch(() => null);
        if (!response.ok) {
            const error = new Error(response.status < 500 && data?.message ? data.message : t('failed'));
            error.status = response.status;
            throw error;
        }

        return data;
    }

    return {
        root,
        token,
        exchange: (code) => request('POST', 'session', { code }),
        requestLink: (email) => request('POST', 'link', { email, url: location.href.split('#')[0] }),
        me: () => request('GET', 'me'),
        signOut: () => request('DELETE', 'session').finally(() => token.set(null)),
        openHere: () => request('GET', `feedback?url=${encodeURIComponent(location.href.split('#')[0])}`),
        send: (form) => request('POST', 'feedback', form),
    };
}
