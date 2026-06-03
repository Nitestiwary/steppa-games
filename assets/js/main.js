/**
 * Steppa Discover - Main JS
 * Handles: search, mobile drawer, load more, FAQ accordion, filters, lightbox, share
 */
(function () {
    'use strict';

    /* ---- Search Autocomplete ---- */
    const searchInputs = document.querySelectorAll('.nav-search-input, .archive-search-input');
    let searchTimeout = null;

    searchInputs.forEach(input => {
        const dropdown = input.closest('.nav-search, .archive-search')?.querySelector('.search-dropdown');
        if (!input || !dropdown) return;

        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const q = input.value.trim();
            if (q.length < 2) { dropdown.classList.remove('active'); return; }

            searchTimeout = setTimeout(() => {
                fetch(steppaData.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'steppa_search',
                        nonce: steppaData.nonce,
                        q: q
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success || !data.data.length) {
                        dropdown.innerHTML = '<div class="search-result-item"><div class="search-result-info"><div class="search-result-title">No games found</div></div></div>';
                    } else {
                        dropdown.innerHTML = data.data.map(g => `
                            <a href="${g.url}" class="search-result-item">
                                <img class="search-result-icon" src="${g.icon}" alt="${g.title}" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(g.title)}&background=7c3aed&color=fff&size=256'">
                                <div class="search-result-info">
                                    <div class="search-result-title">${g.title}</div>
                                    <div class="search-result-meta">⭐ ${g.rating} &bull; ${g.genre}</div>
                                </div>
                            </a>
                        `).join('');
                    }
                    dropdown.classList.add('active');
                })
                .catch(() => { dropdown.classList.remove('active'); });
            }, 300);
        });

        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                window.location.href = steppaData.searchUrl + '?s=' + encodeURIComponent(input.value.trim());
            }
            if (e.key === 'Escape') { dropdown.classList.remove('active'); }
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('.nav-search, .archive-search')) dropdown.classList.remove('active');
        });
    });

    /* ---- Mobile Drawer ---- */
    const hamburger = document.getElementById('nav-hamburger');
    const drawer = document.getElementById('mobile-drawer');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const drawerClose = document.getElementById('drawer-close');

    function openDrawer() {
        drawer?.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer?.classList.remove('open');
        document.body.style.overflow = '';
    }

    hamburger?.addEventListener('click', openDrawer);
    drawerOverlay?.addEventListener('click', closeDrawer);
    drawerClose?.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeDrawer();
    });

    /* ---- Load More Games ---- */
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        let page = 2;
        const grid = document.getElementById('games-grid');
        const genre = loadMoreBtn.dataset.genre || '';
        const sort = loadMoreBtn.dataset.sort || 'newest';
        const perPage = parseInt(loadMoreBtn.dataset.per_page || '24');

        loadMoreBtn.addEventListener('click', () => {
            loadMoreBtn.classList.add('loading');

            fetch(steppaData.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'steppa_load_more',
                    nonce: steppaData.nonce,
                    page: page,
                    genre: genre,
                    sort: sort,
                    per_page: perPage,
                    install_filter: loadMoreBtn.dataset.install_filter || ''
                })
            })
            .then(r => r.json())
            .then(data => {
                loadMoreBtn.classList.remove('loading');
                if (data.success && data.data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.data.html;
                    temp.querySelectorAll('.game-card').forEach(card => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(24px)';
                        grid.appendChild(card);
                        setTimeout(() => {
                            card.style.transition = 'all 0.4s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 50);
                    });
                    page++;
                    if (!data.data.has_more) {
                        loadMoreBtn.style.display = 'none';
                    }
                } else {
                    loadMoreBtn.textContent = 'No more games';
                    loadMoreBtn.disabled = true;
                }
            })
            .catch(() => {
                loadMoreBtn.classList.remove('loading');
            });
        });
    }

    /* ---- Trending Tabs ---- */
    const trendingTabs = document.querySelectorAll('.trending-tab');
    const trendingContainers = document.querySelectorAll('.trending-container');

    trendingTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            trendingTabs.forEach(t => t.classList.remove('active'));
            trendingContainers.forEach(c => c.classList.add('hidden'));
            tab.classList.add('active');
            const target = document.getElementById(`trending-${tab.dataset.period}`);
            target?.classList.remove('hidden');
        });
    });

    /* ---- Install Filter Tabs ---- */
    const installTabs = document.querySelectorAll('.install-tab');
    installTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            installTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            if (loadMoreBtn) {
                loadMoreBtn.dataset.install_filter = tab.dataset.filter;
                loadMoreBtn.dataset.page = '1';
                page = 2;
            }

            const grid = document.getElementById('games-grid');
            if (!grid) return;

            const filter = tab.dataset.filter;
            grid.style.opacity = '0.5';

            fetch(steppaData.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'steppa_load_more',
                    nonce: steppaData.nonce,
                    page: 1,
                    sort: 'installs',
                    install_filter: filter
                })
            })
            .then(r => r.json())
            .then(data => {
                grid.style.opacity = '1';
                if (data.success && data.data.html) {
                    grid.innerHTML = data.data.html;
                }
            })
            .catch(() => { grid.style.opacity = '1'; });
        });
    });

    /* ---- FAQ Accordion ---- */
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const answer = btn.nextElementSibling;
            const isOpen = btn.classList.contains('open');

            document.querySelectorAll('.faq-question').forEach(b => {
                b.classList.remove('open');
                b.nextElementSibling?.classList.remove('open');
            });

            if (!isOpen) {
                btn.classList.add('open');
                answer?.classList.add('open');
            }
        });
    });

    /* ---- Screenshot Lightbox ---- */
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');

    document.querySelectorAll('.screenshot-img').forEach(img => {
        img.addEventListener('click', () => {
            if (!lightbox || !lightboxImg) return;
            lightboxImg.src = img.src;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    document.getElementById('lightbox-close')?.addEventListener('click', () => {
        lightbox?.classList.remove('active');
        document.body.style.overflow = '';
    });

    lightbox?.addEventListener('click', e => {
        if (e.target === lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    /* ---- Filter Form ---- */
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', e => {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(filterForm));
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    }

    /* ---- Share Buttons ---- */
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.share;
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);

            if (type === 'twitter') {
                window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
            } else if (type === 'facebook') {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
            } else if (type === 'copy') {
                navigator.clipboard.writeText(window.location.href).then(() => showToast('✓ Link copied!'));
            }
        });
    });

    /* ---- Toast ---- */
    function showToast(message, duration = 2500) {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), duration);
    }

    window.showToast = showToast;

    /* ---- Scroll Animations ---- */
    const animElements = document.querySelectorAll('.fade-in-up');
    if (animElements.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fade-in-up 0.6s ease forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animElements.forEach(el => observer.observe(el));
    }

    /* ---- Image Error Fallback ---- */
    document.querySelectorAll('img.game-icon-large, img.game-card-icon, img.trending-icon').forEach(img => {
        img.addEventListener('error', function () {
            const name = this.alt || 'Game';
            this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=7c3aed&color=fff&size=256&bold=true`;
        });
    });

    /* ---- Active Nav Link ---- */
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) link.classList.add('active');
    });

})();
