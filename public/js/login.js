// login.js - handle login-related frontend actions

function handleLoginResponse(parsedData) {
    const responseDiv = document.getElementById("textResponse");
    
    if (parsedData.status === 'ok' || parsedData.status === 'success') {
        // Store the session key in sessionStorage
        if (parsedData.session_key) {
            sessionStorage.setItem('session_key', parsedData.session_key);
            sessionStorage.setItem('username', parsedData.username || '');
        }
        
        responseDiv.innerHTML = `
            <div class="alert alert-success">
                <strong>Login Successful!</strong><br>
                Welcome! Redirecting...
            </div>
        `;
        setTimeout(() => {
            window.location.href = 'dashboard.php';
        }, 1000);
    } else {
        responseDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>Login Failed:</strong><br>
                ${parsedData.message || 'Unknown error'}
            </div>
        `;
    }
}

function sendLoginRequest(username, password) {
    const data = new URLSearchParams({
        "type": "login",
        "uname": username,
        "pword": password
    });

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 30000);
    
    fetch("login.php", {
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
            handleLoginResponse(parsedData);
        })
        .catch(error => {
            clearTimeout(timeout);
            const responseDiv = document.getElementById("textResponse");
            let message = error.message;
            if (error.name === 'AbortError') {
                message = 'Request timed out - server may be unreachable';
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