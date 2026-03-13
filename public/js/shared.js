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
        if (str == null) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
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

    /** Always use string keys for consistent lookup */
    function isLiked(itemId) {
        return !!getLikedItems()[String(itemId)];
    }

    /* ── Card HTML ──────────────────────────────────────────────── */

    // Dark-only palette so white text is always readable
    const BG_PALETTE = [
        { bg: '#1a1a2e', border: '#16213e' },
        { bg: '#0f3460', border: '#1a1a5e' },
        { bg: '#533483', border: '#4a2d7a' },
        { bg: '#1b4332', border: '#2d6a4f' },
        { bg: '#7b2d26', border: '#9b3a30' },
        { bg: '#2c3e50', border: '#34495e' },
        { bg: '#4a1942', border: '#6b2a63' },
        { bg: '#1c3879', border: '#2a4a8c' },
    ];

    /**
     * Build the HTML for a single artist card with a star button.
     * @param {Object}  item      - { id, name, listeners, play_count, bio, url, fetched_at }
     * @param {boolean} removable - if true, star acts as "remove" (artists page)
     */
    function renderCard(item, removable) {
        const sid       = String(item.id); // always string
        const liked     = removable || isLiked(sid);
        const starClass = liked ? 'star-btn starred' : 'star-btn';
        const starFill  = liked ? '&#9733;' : '&#9734;';
        const tip       = liked ? 'Remove from favorites' : 'Add to favorites';

        const palette   = BG_PALETTE[Math.abs(hashCode(item.name || sid)) % BG_PALETTE.length];
        const bioShort  = item.bio
            ? (item.bio.length > 120 ? item.bio.substring(0, 120) + '\u2026' : item.bio)
            : '';

        const urlBlock = item.url
            ? `<a href="${escapeHtml(item.url)}" target="_blank" rel="noopener"
                  class="small text-decoration-none" style="color:rgba(255,255,255,.6)">View on Last.fm &nearr;</a>`
            : '';

        return `
            <div class="col card-item" data-item-id="${sid}">
                <div class="card h-100 position-relative border-0 shadow-sm"
                     style="overflow:hidden;background:${palette.bg};color:#fff;border-left:4px solid ${palette.border}!important">
                    <button type="button" class="${starClass}" data-like-id="${sid}" title="${tip}">${starFill}</button>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">${escapeHtml(item.name || 'Unknown')}</h5>
                        <p class="card-text small flex-grow-1 mb-2" style="opacity:.75">${escapeHtml(bioShort)}</p>
                        <div class="d-flex gap-2 flex-wrap mt-auto mb-1">
                            <span class="badge" style="background:rgba(255,255,255,.15)">\uD83D\uDC64 ${formatNumber(item.listeners)} listeners</span>
                            <span class="badge" style="background:rgba(255,255,255,.15)">\u25B6 ${formatNumber(item.play_count)} plays</span>
                        </div>
                        ${urlBlock}
                    </div>
                </div>
            </div>`;
    }

    /** Simple string hash for deterministic palette assignment by name */
    function hashCode(str) {
        let h = 0;
        for (let i = 0; i < str.length; i++) {
            h = ((h << 5) - h) + str.charCodeAt(i);
            h |= 0;
        }
        return h;
    }

    /* ── Public API ─────────────────────────────────────────────── */

    /* ── Toast helper ───────────────────────────────────────────── */

    /**
     * Show a Bootstrap toast notification briefly.
     * @param {string} message - HTML-safe message text
     * @param {string} [bg='success'] - Bootstrap bg class suffix (success, danger, warning, info)
     * @param {number} [delay=3000] - Auto-hide delay in ms
     */
    function showToast(message, bg, delay) {
        bg    = bg    || 'success';
        delay = delay || 3000;

        // Ensure toast container exists
        let container = document.getElementById('m3-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'm3-toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1090';
            document.body.appendChild(container);
        }

        const id = 'm3toast_' + Date.now();
        const html =
            '<div id="' + id + '" class="toast align-items-center text-bg-' + bg + ' border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="' + delay + '">' +
                '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);

        const el = document.getElementById(id);
        if (el && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            var toast = new bootstrap.Toast(el);
            toast.show();
            el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        }
    }

    return {
        escapeHtml,
        debounce,
        formatNumber,
        getLikedItems,
        saveLikedItems,
        isLiked,
        showToast,
        renderCard,
        hashCode
    };
})();
