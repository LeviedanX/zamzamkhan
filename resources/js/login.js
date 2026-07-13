const password = document.querySelector('#password');
const passwordToggle = document.querySelector('#password-toggle');
const passwordVisibleIcon = document.querySelector('#password-visible-icon');
const passwordHiddenIcon = document.querySelector('#password-hidden-icon');

passwordToggle?.addEventListener('click', () => {
    const willShow = password.type === 'password';

    password.type = willShow ? 'text' : 'password';
    passwordToggle.setAttribute('aria-pressed', String(willShow));
    passwordToggle.setAttribute('aria-label', willShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    passwordVisibleIcon.hidden = willShow;
    passwordHiddenIcon.hidden = !willShow;
});

document.querySelector('#admin-login-form')?.addEventListener('submit', (event) => {
    const submitButton = event.currentTarget.querySelector('button[type="submit"]');
    if (!submitButton) return;

    submitButton.classList.add('opacity-70', 'cursor-wait', 'pointer-events-none');
    window.setTimeout(() => {
        submitButton.disabled = true;
    }, 0);
});
