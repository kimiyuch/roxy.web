import { t } from './strings.js';

// Point at the problem: outline what's under the pointer, take what's clicked.
// The outline lives in Roxy's shadow root, so the page isn't touched.

export function pickElement(host, layer) {
    return new Promise((resolve) => {
        const box = document.createElement('div');
        box.className = 'pick-box';
        const hint = document.createElement('div');
        hint.className = 'pick-hint';
        hint.textContent = t('pickHint');
        layer.append(box, hint);

        let current = null;
        const ours = (event) => event.composedPath().includes(host);

        const show = (element) => {
            current = element;
            const rect = element.getBoundingClientRect();
            Object.assign(box.style, { display: 'block', left: `${rect.x - 3}px`, top: `${rect.y - 3}px`, width: `${rect.width + 6}px`, height: `${rect.height + 6}px` });
        };

        const move = (event) => {
            if (ours(event)) return;
            const element = document.elementFromPoint(event.clientX, event.clientY);
            if (element && element !== document.documentElement && element !== document.body) show(element);
        };

        const swallow = (event) => {
            if (ours(event)) return;
            event.preventDefault();
            event.stopPropagation();
        };

        const click = (event) => {
            if (ours(event)) return;
            swallow(event);
            const element = document.elementFromPoint(event.clientX, event.clientY) || current;
            done(element && element !== document.documentElement && element !== document.body ? element : null);
        };

        const key = (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                done(null);
            }
        };

        const options = { capture: true };
        const events = [['pointermove', move], ['pointerdown', swallow], ['pointerup', swallow], ['mousedown', swallow], ['mouseup', swallow], ['click', click], ['keydown', key]];
        events.forEach(([name, handler]) => addEventListener(name, handler, options));
        document.documentElement.style.setProperty('cursor', 'crosshair', 'important');

        function done(element) {
            events.forEach(([name, handler]) => removeEventListener(name, handler, options));
            document.documentElement.style.removeProperty('cursor');
            box.remove();
            hint.remove();
            resolve(element);
        }
    });
}
