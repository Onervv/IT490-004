/**
 * artists.js
 * ----------
 * Renders the user's starred / liked cards from localStorage.
 * Items are saved by explore.js under the key "liked_items_{username}".
 */

(function () {
    'use strict';

    function getLikedKey() {
        const username = sessionStorage.getItem('username') || '_anon';
        return `liked_items_${username}`;
    }

    function getLikedItems() {
        try {
            return JSON.parse(localStorage.getItem(getLikedKey())) || {};
        } catch { return {}; }
    }

    function saveLikedItems(liked) {
        localStorage.setItem(getLikedKey(), JSON.stringify(liked));
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function removeLike(itemId) {
        const liked = getLikedItems();
        delete liked[itemId];
        saveLikedItems(liked);
        render();
    }

    function renderCard(item) {
        return `
            <div class="col card-item" data-item-id="${item.id}">
                <div class="card ${item.bgClass || ''} ${item.textClass || ''} h-100 position-relative">
                    <button type="button" class="star-btn starred" data-like-id="${item.id}" title="Remove from favorites">&#9733;</button>
                    <div class="card-header">${escapeHtml(item.genre || '')}</div>
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(item.title || 'Untitled')}</h5>
                        <p class="card-text">${escapeHtml(item.artist || 'Unknown')}</p>
                    </div>
                </div>
            </div>`;
    }

    function render() {
        const container = document.getElementById('artistsCardContainer');
        const emptyMsg  = document.getElementById('artistsEmpty');
        if (!container) return;

        const liked  = getLikedItems();
        const items  = Object.values(liked);

        if (items.length === 0) {
            container.innerHTML = '';
            emptyMsg.style.display = 'block';
            return;
        }

        emptyMsg.style.display = 'none';
        container.innerHTML = items.map(renderCard).join('');

        // Wire remove-star buttons
        container.querySelectorAll('.star-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                removeLike(parseInt(btn.dataset.likeId, 10));
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', render);
    } else {
        render();
    }
})();
