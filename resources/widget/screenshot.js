// Built as its own file (public/widget-screenshot.js) and only loaded when a reviewer
// opens the panel, so visitors never download modern-screenshot.
import { domToBlob } from 'modern-screenshot';

const MAX_EDGE = 2560;

/** A JPEG of what's in the viewport right now, without Roxy itself. */
window.__roxyScreenshot = async function (host) {
    // The visible area without scrollbars, the same box `element.rect` and the context's viewport use
    const width = document.documentElement.clientWidth || innerWidth;
    const height = document.documentElement.clientHeight || innerHeight;
    const scale = Math.min(devicePixelRatio || 1, 2, MAX_EDGE / Math.max(width, height));
    const background = getComputedStyle(document.body).backgroundColor;

    const blob = await domToBlob(document.documentElement, {
        width,
        height,
        scale,
        type: 'image/jpeg',
        quality: 0.85,
        backgroundColor: background && background !== 'rgba(0, 0, 0, 0)' ? background : '#ffffff',
        filter: (node) => node !== host,
        style: { transform: `translate(${-scrollX}px, ${-scrollY}px)`, transformOrigin: 'top left' },
        timeout: 15000,
    });

    return { blob, width: Math.round(width * scale), height: Math.round(height * scale) };
};
