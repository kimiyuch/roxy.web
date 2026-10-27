// Screenshots: the automatic one (lazy-loaded script) and the ones reviewers add.

const MAX_EDGE = 2560;
const MAX_BYTES = 7.5 * 1024 * 1024;

let loading;

function loadScreenshotter(base, version) {
    loading ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `${base}/widget-screenshot.js?v=${version}`;
        script.onload = () => resolve(window.__roxyScreenshot);
        script.onerror = () => {
            loading = null;
            reject(new Error('Could not load the screenshot tool.'));
        };
        document.head.append(script);
    });

    return loading;
}

export async function takeScreenshot(base, version, host) {
    const capture = await loadScreenshotter(base, version);
    return capture(host);
}

/** Fit a picked, dropped or pasted image into the upload limits; small ones go as they are. */
export async function prepareImage(file) {
    if (!/^image\/(png|jpe?g|webp)$/.test(file.type) && !/^image\//.test(file.type)) return null;

    const bitmap = await createImageBitmap(file).catch(() => null);
    if (!bitmap) return null;

    const fits = Math.max(bitmap.width, bitmap.height) <= MAX_EDGE && file.size <= MAX_BYTES && /^image\/(png|jpe?g|webp)$/.test(file.type);
    if (fits) {
        bitmap.close();
        return file;
    }

    const ratio = Math.min(1, MAX_EDGE / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * ratio);
    canvas.height = Math.round(bitmap.height * ratio);
    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close();

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.88));
    return blob && new File([blob], 'image.jpg', { type: 'image/jpeg' });
}
