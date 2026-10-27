// A CSS selector that finds the element again: an id if it's unique, otherwise a
// short path of tags, a few classes and :nth-of-type where siblings look alike.

// Utility classes (Tailwind and friends) say nothing about what an element is and change
// often, so they're left out; so are state classes and generated ones.
const utility = /^-?(text|font|bg|flex|grid|items|justify|gap|space|rounded|shadow|ring|border|w|h|min|max|p[xytrbl]?|m[xytrbl]?|tracking|leading|block|inline|hidden|relative|absolute|fixed|sticky|overflow|z|top|left|right|bottom|inset|opacity|transition|duration|ease|cursor|select|sr|object|aspect|col|row|order|self|place|content|truncate|line|whitespace|break|underline|decoration|uppercase|lowercase|capitalize|italic|antialiased|outline|fill|stroke|size|scroll|snap|isolate|divide|backdrop|blur|from|via|to|translate|rotate|scale|origin|animate|pointer|resize|list|table|float|clear|container|prose|shrink|grow|basis|visible|invisible|static|contents|align|indent|tabular|lining|ordinal|slashed|diagonal|normal|not|group|peer|dark|motion)(-|$)/;
const usable = (name) =>
    name && name.length < 40 && !/^\d|[^\w-]|\d/.test(name) && !utility.test(name) && !/^(is|has)-|active|hover|focus|open|show|selected/.test(name);

function part(element) {
    let selector = element.localName;
    const classes = [...element.classList].filter(usable).slice(0, 2);
    if (classes.length) selector += '.' + classes.map(CSS.escape).join('.');

    const parent = element.parentElement;
    if (parent) {
        const alike = [...parent.children].filter((sibling) => sibling.localName === element.localName);
        if (alike.length > 1 && [...parent.children].filter((sibling) => sibling.matches(selector)).length > 1) {
            selector += `:nth-of-type(${alike.indexOf(element) + 1})`;
        }
    }

    return selector;
}

export function selectorFor(element) {
    const unique = (selector) => {
        try {
            return document.querySelectorAll(selector).length === 1;
        } catch {
            return false;
        }
    };

    if (element.id && usable(element.id) && unique(`#${CSS.escape(element.id)}`)) return `#${CSS.escape(element.id)}`;

    // Walk up until the path is unique and anchored somewhere an agent can find in the
    // code: an element with an id, a landmark, or at least four steps of context.
    const landmark = /^(main|header|nav|footer|aside|section|article|form|dialog)$/;
    const parts = [];
    for (let node = element; node && node !== document.documentElement; node = node.parentElement) {
        if (node.id && usable(node.id) && unique(`#${CSS.escape(node.id)}`)) {
            parts.unshift(`#${CSS.escape(node.id)}`);
            break;
        }
        parts.unshift(part(node));
        if (node.localName === 'body') break;
        if (unique(parts.join(' > ')) && (landmark.test(node.localName) || parts.length >= 4)) break;
    }

    return parts.join(' > ');
}

/** What the dashboard and the agent get about the picked element. */
export function describe(element) {
    // An icon's <svg> or <path> means the button or link around it
    if (element instanceof SVGElement) element = element.closest('svg')?.parentElement ?? element;

    const rect = element.getBoundingClientRect();
    const text = (element.innerText || element.textContent || '').replace(/\s+/g, ' ').trim();

    return {
        selector: selectorFor(element),
        html: element.outerHTML.slice(0, 4000),
        text: text.slice(0, 300) || undefined,
        rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
    };
}
