// register.js - simple client-side validation for the registration form

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const username = document.getElementById('regUsername');
    const password = document.getElementById('regPassword');
    const confirm = document.getElementById('regConfirm');

    const usernameValid = () => username.value.trim().length >= 3;
    const passwordValid = () => {
        const p = password.value;
        return p.length >= 8 && /\d/.test(p);
    };
    const passwordsMatch = () => password.value === confirm.value && password.value.length > 0;

    const updateValidity = (el, condition) => {
        if (condition) {
            el.classList.add('is-valid');
            el.classList.remove('is-invalid');
        } else {
            el.classList.add('is-invalid');
            el.classList.remove('is-valid');
        }
    };

    username.addEventListener('input', () => {
        updateValidity(username, usernameValid());
    });
    password.addEventListener('input', () => {
        updateValidity(password, passwordValid());
        updateValidity(confirm, passwordsMatch());
    });
    confirm.addEventListener('input', () => {
        updateValidity(confirm, passwordsMatch());
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        // trigger validation updates
        updateValidity(username, usernameValid());
        updateValidity(password, passwordValid());
        updateValidity(confirm, passwordsMatch());

        if (usernameValid() && passwordValid() && passwordsMatch()) {
            // placeholder: submit data via fetch or normal post
            alert('Form is valid; ready to send to server.');
        } else {
            alert('Please correct the errors highlighted in red.');
        }
    });
});
