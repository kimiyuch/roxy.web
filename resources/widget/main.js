// Roxy's widget: <script src="https://roxy.kimiyu.ch/widget.js" data-site="KEY" defer></script>
//
// Visitors get nothing: no button, no recording, no cookies. Reviewers sign in through the
// magic link from their email (it lands on the site with #roxy=CODE), and from then on the
// token in localStorage shows them the button. `?feedback` on any page asks for a new link.
import { createApi } from './api.js';
import { captured, installCapture, pauseCapture } from './capture.js';
import { icon } from './icons.js';
import { prepareImage, takeScreenshot } from './images.js';
import { pickElement } from './picker.js';
import { describe } from './selector.js';
import { t } from './strings.js';
import css from './styles.css?inline';

const VERSION = __ROXY_VERSION__;
const MAX_SHOTS = 5;

const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => `&#${char.charCodeAt(0)};`);
const plural = (count, one, many) => `${count} ${count === 1 ? one : many}`;

function boot() {
    if (window.__roxy) return;

    const script = document.currentScript || document.querySelector('script[src*="/widget.js"][data-site]');
    const key = script?.dataset.site;
    if (!key) return;

    const base = new URL(script.src).origin;
    const api = createApi(base, key);
    window.__roxy = { version: VERSION };

    const code = takeCode();
    if (code || api.token.get()) installCapture((url) => String(url).startsWith(base));

    const start = () => run(api, base, code).catch(() => {});
    document.body ? start() : addEventListener('DOMContentLoaded', start, { once: true });
}

/** The one-time code from the magic link, removed from the address right away. */
function takeCode() {
    const match = location.hash.match(/(?:^#|&)roxy=([\w-]+)/);
    if (!match) return null;

    const rest = location.hash.replace(match[0], '').replace(/^[#&]+/, '');
    history.replaceState(history.state, '', location.pathname + location.search + (rest ? `#${rest}` : ''));

    return match[1];
}

async function run(api, base, code) {
    // Only on the page once there's something to show: visitors without a token get no element at all
    let mounted;
    const widget = { signIn: (...args) => (mounted ??= mount(base)).signIn(...args), ready: (...args) => (mounted ??= mount(base)).ready(...args) };

    if (code) {
        try {
            const { token } = await api.exchange(code);
            api.token.set(token);
        } catch (error) {
            return widget.signIn(api, error.status === 422 ? t('linkExpired') : error.message);
        }
    }

    if (api.token.get()) {
        let me;
        try {
            me = await api.me();
        } catch (error) {
            if (error.status === 401) return new URLSearchParams(location.search).has('feedback') && widget.signIn(api);
            return;
        }

        return widget.ready(api, me, { open: Boolean(code) });
    }

    if (new URLSearchParams(location.search).has('feedback')) widget.signIn(api);
}

function mount(base) {
    const host = document.createElement('roxy-widget');
    host.style.setProperty('all', 'initial', 'important');
    const shadow = host.attachShadow({ mode: 'open' });
    shadow.innerHTML = `<style>${css}</style><div class="layer"></div><div class="root"></div>`;
    document.body.append(host);

    const root = shadow.querySelector('.root');
    const layer = shadow.querySelector('.layer');
    const logo = `${base}/images/roxy.webp`;

    const head = (title, subtitle, actions = '') => `
        <div class="head">
            <img src="${logo}" alt="">
            <div class="head-text">
                <div class="title">${esc(title)}</div>
                ${subtitle ? `<div class="subtitle">${esc(subtitle)}</div>` : ''}
            </div>
            ${actions}
        </div>`;

    /** No token: ask for an email address and send a fresh link back to this page. */
    function signIn(api, message) {
        root.innerHTML = `
            <form class="panel" novalidate>
                ${head(t('signInTitle'), t('signInSubtitle'))}
                <div class="body">
                    <p class="lead">${esc(message || t('signInLead'))}</p>
                    <input class="field" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                    <div class="error" hidden></div>
                    <button class="button wide" type="submit">${esc(t('emailLink'))}</button>
                </div>
            </form>`;

        const form = root.querySelector('form');
        const error = form.querySelector('.error');
        form.email.focus();
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const email = form.email.value.trim();
            if (!/^\S+@\S+\.\S+$/.test(email)) {
                error.textContent = t('invalidEmail');
                error.hidden = false;
                return;
            }

            form.querySelector('button').disabled = true;
            try {
                await api.requestLink(email);
                form.querySelector('.body').innerHTML = `
                    <div class="done">${icon('check')}<strong>${esc(t('checkInbox'))}</strong>
                    <p>${esc(t('linkOnItsWay', email))}</p></div>`;
            } catch (failure) {
                error.textContent = failure.message;
                error.hidden = false;
                form.querySelector('button').disabled = false;
            }
        });
    }

    /** Signed in: the button, and the panel behind it. */
    function ready(api, me, { open }) {
        const settings = me.site?.settings ?? {};
        if (settings.color) host.style.setProperty('--accent', settings.color);
        root.classList.toggle('left', settings.position === 'bottom-left');

        const reviewer = me.reviewer.name || me.reviewer.email;
        const state = { open: false, openHere: [], shots: [], element: null, taking: null, sending: false, sent: false, error: null };

        root.innerHTML = `
            <div class="panel" role="dialog" aria-label="${esc(t('panelTitle'))}" hidden></div>
            <button class="launcher" type="button" aria-expanded="false">
                <span class="sr-only">${esc(t('feedback'))}</span>${icon('chat')}<span class="badge" hidden></span>
            </button>`;

        const panel = root.querySelector('.panel');
        const launcher = root.querySelector('.launcher');
        const badge = launcher.querySelector('.badge');

        launcher.addEventListener('click', () => (state.open ? close() : show()));
        shadow.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && state.open) close();
        });

        refreshOpenHere();
        if (open) show();

        async function refreshOpenHere() {
            try {
                state.openHere = (await api.openHere()).data;
            } catch {
                state.openHere = [];
            }
            badge.hidden = !state.openHere.length;
            badge.textContent = state.openHere.length;
            if (state.open && !state.sent) renderOpenHere();
        }

        function show() {
            state.open = true;
            launcher.setAttribute('aria-expanded', 'true');
            launcher.innerHTML = `<span class="sr-only">${esc(t('close'))}</span>${icon('close')}`;
            panel.hidden = false;
            if (state.sent) reset();
            renderPanel();
            panel.querySelector('textarea')?.focus();
        }

        function close() {
            state.open = false;
            launcher.setAttribute('aria-expanded', 'false');
            launcher.innerHTML = `<span class="sr-only">${esc(t('feedback'))}</span>${icon('chat')}`;
            launcher.append(badge);
            panel.hidden = true;
            launcher.focus();
        }

        function reset() {
            state.shots.forEach((shot) => URL.revokeObjectURL(shot.url));
            Object.assign(state, { shots: [], element: null, picked: null, sent: false, error: null, draft: '' });
        }

        /**
         * The window as the reviewer sees it right now, scrolled or not; replaces the previous one.
         * The picked element is measured again, so its outline matches this screenshot.
         */
        function screenshot() {
            if (state.taking) return;
            const taking = (async () => {
                pauseCapture(true);
                try {
                    const result = await takeScreenshot(base, VERSION, host);
                    if (state.picked?.isConnected) state.element = describe(state.picked);
                    state.shots.filter((shot) => shot.kind === 'auto').forEach((shot) => URL.revokeObjectURL(shot.url));
                    state.shots = state.shots.filter((shot) => shot.kind !== 'auto');
                    state.shots.unshift({ kind: 'auto', blob: result.blob, url: URL.createObjectURL(result.blob), scroll: { x: scrollX, y: scrollY } });
                } catch {
                    showError(t('screenshotFailed'));
                } finally {
                    pauseCapture(false);
                }
            })();

            state.taking = taking;
            renderAttachments();
            taking.finally(() => {
                if (state.taking === taking) state.taking = null;
                renderAttachments();
            });
        }

        function renderPanel() {
            if (state.sent) {
                panel.innerHTML = `
                    ${head(t('thanks'), t('signedInAs', reviewer))}
                    <div class="done">${icon('check')}<strong>${esc(t('passedOn'))}</strong>
                    <p>${esc(t('seeItHere'))}</p></div>
                    <div class="foot"><button class="button" type="button" data-action="again">${esc(t('addMore'))}</button></div>`;
                panel.querySelector('[data-action="again"]').addEventListener('click', () => {
                    reset();
                    renderPanel();
                    panel.querySelector('textarea').focus();
                });
                return;
            }

            panel.innerHTML = `
                ${head(t('panelTitle'), t('signedInAs', reviewer))}
                <div class="body">
                    <div class="open-here" hidden></div>
                    <label class="sr-only" for="roxy-body">${esc(t('whatShouldChange'))}</label>
                    <textarea id="roxy-body" rows="4" placeholder="${esc(t('whatShouldChange'))}"></textarea>
                    <div class="tools">
                        <button class="tool" type="button" data-action="screenshot">${icon('camera')}<span></span></button>
                        <button class="tool" type="button" data-action="image">${icon('photo')}${esc(t('uploadImage'))}</button>
                        <input type="file" accept="image/png,image/jpeg,image/webp" multiple hidden>
                    </div>
                    <div class="attachments"></div>
                    <div class="error" hidden></div>
                </div>
                <div class="foot">
                    <button class="ghost" type="button" data-action="pick">${icon('pick')}${esc(t('pointAtIt'))}</button>
                    <button class="button" type="button" data-action="send">${esc(t('send'))}</button>
                </div>`;

            const textarea = panel.querySelector('textarea');
            textarea.value = state.draft ?? '';
            textarea.addEventListener('input', () => {
                state.draft = textarea.value;
                renderSend();
            });
            textarea.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) send();
            });
            textarea.addEventListener('paste', (event) => {
                const files = [...(event.clipboardData?.files ?? [])].filter((file) => file.type.startsWith('image/'));
                if (files.length) {
                    event.preventDefault();
                    addImages(files);
                }
            });

            panel.addEventListener('dragover', (event) => event.preventDefault());
            panel.addEventListener('drop', (event) => {
                event.preventDefault();
                addImages([...(event.dataTransfer?.files ?? [])]);
            });

            const input = panel.querySelector('input[type="file"]');
            input.addEventListener('change', () => {
                addImages([...input.files]);
                input.value = '';
            });

            panel.querySelector('[data-action="image"]').addEventListener('click', () => input.click());
            panel.querySelector('[data-action="screenshot"]').addEventListener('click', screenshot);
            panel.querySelector('[data-action="pick"]').addEventListener('click', pick);
            panel.querySelector('[data-action="send"]').addEventListener('click', send);

            renderOpenHere();
            renderAttachments();
            renderSend();
        }

        function renderOpenHere() {
            const box = panel.querySelector('.open-here');
            if (!box) return;
            const items = state.openHere;
            box.hidden = !items.length;
            if (!items.length) return;

            const first = items[0];
            box.innerHTML = `
                <button class="open-toggle" type="button" aria-expanded="false">
                    ${icon('chat')}
                    <span>${esc(items.length === 1 ? t('openHereOne', first.body) : t('openHereMany', items.length))}</span>
                    ${icon('chevron', 'icon chevron')}
                </button>
                <ul class="open-list">
                    ${items.map((item) => `<li>${esc(item.body)} <span class="who">· ${esc(item.mine ? t('you') : item.author || t('someone'))}</span></li>`).join('')}
                </ul>`;
            box.querySelector('.open-toggle').addEventListener('click', (event) => {
                box.classList.toggle('expanded');
                event.currentTarget.setAttribute('aria-expanded', box.classList.contains('expanded'));
            });
        }

        function renderAttachments() {
            const box = panel.querySelector('.attachments');
            if (!box) return;

            const auto = state.shots.some((shot) => shot.kind === 'auto');
            const button = panel.querySelector('[data-action="screenshot"]');
            button.disabled = Boolean(state.taking);
            button.querySelector('span').textContent = state.taking ? t('takingScreenshot') : auto ? t('retakeScreenshot') : t('takeScreenshot');

            const shots = state.shots
                .map((shot, i) => `
                    <div class="shot">
                        <img src="${shot.url}" alt="${esc(shot.kind === 'auto' ? t('screenshot') : t('yourImage'))}">
                        <button class="remove" type="button" data-remove="${i}" title="${esc(t('remove'))}">${icon('close')}<span class="sr-only">${esc(t('remove'))}</span></button>
                    </div>`)
                .join('');
            const taking = state.taking && !state.shots.some((shot) => shot.kind === 'auto') ? `<div class="shot taking" aria-label="${esc(t('takingScreenshot'))}"></div>` : '';

            const element = state.element
                ? `<div class="fact element">${icon('pick')}<code title="${esc(state.element.selector)}">${esc(state.element.selector.split(' > ').pop())}</code>
                   <button class="clear" type="button" data-action="clear-element" title="${esc(t('forgetElement'))}">${icon('close')}<span class="sr-only">${esc(t('forgetElement'))}</span></button></div>`
                : '';
            box.hidden = !(shots || taking || element);
            box.innerHTML = `
                ${shots || taking ? `<div class="shots">${taking}${shots}</div>` : ''}
                ${element ? `<div class="facts" style="margin-top:${shots || taking ? 8 : 0}px">${element}</div>` : ''}`;

            box.querySelectorAll('[data-remove]').forEach((button) =>
                button.addEventListener('click', () => {
                    const [removed] = state.shots.splice(Number(button.dataset.remove), 1);
                    URL.revokeObjectURL(removed.url);
                    renderAttachments();
                }),
            );
            box.querySelector('[data-action="clear-element"]')?.addEventListener('click', () => {
                state.element = null;
                state.picked = null;
                renderAttachments();
            });
        }

        function renderSend() {
            const button = panel.querySelector('[data-action="send"]');
            if (button) {
                button.disabled = state.sending || !(state.draft ?? '').trim();
                button.textContent = state.sending ? t('sending') : t('send');
            }
        }

        function showError(message) {
            const box = panel.querySelector('.error');
            if (!box) return;
            box.textContent = message ?? '';
            box.hidden = !message;
        }

        async function addImages(files) {
            for (const file of files) {
                if (state.shots.length >= MAX_SHOTS) {
                    showError(t('maxImages', MAX_SHOTS));
                    break;
                }
                const prepared = await prepareImage(file);
                if (prepared) state.shots.push({ kind: 'manual', blob: prepared, url: URL.createObjectURL(prepared) });
            }
            renderAttachments();
        }

        async function pick() {
            panel.hidden = true;
            launcher.hidden = true;
            const element = await pickElement(host, layer);
            panel.hidden = false;
            launcher.hidden = false;
            if (element) {
                state.picked = element;
                state.element = describe(element);
                // A screenshot taken before may show another part of the page now
                if (state.shots.some((shot) => shot.kind === 'auto')) screenshot();
            }
            renderAttachments();
            panel.querySelector('textarea')?.focus();
        }

        async function send() {
            const body = (state.draft ?? '').trim();
            if (!body || state.sending) return;

            state.sending = true;
            showError(null);
            renderSend();

            try {
                await state.taking;
                const auto = state.shots.find((shot) => shot.kind === 'auto');
                const shots = [...(auto ? [auto] : []), ...state.shots.filter((shot) => shot.kind !== 'auto')].slice(0, MAX_SHOTS);
                const { console: errors, network } = captured();

                const form = new FormData();
                form.append('body', body);
                form.append('url', location.href.split('#')[0]);
                form.append('context', JSON.stringify({
                    title: document.title,
                    viewport: { width: document.documentElement.clientWidth || innerWidth, height: document.documentElement.clientHeight || innerHeight },
                    screen: { width: screen.width, height: screen.height },
                    dpr: devicePixelRatio,
                    user_agent: navigator.userAgent,
                    language: navigator.language,
                    scroll: auto?.scroll ?? { x: scrollX, y: scrollY },
                }));
                if (state.element) form.append('element', JSON.stringify(state.element));
                if (errors.length) form.append('console', JSON.stringify(errors));
                if (network.length) form.append('network', JSON.stringify(network));
                shots.forEach((shot, i) => {
                    form.append('screenshots[]', shot.blob, shot.kind === 'auto' ? 'screenshot.jpg' : `image-${i}.${shot.blob.type.split('/')[1] || 'jpg'}`);
                    form.append('screenshot_kinds[]', shot.kind);
                });

                await api.send(form);
                state.sent = true;
                renderPanel();
                refreshOpenHere();
            } catch (error) {
                if (error.status === 401) {
                    host.remove();
                    window.__roxy = null;
                    return;
                }
                showError(error.status === 429 ? t('tooMany') : error.message);
            } finally {
                state.sending = false;
                renderSend();
            }
        }
    }

    return { signIn, ready };
}

try {
    boot();
} catch {
    // Roxy must never break the page it's on.
}
