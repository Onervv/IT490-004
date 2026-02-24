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

    fetch("login.php", {
        method: "POST",
        body: data
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(parsedData => {
            console.debug("login response received", parsedData);
            handleLoginResponse(parsedData);
        })
        .catch(error => {
            console.error("login request failed", error);
            const responseDiv = document.getElementById("textResponse");
            responseDiv.innerHTML = `<div class="alert alert-danger">Request Failed: ${error.message}</div>`;
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