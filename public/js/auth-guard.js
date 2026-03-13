/**
 * auth-guard.js
 * -------------
 * Shared session check + logout handler for all protected pages.
 * Include this script on any page behind authentication.
 */
(function () {
    'use strict';

    const sessionKey = sessionStorage.getItem('session_key');
    if (!sessionKey) {
        window.location.href = 'login_page.php';
        return;
    }

    document.getElementById('logoutBtn').addEventListener('click', function (e) {
        e.preventDefault();
        sessionStorage.removeItem('session_key');
        sessionStorage.removeItem('username');
        window.location.href = 'login_page.php';
    });
})();
