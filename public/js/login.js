// login.js - handle login-related frontend actions

// Define the Response Handler
function handleLoginResponse(parsedData) {
    const responseDiv = document.getElementById("textResponse");
    responseDiv.innerHTML = `<div class="alert alert-success">Response: ${parsedData}</div>`;
}

// Modern Fetch API Request (login-specific)
function sendLoginRequest(username, password) {
    console.debug("sendLoginRequest() called", { username, password });

    const data = new URLSearchParams({
        "type": "login",
        "uname": username,
        "pword": password
    });

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 10000); // 10 second timeout

    fetch("login.php", {
        method: "POST",
        body: data,
        signal: controller.signal
    })
        .then(async response => {
            clearTimeout(timeout);
            const text = await response.text();
            if (!response.ok) {
                // include server body in error message for debugging
                throw new Error(`HTTP ${response.status} - ${text || '<empty>'}`);
            }
            if (!text || text.trim().length === 0) {
                throw new Error('Received empty response from server');
            }
            // attempt to parse JSON and provide a clearer error message
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Invalid JSON from server: ${e.message} (body: ${text})`);
            }
        })
        .then(parsedData => {
            console.debug("login response received", parsedData);
            handleLoginResponse(parsedData);
        })
        .catch(error => {
            clearTimeout(timeout);
            console.error("login request failed", error);
            const responseDiv = document.getElementById("textResponse");
            let message = error.message;
            if (error.name === 'AbortError') {
                message = 'Request timed out after 10 seconds - server may be unreachable';
            }
            responseDiv.innerHTML = `<div class="alert alert-danger">Request Failed: ${message}</div>`;
        });
}

// wire up the button after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const loginButton = document.getElementById('loginBtn');
    loginButton.addEventListener('click', () => {
        const user = document.getElementById('usernameInput').value;
        const pass = document.getElementById('passwordInput').value;

        document.getElementById("textResponse").innerHTML = '<div class="text-muted">Sending data...</div>';
        sendLoginRequest(user, pass);
    });
});