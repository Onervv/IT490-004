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

    /* ── Number formatting ──────────────────────────────────────── */

    function formatNumber(n) {
        if (!n) return '0';
        n = parseInt(n, 10);
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
        if (n >= 1_000)     return (n / 1_000).toFixed(1).replace(/\.0$/, '') + 'K';
        return n.toLocaleString();
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

    // Bg colour palette (dark backgrounds for white text)
    const BG_CLASSES = ['bg-primary','bg-secondary','bg-success','bg-danger','bg-info','bg-dark'];

    /**
     * Build the HTML for a single artist card with a star button.
     * @param {Object}  item      - { id, name, listeners, play_count, bio, url, fetched_at }
     * @param {boolean} removable - if true, star acts as "remove" (artists page)
     */
    function renderCard(item, removable) {
        const liked     = removable || isLiked(item.id);
        const starClass = liked ? 'star-btn starred' : 'star-btn';
        const starFill  = liked ? '&#9733;' : '&#9734;';
        const tip       = liked ? 'Remove from favorites' : 'Add to favorites';

        const bgClass   = BG_CLASSES[item.id % BG_CLASSES.length];
        const bioShort  = item.bio
            ? (item.bio.length > 120 ? item.bio.substring(0, 120) + '\u2026' : item.bio)
            : '';

        const urlBlock = item.url
            ? `<div class="card-footer bg-transparent border-top-0 pt-0">
                 <a href="${escapeHtml(item.url)}" target="_blank" rel="noopener"
                    class="small text-white-50 text-decoration-none">View on Last.fm &nearr;</a>
               </div>`
            : '';

        return `
            <div class="col card-item" data-item-id="${item.id}">
                <div class="card ${bgClass} text-white h-100 position-relative" style="overflow:hidden">
                    <button type="button" class="${starClass}" data-like-id="${item.id}" title="${tip}">${starFill}</button>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">${escapeHtml(item.name || 'Unknown')}</h5>
                        <p class="card-text small flex-grow-1 mb-2" style="opacity:.85">${escapeHtml(bioShort)}</p>
                        <div class="d-flex gap-2 flex-wrap mt-auto">
                            <span class="badge bg-light text-dark">\uD83D\uDC64 ${formatNumber(item.listeners)} listeners</span>
                            <span class="badge bg-light text-dark">\u25B6 ${formatNumber(item.play_count)} plays</span>
                        </div>
                    </div>
                    ${urlBlock}
                </div>
            </div>`;
    }

    /* ── Public API ─────────────────────────────────────────────── */

    return {
        escapeHtml,
        debounce,
        formatNumber,
        getLikedItems,
        saveLikedItems,
        isLiked,
        renderCard
    };
})();
