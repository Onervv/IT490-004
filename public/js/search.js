// search.js - simple in-page search/highlight

function clearHighlights() {
    document.querySelectorAll('.search-highlight').forEach(el => {
        const parent = el.parentNode;
        parent.replaceChild(document.createTextNode(el.textContent), el);
        parent.normalize();
    });
}

function highlightText(text) {
    if (!text) return;
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null);
    const re = new RegExp(text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
    const matches = [];
    while(walker.nextNode()) {
        const node = walker.currentNode;
        const match = re.exec(node.nodeValue);
        if (match) {
            const span = document.createElement('span');
            span.className = 'search-highlight';
            const before = node.nodeValue.slice(0, match.index);
            const after = node.nodeValue.slice(match.index + match[0].length);
            span.textContent = match[0];
            const afterNode = document.createTextNode(after);
            node.nodeValue = before;
            node.parentNode.insertBefore(span, node.nextSibling);
            node.parentNode.insertBefore(afterNode, span.nextSibling);
            matches.push(span);
            // continue searching in after part
            walker.currentNode = afterNode;
        }
    }
    return matches;
}

// wire up nav search
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('navSearchForm');
    const input = document.getElementById('navSearchInput');
    form.addEventListener('submit', e => {
        e.preventDefault();
        clearHighlights();
        const term = input.value.trim();
        if (term.length === 0) return;
        const results = highlightText(term);
        if (results && results.length) {
            results[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});