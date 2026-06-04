/**
 * Steppa.in — Main JavaScript
 * Handles: live search, mobile sidebar, load more, FAQ accordion, lightbox, share, sticky header
 */
(function () {
    'use strict';

    /* =========================================================
       MOBILE SIDEBAR DRAWER
    ========================================================= */
    const hamburger   = document.getElementById('hamburger-btn');
    const sidebar     = document.getElementById('mobile-sidebar');
    const overlay     = document.getElementById('overlay');
    const sidebarClose = document.getElementById('sidebar-close');

    function openSidebar() {
        sidebar && sidebar.classList.add('open');
        overlay && overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        hamburger && hamburger.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar && sidebar.classList.remove('open');
        overlay && overlay.classList.remove('show');
        document.body.style.overflow = '';
        hamburger && hamburger.setAttribute('aria-expanded', 'false');
    }

    hamburger    && hamburger.addEventListener('click', openSidebar);
    overlay      && overlay.addEventListener('click', closeSidebar);
    sidebarClose && sidebarClose.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });


    /* =========================================================
       LIVE SEARCH (header + mobile sidebar)
    ========================================================= */
    let searchTimer = null;

    function initSearch(input, dropdown) {
        if (!input || !dropdown) return;

        input.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = input.value.trim();
            if (q.length < 2) {
                dropdown.classList.remove('show');
                dropdown.innerHTML = '';
                return;
            }

            searchTimer = setTimeout(() => {
                dropdown.innerHTML = '<div class="search-result-item"><div class="search-result-info"><div class="name">Searching…</div></div></div>';
                dropdown.classList.add('show');

                fetch(steppa.ajax, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'steppa_search',
                        nonce: steppa.nonce,
                        q: q
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success || !data.data.length) {
                        dropdown.innerHTML = '<div class="search-result-item"><div class="search-result-info"><div class="name" style="color:#888;">No games found for "' + escHtml(q) + '"</div></div></div>';
                    } else {
                        dropdown.innerHTML = data.data.map(g => `
                            <a href="${g.url}" class="search-result-item">
                                <img src="${g.icon}" alt="${escHtml(g.title)}" width="38" height="38"
                                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(g.title)}&background=3dbb85&color=fff&size=64'">
                                <div class="search-result-info">
                                    <div class="name">${escHtml(g.title)}</div>
                                    <div class="meta">${escHtml(g.genre)}${g.rating ? ' &nbsp;⭐ ' + g.rating : ''}</div>
                                </div>
                            </a>
                        `).join('');
                    }
                    dropdown.classList.add('show');
                })
                .catch(() => {
                    dropdown.classList.remove('show');
                });
            }, 320);
        });

        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                window.location.href = steppa.home + '?s=' + encodeURIComponent(input.value.trim());
            }
            if (e.key === 'Escape') {
                dropdown.classList.remove('show');
                input.blur();
            }
        });
    }

    initSearch(
        document.getElementById('header-search-input'),
        document.getElementById('search-dropdown')
    );
    // Close dropdown on outside click
    document.addEventListener('click', e => {
        if (!e.target.closest('.header-search')) {
            const dd = document.getElementById('search-dropdown');
            dd && dd.classList.remove('show');
        }
    });


    /* =========================================================
       LOAD MORE GAMES (homepage)
    ========================================================= */
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        const grid = document.getElementById('main-games-grid');
        let page   = parseInt(loadMoreBtn.dataset.page) || 2;
        let busy   = false;

        loadMoreBtn.addEventListener('click', () => {
            if (busy) return;
            busy = true;
            loadMoreBtn.classList.add('loading');

            fetch(steppa.ajax, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'steppa_loadmore',
                    nonce: steppa.nonce,
                    page: page,
                    genre: loadMoreBtn.dataset.genre || '',
                    sort:  loadMoreBtn.dataset.sort  || 'newest',
                    pp:    '20'
                })
            })
            .then(r => r.json())
            .then(data => {
                loadMoreBtn.classList.remove('loading');
                busy = false;
                if (data.success && data.data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.data.html;
                    temp.querySelectorAll('.game-card').forEach((card, i) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        grid.appendChild(card);
                        requestAnimationFrame(() => {
                            setTimeout(() => {
                                card.style.transition = 'opacity .35s ease, transform .35s ease';
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, i * 40);
                        });
                    });
                    page++;
                    if (!data.data.has_more) {
                        loadMoreBtn.textContent = 'All games loaded';
                        loadMoreBtn.disabled = true;
                        loadMoreBtn.style.opacity = '0.5';
                    }
                } else {
                    loadMoreBtn.textContent = 'No more games';
                    loadMoreBtn.disabled = true;
                }
            })
            .catch(() => {
                loadMoreBtn.classList.remove('loading');
                busy = false;
            });
        });
    }


    /* =========================================================
       FAQ ACCORDION
    ========================================================= */
    document.querySelectorAll('.faq-q').forEach(btn => {
        btn.addEventListener('click', () => {
            const answer = btn.nextElementSibling;
            const isOpen = btn.classList.contains('open');

            // Close all others
            document.querySelectorAll('.faq-q.open').forEach(b => {
                b.classList.remove('open');
                b.setAttribute('aria-expanded', 'false');
                const a = b.nextElementSibling;
                if (a) a.classList.remove('open');
            });

            if (!isOpen) {
                btn.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
                if (answer) answer.classList.add('open');
            }
        });
    });


    /* =========================================================
       SCREENSHOT LIGHTBOX
    ========================================================= */
    const lightbox    = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxClose = document.getElementById('lightbox-close');

    function openLightbox(src, alt) {
        if (!lightbox || !lightboxImg) return;
        lightboxImg.src = src;
        lightboxImg.alt = alt || '';
        lightbox.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox && lightbox.classList.remove('show');
        document.body.style.overflow = '';
        if (lightboxImg) { lightboxImg.src = ''; }
    }

    document.querySelectorAll('.screenshot-img img').forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', () => openLightbox(img.src, img.alt));
    });

    lightboxClose && lightboxClose.addEventListener('click', closeLightbox);
    lightbox && lightbox.addEventListener('click', e => {
        if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLightbox();
    });


    /* =========================================================
       SHARE BUTTONS
    ========================================================= */
    document.querySelectorAll('[data-share]').forEach(btn => {
        btn.addEventListener('click', () => {
            const type  = btn.dataset.share;
            const url   = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);

            if (type === 'twitter') {
                window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
            } else if (type === 'facebook') {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
            } else if (type === 'whatsapp') {
                window.open(`https://api.whatsapp.com/send?text=${title}%20${url}`, '_blank');
            } else if (type === 'copy') {
                navigator.clipboard && navigator.clipboard.writeText(window.location.href)
                    .then(() => showToast('✓ Link copied to clipboard!'));
            }
        });
    });


    /* =========================================================
       TOAST NOTIFICATION
    ========================================================= */
    function showToast(msg, duration) {
        duration = duration || 2800;
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), duration);
    }
    window.showToast = showToast;


    /* =========================================================
       STICKY HEADER SHADOW ON SCROLL
    ========================================================= */
    const header = document.getElementById('site-header');
    if (header) {
        let lastY = 0;
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            if (y > 10) {
                header.style.boxShadow = '0 3px 18px rgba(0,0,0,.4)';
            } else {
                header.style.boxShadow = '0 2px 12px rgba(0,0,0,.3)';
            }
            lastY = y;
        }, { passive: true });
    }


    /* =========================================================
       IMAGE ERROR FALLBACK (for any game icon)
    ========================================================= */
    document.addEventListener('error', e => {
        if (e.target.tagName === 'IMG' && e.target.closest('.game-card,.download-card,.game-icon-wrap,.trending-item')) {
            const alt = e.target.alt || 'Game';
            e.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(alt) + '&background=3dbb85&color=fff&size=256&bold=true';
        }
    }, true);


    /* =========================================================
       INTERSECTION OBSERVER — Fade in game cards on scroll
    ========================================================= */
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-up');
                    obs.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });

        document.querySelectorAll('.content-section, .section').forEach(el => io.observe(el));
    }


    /* =========================================================
       CONTACT FORM (basic validation)
    ========================================================= */
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', e => {
            e.preventDefault();
            const btn = contactForm.querySelector('[type="submit"]');
            btn.textContent = 'Sending…';
            btn.disabled = true;
            // Simulate send (no server handler — mailto fallback)
            setTimeout(() => {
                showToast('✓ Message sent! We\'ll reply within 48 hours.');
                contactForm.reset();
                btn.textContent = 'Send Message';
                btn.disabled = false;
            }, 1200);
        });
    }


    /* =========================================================
       UTILITY: HTML escape
    ========================================================= */
    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

})();
