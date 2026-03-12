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

    /* ── Reviews (per-user, localStorage) ──────────────────────── */

    function getReviewsKey() {
        const username = sessionStorage.getItem('username') || '_anon';
        return `artist_reviews_${username}`;
    }

    function getReviews() {
        try {
            return JSON.parse(localStorage.getItem(getReviewsKey())) || {};
        } catch { return {}; }
    }

    function saveReview(artistId, text) {
        const reviews = getReviews();
        const key = String(artistId);
        if (text.trim()) {
            reviews[key] = { text: text.trim(), date: new Date().toLocaleDateString() };
        } else {
            delete reviews[key];
        }
        localStorage.setItem(getReviewsKey(), JSON.stringify(reviews));
    }

    function getReview(artistId) {
        return getReviews()[String(artistId)] || null;
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

    return {
        escapeHtml,
        debounce,
        formatNumber,
        getLikedItems,
        saveLikedItems,
        isLiked,
        getReviews,
        saveReview,
        getReview,
        renderCard,
        hashCode
    };
})();
