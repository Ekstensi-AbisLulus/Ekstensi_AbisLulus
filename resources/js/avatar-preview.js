// Pratinjau foto profil sebelum disimpan.
// <input type="file" data-avatar-input="#idElemenAvatar"> — elemen target boleh <img> atau <span> inisial.
document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-avatar-input]');
    const file = input?.files?.[0];

    if (!file || !file.type.startsWith('image/')) {
        return;
    }

    const target = document.querySelector(input.dataset.avatarInput);

    if (!target) {
        return;
    }

    let img = target;

    if (target.tagName !== 'IMG') {
        img = document.createElement('img');
        img.className = target.className;
        img.id = target.id;
        img.alt = 'Pratinjau foto profil';
        target.replaceWith(img);
    }

    if (img.dataset.objectUrl) {
        URL.revokeObjectURL(img.dataset.objectUrl);
    }

    img.dataset.objectUrl = URL.createObjectURL(file);
    img.src = img.dataset.objectUrl;
});
