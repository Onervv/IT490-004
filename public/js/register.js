// register.js - handle registration form submission

function handleRegisterResponse(parsedData) {
    const responseDiv = document.getElementById("registerResponse");
    
    if (parsedData.status === 'ok' || parsedData.status === 'success') {
        responseDiv.innerHTML = `
            <div class="alert alert-success">
                <strong>Registration Successful!</strong><br>
                Your account has been created. You can now <a href="login_page.php">login here</a>.
            </div>
        `;
        document.getElementById('registerForm').reset();
    } else {
        responseDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>Registration Failed:</strong><br>
                ${parsedData.message || 'Unknown error'}
            </div>
        `;
    }
}

function sendRegisterRequest(username, password) {
    const data = new URLSearchParams({
        "type": "register",
        "uname": username,
        "pword": password
    });

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 30000);
    
    fetch("register.php", {
        method: "POST",
        body: data,
        signal: controller.signal
    })
        .then(async response => {
            clearTimeout(timeout);
            const text = await response.text();
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status} - ${text || '<empty response>'}`);
            }
            if (!text || text.trim().length === 0) {
                throw new Error('Received empty response from server');
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON from server: ${e.message}`);
            }
        })
        .then(parsedData => {
            handleRegisterResponse(parsedData);
        })
        .catch(error => {
            clearTimeout(timeout);
            const responseDiv = document.getElementById("registerResponse");
            let message = error.message;
            if (error.name === 'AbortError') {
                message = 'Request timed out - server may be unreachable';
            }
            responseDiv.innerHTML = `<div class="alert alert-danger">Request Failed: ${message}</div>`;
        });
}

// wire up the form after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const username = document.getElementById('regUsername');
    const password = document.getElementById('regPassword');
    const confirm = document.getElementById('regConfirm');
    const responseDiv = document.getElementById('registerResponse') || (() => {
        // Create response div if it doesn't exist
        const div = document.createElement('div');
        div.id = 'registerResponse';
        form.parentNode.insertBefore(div, form);
        return div;
    })();

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
            responseDiv.innerHTML = '<div class="text-muted">Creating account...</div>';
            sendRegisterRequest(username.value.trim(), password.value);
        } else {
            responseDiv.innerHTML = '<div class="alert alert-warning">Please correct the errors highlighted in red.</div>';
        }
    });
});
