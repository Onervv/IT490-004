/**
 * explore.js
 * ----------
 * Explore page: virtual scrolling, infinite scroll, search, star/like.
 * Depends on shared.js (M3 namespace) being loaded first.
 *
 * TODO: Replace placeholder data source with real API calls.
 */

(function () {
    'use strict';

    /* ── Config ─────────────────────────────────────────────────── */
    const CARD_MIN_HEIGHT  = 220;
    const COLS_PER_ROW     = 4;
    const BATCH_SIZE       = 20;
    const SCROLL_THRESHOLD = 300;
    const DEBOUNCE_MS      = 120;
    const OVERSCAN_ROWS    = 2;

    /* ── State ──────────────────────────────────────────────────── */
    let allItems = [], filteredItems = [];
    let currentBatch = 0, isFetching = false, hasMore = true;
    let activeSearch = '';
    let firstVisibleRow = 0, lastVisibleRow = 0;

    /* ── DOM refs (resolved in init) ────────────────────────────── */
    let viewport, spacer, cardContainer, sentinel;
    let searchInput, searchForm, searchDropdown;

    /* ── Placeholder data ───────────────────────────────────────── */
    // TODO: Replace with real API fetch
    function generatePlaceholderItems(offset, limit) {
        const GENRES   = ['Pop','Rock','Jazz','Hip-Hop','R&B','Electronic','Classical','Country'];
        const ARTISTS  = ['The Weeknd','Doja Cat','Tyler the Creator','SZA','Kendrick Lamar','Frank Ocean','Bad Bunny','Billie Eilish','Drake','Ariana Grande','Post Malone','Dua Lipa','Travis Scott','Lana Del Rey','Metro Boomin','J. Cole'];
        const BG       = ['bg-primary','bg-secondary','bg-success','bg-danger','bg-warning','bg-info','bg-light','bg-dark'];
        const MAX = 200; // TODO: remove cap once real API is wired up
        const items = [];
        for (let i = 0; i < limit; i++) {
            const idx = offset + i;
            if (idx >= MAX) break;
            items.push({ id: idx, title: `Track #${idx+1}`, artist: ARTISTS[idx%ARTISTS.length], genre: GENRES[idx%GENRES.length], bgClass: BG[idx%BG.length], textClass: idx%BG.length===7 ? 'text-white' : '' });
        }
        return items;
    }

    /* ── Like toggle ────────────────────────────────────────────── */
    function toggleLike(itemId) {
        const liked = M3.getLikedItems();
        if (liked[itemId]) { delete liked[itemId]; }
        else { const item = allItems.find(i => i.id === itemId); if (item) liked[itemId] = item; }
        M3.saveLikedItems(liked);
        firstVisibleRow = -1;
        renderVisibleCards();
    }

    /* ── Virtual scrolling ──────────────────────────────────────── */
    function totalRows()  { return Math.ceil(filteredItems.length / COLS_PER_ROW); }
    function rowHeight()  { const c = cardContainer.querySelector('.card-item'); return c ? c.offsetHeight + 24 : CARD_MIN_HEIGHT; }
    function updateSpacer() { spacer.style.minHeight = totalRows() * rowHeight() + 'px'; }

    function renderVisibleCards() {
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
            btn.addEventListener('click', e => { e.stopPropagation(); toggleLike(parseInt(btn.dataset.likeId, 10)); })
        );
    }

    /* ── Infinite scroll ────────────────────────────────────────── */
    async function loadNextBatch() {
        if (isFetching || !hasMore) return;
        isFetching = true; sentinel.style.display = 'block';
        const items = generatePlaceholderItems(currentBatch * BATCH_SIZE, BATCH_SIZE);
        if (items.length < BATCH_SIZE) hasMore = false;
        allItems = allItems.concat(items); currentBatch++;
        applyFilter(); updateSpacer(); renderVisibleCards();
        sentinel.style.display = 'none'; isFetching = false;
    }

    /* ── Search / filter ────────────────────────────────────────── */
    function applyFilter() {
        if (!activeSearch) { filteredItems = allItems; return; }
        const t = activeSearch.toLowerCase();
        filteredItems = allItems.filter(i => i.title.toLowerCase().includes(t) || i.artist.toLowerCase().includes(t) || i.genre.toLowerCase().includes(t));
    }

    function updateDropdown() {
        if (!activeSearch || !filteredItems.length) { searchDropdown.innerHTML = ''; searchDropdown.style.display = 'none'; return; }
        const max = 8, preview = filteredItems.slice(0, max);
        let html = preview.map(i => `<button type="button" class="dropdown-item search-result-item" data-target-id="${i.id}"><strong>${M3.escapeHtml(i.title)}</strong> &mdash; ${M3.escapeHtml(i.artist)}</button>`).join('');
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

        const dScroll = M3.debounce(() => {
            renderVisibleCards();
            if (document.documentElement.scrollHeight - (window.scrollY + window.innerHeight) < SCROLL_THRESHOLD && hasMore && !isFetching) loadNextBatch();
        }, DEBOUNCE_MS);
        window.addEventListener('scroll', dScroll);
        window.addEventListener('resize', M3.debounce(() => { updateSpacer(); renderVisibleCards(); }, DEBOUNCE_MS));

        searchInput.addEventListener('input', M3.debounce(() => {
            activeSearch = searchInput.value.trim(); applyFilter(); updateSpacer(); firstVisibleRow = -1; renderVisibleCards(); updateDropdown();
        }, DEBOUNCE_MS));
        searchForm.addEventListener('submit', e => {
            e.preventDefault(); activeSearch = searchInput.value.trim(); applyFilter(); updateSpacer(); firstVisibleRow = -1; renderVisibleCards();
            if (filteredItems.length) jumpToCard(filteredItems[0].id); searchDropdown.style.display = 'none';
        });
        document.addEventListener('click', e => { if (!searchDropdown.contains(e.target) && e.target !== searchInput) searchDropdown.style.display = 'none'; });

        new IntersectionObserver(entries => { if (entries[0].isIntersecting && hasMore && !isFetching) loadNextBatch(); }, { rootMargin: `${SCROLL_THRESHOLD}px` }).observe(sentinel);
        loadNextBatch();
    }

    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();
})();
