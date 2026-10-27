import { defineConfig } from 'vite';

// The widget for client sites, built apart from the dashboard: one small classic script at a
// stable URL (public/widget.js), plus the screenshot tool that's only loaded when needed
// (public/widget-screenshot.js). `WIDGET_ENTRY=screenshot` builds the second one.
const screenshot = process.env.WIDGET_ENTRY === 'screenshot';

export default defineConfig({
    publicDir: false,
    define: {
        __ROXY_VERSION__: JSON.stringify(Date.now().toString(36)),
    },
    build: {
        outDir: 'public',
        emptyOutDir: false,
        copyPublicDir: false,
        target: 'es2020',
        lib: {
            entry: screenshot ? 'resources/widget/screenshot.js' : 'resources/widget/main.js',
            formats: ['iife'],
            name: screenshot ? 'RoxyScreenshot' : 'Roxy',
            fileName: () => (screenshot ? 'widget-screenshot.js' : 'widget.js'),
        },
    },
});
