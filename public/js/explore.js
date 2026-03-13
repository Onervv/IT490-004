/**
 * explore.js
 * ----------
 * Explore page: virtual scrolling, client-side search, star/like.
 * Fetches ALL artist data in a single RabbitMQ call (avoids server
 * crash on repeated requests), then does everything client-side.
 * Depends on shared.js (M3 namespace) being loaded first.
 */

(function () {
    'use strict';

    /* ── Config ─────────────────────────────────────────────────── */
    const CARD_MIN_HEIGHT  = 260;
    const COLS_PER_ROW     = 4;
    const SCROLL_THRESHOLD = 300;
    const DEBOUNCE_MS      = 120;
    const OVERSCAN_ROWS    = 2;

    /* ── State ──────────────────────────────────────────────────── */
    let masterList    = [];   // full dataset from API (never mutated after load)
    let filteredItems = [];   // current view (search-filtered subset)
    let dataLoaded    = false;
    let firstVisibleRow = -1, lastVisibleRow = -1;

    /* ── DOM refs ───────────────────────────────────────────────── */
    let viewport, spacer, cardContainer, sentinel;
    let searchInput, searchForm, searchDropdown;

    /* ── One-shot API fetch ─────────────────────────────────────── */

    async function fetchAllArtists() {
        sentinel.style.display = 'block';

        try {
            const res = await fetch('explore_api.php?offset=0&limit=500');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch {
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Invalid JSON from API');
            }

            if (data.status !== 'ok') {
                console.error('API returned:', data);
                throw new Error(data.message || 'API error');
            }

            masterList    = data.artists || [];
            filteredItems = masterList;
            dataLoaded    = true;

            updateSpacer();
            renderVisibleCards();
        } catch (err) {
            console.error('Failed to load artists:', err);
            cardContainer.innerHTML =
                '<div class="col-12"><div class="alert alert-danger">Failed to load artist data. Make sure the API server is running on the DB VM, then refresh.</div></div>';
        }

        sentinel.style.display = 'none';
    }

    /* ── Like toggle ────────────────────────────────────────────── */
    function toggleLike(itemId) {
        const sid = String(itemId);
        const liked = M3.getLikedItems();
        const wasLiked = !!liked[sid];
        if (wasLiked) { delete liked[sid]; }
        else {
            const item = filteredItems.find(i => String(i.id) === sid)
                      || masterList.find(i => String(i.id) === sid);
            if (item) {
                liked[sid] = item;
                M3.showToast('\u2605 <strong>' + M3.escapeHtml(item.name) + '</strong> added to your favorites!', 'success', 3000);
            }
        }
        M3.saveLikedItems(liked);
        firstVisibleRow = -1;
        renderVisibleCards();
    }

    /* ── Virtual scrolling ──────────────────────────────────────── */
    function totalRows()  { return Math.ceil(filteredItems.length / COLS_PER_ROW); }
    function rowHeight()  { const c = cardContainer.querySelector('.card-item'); return c ? c.offsetHeight + 24 : CARD_MIN_HEIGHT; }
    function updateSpacer() { spacer.style.minHeight = totalRows() * rowHeight() + 'px'; }

    function renderVisibleCards() {
        if (!filteredItems.length) {
            cardContainer.innerHTML = dataLoaded
                ? '<div class="col-12 text-center text-muted py-4">No artists match your search.</div>'
                : '';
            cardContainer.style.transform = '';
            return;
        }

        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const rh = rowHeight();
        const relScroll = Math.max(0, scrollTop - (viewport.getBoundingClientRect().top + scrollTop));
        const first = Math.max(0, Math.floor(relScroll / rh) - OVERSCAN_ROWS);
        const last  = Math.min(totalRows() - 1, first + Math.ceil(window.innerHeight / rh) + OVERSCAN_ROWS * 2);

        if (first === firstVisibleRow && last === lastVisibleRow && cardContainer.childElementCount > 0) return;
        firstVisibleRow = first; lastVisibleRow = last;

        let html = '';
        for (let r = first; r <= last; r++) {
            const s = r * COLS_PER_ROW, e = Math.min(s + COLS_PER_ROW, filteredItems.length);
            for (let i = s; i < e; i++) html += M3.renderCard(filteredItems[i]);
        }
        cardContainer.innerHTML = html;
        cardContainer.style.transform = `translateY(${first * rh}px)`;

        cardContainer.querySelectorAll('.star-btn').forEach(btn =>
            btn.addEventListener('click', e => { e.stopPropagation(); toggleLike(btn.dataset.likeId); })
        );
    }

    /* ── Client-side search / filter ────────────────────────────── */

    function applyFilter(term) {
        if (!term) { filteredItems = masterList; return; }
        const t = term.toLowerCase();
        filteredItems = masterList.filter(i =>
            (i.name && i.name.toLowerCase().includes(t)) ||
            (i.bio  && i.bio.toLowerCase().includes(t))
        );
    }

    function updateDropdown() {
        const term = searchInput.value.trim();
        if (!term || !filteredItems.length) {
            searchDropdown.innerHTML = ''; searchDropdown.style.display = 'none'; return;
        }
        const max = 8, preview = filteredItems.slice(0, max);
        let html = preview.map(i =>
            `<button type="button" class="dropdown-item search-result-item" data-target-id="${i.id}">
                <strong>${M3.escapeHtml(i.name)}</strong>
                <span class="text-muted small ms-2">${M3.formatNumber(i.listeners)} listeners</span>
            </button>`
        ).join('');
        if (filteredItems.length > max) html += `<span class="dropdown-item text-muted small">+${filteredItems.length - max} more</span>`;
        searchDropdown.innerHTML = html; searchDropdown.style.display = 'block';
        searchDropdown.querySelectorAll('.search-result-item').forEach(btn =>
            btn.addEventListener('click', () => { jumpToCard(parseInt(btn.dataset.targetId, 10)); searchDropdown.style.display = 'none'; })
        );
    }

    function jumpToCard(itemId) {
        const idx = filteredItems.findIndex(i => i.id === itemId);
        if (idx === -1) return;
        const top = viewport.getBoundingClientRect().top + (window.scrollY || document.documentElement.scrollTop);
        window.scrollTo({ top: top + Math.floor(idx / COLS_PER_ROW) * rowHeight() - 80, behavior: 'smooth' });
        setTimeout(() => {
            renderVisibleCards();
            const el = cardContainer.querySelector(`[data-item-id="${itemId}"]`);
            if (el) { el.classList.add('card-highlight'); setTimeout(() => el.classList.remove('card-highlight'), 1500); }
        }, 400);
    }

    /* ── Init ───────────────────────────────────────────────────── */
    function init() {
        viewport       = document.getElementById('virtualScrollViewport');
        spacer         = document.getElementById('virtualScrollSpacer');
        cardContainer  = document.getElementById('cardContainer');
        sentinel       = document.getElementById('scrollSentinel');
        searchInput    = document.getElementById('exploreSearchInput');
        searchForm     = document.getElementById('exploreSearchForm');
        searchDropdown = document.getElementById('exploreSearchResults');
        if (!viewport || !cardContainer) return;

        /* Scroll / resize → re-render visible window */
        window.addEventListener('scroll',
            M3.debounce(() => renderVisibleCards(), DEBOUNCE_MS));
        window.addEventListener('resize',
            M3.debounce(() => { updateSpacer(); firstVisibleRow = -1; renderVisibleCards(); }, DEBOUNCE_MS));

        /* Client-side search (no extra API calls) */
        searchInput.addEventListener('input', M3.debounce(() => {
            const term = searchInput.value.trim();
            applyFilter(term);
            updateSpacer();
            firstVisibleRow = -1; lastVisibleRow = -1;
            renderVisibleCards();
            updateDropdown();
        }, DEBOUNCE_MS));

        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            const term = searchInput.value.trim();
            applyFilter(term);
            updateSpacer();
            firstVisibleRow = -1; lastVisibleRow = -1;
            renderVisibleCards();
            if (filteredItems.length) jumpToCard(filteredItems[0].id);
            searchDropdown.style.display = 'none';
        });

        document.addEventListener('click', e => {
            if (!searchDropdown.contains(e.target) && e.target !== searchInput) searchDropdown.style.display = 'none';
        });

        /* Single fetch on page load */
        fetchAllArtists();
    }

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();
})();
