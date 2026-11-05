// Built as its own file (public/widget-screenshot.js) and only loaded when a reviewer
// opens the panel, so visitors never download modern-screenshot.
import { domToBlob } from 'modern-screenshot';

const MAX_EDGE = 2560;

// The copy is serialized as XML inside an SVG, so every attribute name must be a valid XML name
// without a namespace prefix: Alpine's `@click`, `:id`, `x-on:click`, `x-bind:id` would break it.
// Real namespaced attributes (`xlink:href`, `xml:space` on SVG) carry a namespaceURI and are kept.
const XML_NAME = /^[A-Za-z_][\w.-]*$/;

function cleanAttributes(element) {
    for (const attr of [...element.attributes]) {
        if (!attr.namespaceURI && !XML_NAME.test(attr.name)) {
            element.removeAttribute(attr.name);
        }
    }
}

// Images the library couldn't inline (lazy ones not loaded yet, failed fetches) still point at a URL,
// which an SVG drawn as an image never loads. Hide them instead of leaving broken boxes, and drop
// <picture> sources, which would win over the inlined <img src>.
function settleImages(root) {
    root.querySelectorAll('picture > source').forEach((source) => source.remove());
    root.querySelectorAll('img').forEach((img) => {
        img.removeAttribute('loading');
        if (!(img.getAttribute('src') ?? '').startsWith('data:')) {
            img.removeAttribute('src');
            img.removeAttribute('srcset');
            img.style.visibility = 'hidden';
        }
    });
}

// The copy of <html> gets the viewport's size, so overflow on <html> or <body> (`overflow-y: scroll`,
// `overflow-x: hidden` with `height: 100%`) would clip it to the first screen, and the shift to the
// scroll position below would then show nothing. Let both overflow; the SVG crops to the viewport.
function unclip(element) {
    if (element.tagName === 'HTML' || element.tagName === 'BODY') {
        element.style.setProperty('overflow', 'visible', 'important');
    }
}

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
        // A margin, not a transform: a transform would make fixed elements (headers, cookie banners)
        // position against <html> instead of the viewport.
        style: { margin: `${-scrollY}px 0 0 ${-scrollX}px` },
        timeout: 15000,
        onCloneEachNode: (node) => {
            if (node.nodeType !== Node.ELEMENT_NODE) return;
            cleanAttributes(node);
            unclip(node);
        },
        onEmbedNode: (clone) => clone.nodeType === Node.ELEMENT_NODE && settleImages(clone),
    });

    return { blob, width: Math.round(width * scale), height: Math.round(height * scale) };
};
