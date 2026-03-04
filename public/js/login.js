// login.js - handle login-related frontend actions

// Define the Response Handler
function handleLoginResponse(parsedData) {
    const responseDiv = document.getElementById("textResponse");
    
    if (parsedData.status === 'ok' || parsedData.status === 'success') {
        responseDiv.innerHTML = `
            <div class="alert alert-success">
                <strong>Login Successful!</strong><br>
                Welcome! Redirecting you in 2 seconds...
            </div>
        `;
        setTimeout(() => {
            window.location.href = 'home.php';
        }, 2000);
    } else {
        responseDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>Login Failed:</strong><br>
                ${parsedData.message || 'Unknown error'}
            </div>
        `;
    }
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
    const timeout = setTimeout(() => controller.abort(), 30000); // 30 second timeout

    console.debug("About to fetch login.php", { method: "POST", bodySize: data.toString().length });
    
    fetch("login.php", {
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
            
            console.debug("login.php raw response (first 200 chars):", text.substring(0, 200));
            
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