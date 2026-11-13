const MAX_IMAGE_BYTES = 3 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

function slugifyTitle(value) {
    const slug = value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/[\s-]+/g, '-')
        .slice(0, 200)
        .replace(/-+$/g, '');

    return slug || 'artikel';
}

document.addEventListener('input', (event) => {
    const source = event.target.closest('[data-slug-source]');
    if (!source) return;

    const targetId = source.dataset.slugSource;
    const preview = document.getElementById(targetId);
    if (!preview) return;

    const toggleId = source.dataset.slugToggle;
    if (toggleId) {
        const toggleCheckbox = document.getElementById(toggleId);
        if (!toggleCheckbox || !toggleCheckbox.checked) {
            return;
        }
    }

    const raw = source.value.trim();
    preview.value = raw === '' ? '' : slugifyTitle(raw);
});

document.addEventListener('change', (event) => {
    const slugToggle = event.target.closest('[data-slug-toggle-control]');
    if (slugToggle) {
        const source = document.querySelector(`[data-slug-toggle="${slugToggle.id}"]`);
        const targetId = source?.dataset.slugSource;
        const preview = targetId ? document.getElementById(targetId) : null;

        if (source && preview) {
            if (slugToggle.checked) {
                const raw = source.value.trim();
                preview.value = raw === '' ? '' : slugifyTitle(raw);
            } else if (preview.dataset.initialSlug !== undefined) {
                preview.value = preview.dataset.initialSlug;
            }
        }
        return;
    }

    const input = event.target.closest('[data-image-input]');
    if (!input) return;

    const container = input.closest('[data-image-uploader]');
    if (!container) return;

    const previewWrapper = container.querySelector('[data-image-preview-wrap]');
    const previewImg = container.querySelector('[data-image-preview]');
    const previewMeta = container.querySelector('[data-image-meta]');
    const emptyState = container.querySelector('[data-image-empty]');
    const errorBox = container.querySelector('[data-image-error]');
    const initialSrc = previewImg?.dataset.initialSrc || '';
    const initialMeta = previewMeta?.dataset.initialMeta || '';

    const restoreInitialOrEmpty = () => {
        if (initialSrc && previewImg && previewWrapper) {
            previewImg.src = initialSrc;
            if (previewMeta) previewMeta.textContent = initialMeta;
            previewWrapper.classList.remove('d-none');
            if (emptyState) emptyState.classList.add('d-none');
        } else {
            if (previewWrapper) previewWrapper.classList.add('d-none');
            if (emptyState) emptyState.classList.remove('d-none');
        }
    };

    const file = input.files && input.files[0];

    if (errorBox) {
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
    }

    if (!file) {
        restoreInitialOrEmpty();
        return;
    }

    if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
        restoreInitialOrEmpty();
        if (errorBox) {
            errorBox.textContent = 'Format file tidak didukung. Pilih gambar JPG, JPEG, PNG, atau WebP.';
            errorBox.classList.remove('d-none');
        }
        return;
    }

    if (file.size > MAX_IMAGE_BYTES) {
        restoreInitialOrEmpty();
        if (errorBox) {
            errorBox.textContent = 'Ukuran gambar melebihi batas maksimal 3 MB.';
            errorBox.classList.remove('d-none');
        }
        return;
    }

    const reader = new FileReader();
    reader.onload = (loadEvent) => {
        if (previewImg && typeof loadEvent.target?.result === 'string') {
            previewImg.src = loadEvent.target.result;
        }
        if (previewMeta) {
            const sizeKb = Math.max(1, Math.round(file.size / 1024));
            previewMeta.textContent = `${file.name} (${sizeKb} KB)`;
        }
        if (emptyState) emptyState.classList.add('d-none');
        if (previewWrapper) previewWrapper.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-panel-form]');
    if (!form) return;

    const submitBtn = form.querySelector('[data-submit-btn]');
    if (!submitBtn || submitBtn.disabled) return;

    submitBtn.disabled = true;
    submitBtn.setAttribute('aria-busy', 'true');

    const defaultLabel = submitBtn.querySelector('[data-submit-default]');
    const loadingLabel = submitBtn.querySelector('[data-submit-loading]');
    if (defaultLabel) defaultLabel.classList.add('d-none');
    if (loadingLabel) loadingLabel.classList.remove('d-none');
});
