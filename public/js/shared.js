/**
 * shared.js
 * ---------
 * Common utility functions shared across explore.js and artists.js.
 * Must be loaded before those scripts.
 */

var M3 = (function () {
    'use strict';

    /* ── HTML escaping ──────────────────────────────────────────── */

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* ── Debounce ───────────────────────────────────────────────── */

    function debounce(fn, ms) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), ms);
        };
    }

    /* ── Liked / Starred items (per-user, localStorage) ─────────── */

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

    function isLiked(itemId) {
        return !!getLikedItems()[itemId];
    }

    /* ── Card HTML ──────────────────────────────────────────────── */

    /**
     * Build the HTML for a single card with a star button.
     * @param {Object}  item    - { id, title, artist, genre, bgClass, textClass }
     * @param {boolean} removable - if true, clicking the star removes the item (artists page behaviour)
     */
    function renderCard(item, removable) {
        const liked     = removable || isLiked(item.id);
        const starClass = liked ? 'star-btn starred' : 'star-btn';
        const starFill  = liked ? '&#9733;' : '&#9734;';
        const title     = liked ? 'Remove from favorites' : 'Add to favorites';

        return `
            <div class="col card-item" data-item-id="${item.id}">
                <div class="card ${item.bgClass || ''} ${item.textClass || ''} h-100 position-relative" style="overflow:hidden">
                    <button type="button" class="${starClass}" data-like-id="${item.id}" title="${title}">${starFill}</button>
                    <div class="card-header">${escapeHtml(item.genre || '')}</div>
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(item.title || 'Untitled')}</h5>
                        <p class="card-text">${escapeHtml(item.artist || 'Unknown')}</p>
                    </div>
                </div>
            </div>`;
    }

    /* ── Public API ─────────────────────────────────────────────── */

    return {
        escapeHtml,
        debounce,
        getLikedItems,
        saveLikedItems,
        isLiked,
        renderCard
    };
})();
