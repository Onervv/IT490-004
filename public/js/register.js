// register.js - handle registration form submission

// Define the Response Handler
function handleRegisterResponse(parsedData) {
    const responseDiv = document.getElementById("registerResponse");
    
    if (parsedData.status === 'ok' || parsedData.status === 'success') {
        responseDiv.innerHTML = `
            <div class="alert alert-success">
                <strong>Registration Successful!</strong><br>
                Your account has been created. You can now <a href="login_page.php">login here</a>.
            </div>
        `;
        // Clear form
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

// Modern Fetch API Request (register-specific)
function sendRegisterRequest(username, password) {
    console.debug("sendRegisterRequest() called", { username, password: '***' });

    const data = new URLSearchParams({
        "type": "register",
        "uname": username,
        "pword": password
    });

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 30000); // 30 second timeout

    console.debug("About to fetch register.php", { method: "POST", bodySize: data.toString().length });
    
    fetch("register.php", {
        method: "POST",
        body: data,
        signal: controller.signal
    })
        .then(async response => {
            clearTimeout(timeout);
            console.debug("fetch() completed, response status:", response.status);
            console.debug("response headers:", {
                'content-type': response.headers.get('content-type'),
                'content-length': response.headers.get('content-length')
            });
            
            let text;
            try {
                text = await response.text();
                console.debug("response.text() succeeded, length:", text.length);
            } catch (e) {
                console.error("response.text() failed:", e);
                throw new Error(`Failed to read response body: ${e.message}`);
            }
            
            console.debug("register.php raw response (first 200 chars):", text.substring(0, 200));
            
            if (!response.ok) {
                // include server body in error message for debugging
                throw new Error(`HTTP ${response.status} - ${text || '<empty response>'}`);
            }
            if (!text || text.trim().length === 0) {
                throw new Error('Received empty response from server');
            }
            // attempt to parse JSON and provide a clearer error message
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON from server: ${e.message} (body: ${text.substring(0, 100)})`);
            }
        })
        .then(parsedData => {
            console.debug("register response received", parsedData);
            handleRegisterResponse(parsedData);
        })
        .catch(error => {
            clearTimeout(timeout);
            console.error("register request failed", error);
            const responseDiv = document.getElementById("registerResponse");
            let message = error.message;
            if (error.name === 'AbortError') {
                message = 'Request timed out after 30 seconds - worker may not be running or broker is unreachable';
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
