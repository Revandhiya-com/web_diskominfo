/**
 * Portal PKL & Magang — Diskominfo Provinsi Banten
 * JavaScript utama (vanilla JS, tanpa dependency selain Bootstrap).
 */

(function () {
    'use strict';

    /* Back to top */
    var backToTop = document.getElementById('backToTop');

    if (backToTop) {
        var toggleBackToTop = function () {
            if (window.scrollY > 400) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        };

        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();

        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* Tutup dropdown mobile setelah item diklik (navigasi lebih mudah di HP) */
    document.querySelectorAll('#mainNavbar .dropdown-menu a').forEach(function (link) {
        link.addEventListener('click', function () {
            var navbar = document.getElementById('mainNavbar');
            if (navbar && navbar.classList.contains('show')) {
                var collapse = bootstrap.Collapse.getOrCreateInstance(navbar);
                collapse.hide();
            }
        });
    });

    /* ------------------------------------------------------------
       DROPDOWN NAVBAR — hanya satu dropdown yang boleh aktif.
       activeDropdown: 'profil' | 'layanan' | 'berita' |
                       'dokumentasi' | 'informasi' | null
       ------------------------------------------------------------ */
    var dropdownElements = {};
    var activeDropdown = null;

    var dropdownKey = function (dropdown) {
        var toggle = dropdown.querySelector('.dropdown-toggle');
        var href = toggle ? (toggle.getAttribute('href') || '') : '';
        var match = href.match(/[?&]page=([a-z-]+)/);
        switch (match ? match[1] : '') {
            case 'profil': return 'profil';
            case 'layanan': return 'layanan';
            case 'berita': return 'berita';
            case 'dokumentasi': return 'dokumentasi';
            case 'pengumuman': return 'informasi';
            default: return null;
        }
    };

    document.querySelectorAll('.dp-navbar .nav-item.dropdown').forEach(function (dropdown) {
        var key = dropdownKey(dropdown);
        if (key) dropdownElements[key] = dropdown;
    });

    var dropdownToggle = function (key) {
        var el = dropdownElements[key];
        return el ? el.querySelector('.dropdown-toggle') : null;
    };

    var dropdownInstance = function (key) {
        var toggle = dropdownToggle(key);
        return toggle ? bootstrap.Dropdown.getInstance(toggle) : null;
    };

    var isDesktop = function () { return window.innerWidth >= 992; };

    /* Tutup dropdown seketika (tanpa menunggu animasi) supaya
       tidak pernah ada dua dropdown yang terlihat bersamaan. */
    var closeDropdown = function (key) {
        var el = dropdownElements[key];
        if (!el) return;
        var menu = el.querySelector('.dropdown-menu');
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            menu.style.display = '';
        }
        var toggle = dropdownToggle(key);
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        var dd = dropdownInstance(key);
        if (dd) dd.hide();
        if (activeDropdown === key) activeDropdown = null;
    };

    /* Buka dropdown: menu lain WAJIB ditutup lebih dulu,
       lalu state aktif diganti ke key yang baru. */
    var openDropdown = function (key) {
        var el = dropdownElements[key];
        if (!el) return;
        Object.keys(dropdownElements).forEach(function (other) {
            if (other !== key) closeDropdown(other);
        });
        var toggle = dropdownToggle(key);
        if (toggle && !el.classList.contains('show')) {
            bootstrap.Dropdown.getOrCreateInstance(toggle).show();
        }
        activeDropdown = key;
    };

    Object.keys(dropdownElements).forEach(function (key) {
        var el = dropdownElements[key];
        el.addEventListener('mouseenter', function () {
            if (!isDesktop()) return;
            openDropdown(key);
        });
        el.addEventListener('mouseleave', function () {
            if (!isDesktop()) return;
            closeDropdown(key);
        });
    });

    /* Pengaman utama: apa pun pemicunya (hover, klik, fokus, keyboard),
       sebelum sebuah dropdown tampil, semua dropdown lain ditutup paksa
       secara instan sehingga tidak pernah menumpuk. */
    var navbarEl = document.querySelector('.dp-navbar');
    if (navbarEl) {
        navbarEl.addEventListener('show.bs.dropdown', function (e) {
            var target = e.target && e.target.closest ? e.target.closest('.nav-item.dropdown') : null;
            var key = target ? dropdownKey(target) : null;
            Object.keys(dropdownElements).forEach(function (other) {
                if (other !== key && dropdownElements[other].classList.contains('show')) {
                    closeDropdown(other);
                }
            });
            if (key) activeDropdown = key;
        });
    }

    /* ------------------------------------------------------------
       SALIN TAUTAN (share/copy link) — detail berita dll.
       ------------------------------------------------------------ */
    var initCopyLink = function () {
        document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var url = btn.getAttribute('data-copy-link');
                var label = btn.querySelector('.dp-copy-label');
                var original = label ? label.textContent : '';

                var feedback = function (ok) {
                    if (!label) return;
                    label.textContent = ok ? 'Tersalin!' : 'Gagal menyalin';
                    setTimeout(function () { label.textContent = original; }, 2000);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(
                        function () { feedback(true); },
                        function () { feedback(false); }
                    );
                    return;
                }

                /* Fallback untuk browser lama / non-secure context */
                var ta = document.createElement('textarea');
                ta.value = url;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                var ok = false;
                try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
                document.body.removeChild(ta);
                feedback(ok);
            });
        });
    };

    /* ------------------------------------------------------------
       LIGHTBOX galeri foto (album)
       ------------------------------------------------------------ */
    var initLightbox = function () {
        var lightbox = document.getElementById('dpLightbox');
        if (!lightbox) return;

        var img = lightbox.querySelector('.dp-lightbox-img');
        var caption = lightbox.querySelector('.dp-lightbox-caption');
        var counter = lightbox.querySelector('.dp-lightbox-count');
        var prevBtn = lightbox.querySelector('.dp-lightbox-prev');
        var nextBtn = lightbox.querySelector('.dp-lightbox-next');
        var closeBtns = lightbox.querySelectorAll('[data-lightbox-close]');

        var items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
        if (items.length === 0) return;

        var index = 0;
        var lastFocused = null;

        var show = function (i) {
            index = (i + items.length) % items.length;
            var item = items[index];
            img.src = item.getAttribute('data-full');
            img.alt = item.getAttribute('data-caption') || '';
            caption.textContent = item.getAttribute('data-caption') || '';
            counter.textContent = (index + 1) + ' / ' + items.length;
            prevBtn.style.display = items.length > 1 ? '' : 'none';
            nextBtn.style.display = items.length > 1 ? '' : 'none';
        };

        var open = function (i, trigger) {
            lastFocused = trigger;
            lightbox.classList.add('show');
            lightbox.setAttribute('aria-hidden', 'false');
            show(i);
            var closeBtn = lightbox.querySelector('.dp-lightbox-close');
            if (closeBtn) closeBtn.focus();
            document.body.style.overflow = 'hidden';
        };

        var close = function () {
            lightbox.classList.remove('show');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (lastFocused) lastFocused.focus();
        };

        items.forEach(function (item, i) {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                open(i, this);
            });
        });

        prevBtn.addEventListener('click', function () { show(index - 1); });
        nextBtn.addEventListener('click', function () { show(index + 1); });

        closeBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.classList.contains('dp-lightbox-backdrop')) return;
                close();
            });
            btn.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.key === 'Enter' || e.key === ' ') close();
            });
        });

        /* Klik backdrop menutup */
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox || e.target.classList.contains('dp-lightbox-backdrop')) {
                close();
            }
        });

        /* Keyboard: Esc, panah kiri/kanan, dan focus trap di dalam lightbox */
        document.addEventListener('keydown', function (e) {
            if (!lightbox.classList.contains('show')) return;
            if (e.key === 'Escape') { close(); return; }
            if (e.key === 'ArrowLeft') { show(index - 1); return; }
            if (e.key === 'ArrowRight') { show(index + 1); return; }
            if (e.key === 'Tab') {
                var focusables = lightbox.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])');
                if (focusables.length === 0) return;
                var first = focusables[0];
                var last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });
    };

    /* ------------------------------------------------------------
       TEMA (gelap/terang) — toggle manual + ikuti sistem
       ------------------------------------------------------------ */
    var initTheme = function () {
        var toggle = document.getElementById('themeToggle');
        if (!toggle) return;

        var icon = toggle.querySelector('i');
        var apply = function () {
            var dark = document.documentElement.classList.contains('dp-dark');
            if (icon) {
                icon.className = dark ? 'bi bi-sun' : 'bi bi-moon-stars';
            }
            toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
            toggle.setAttribute('aria-label', dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
            toggle.setAttribute('title', dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
        };

        toggle.addEventListener('click', function () {
            var root = document.documentElement;
            var dark = root.classList.contains('dp-dark');
            var next = !dark;
            root.classList.toggle('dp-dark', next);
            root.classList.toggle('dp-light', !next);
            root.setAttribute('data-bs-theme', next ? 'dark' : 'light');
            try { localStorage.setItem('dp-theme', next ? 'dark' : 'light'); } catch (e) {}
            apply();
        });

        apply();
    };

    /* ------------------------------------------------------------
       INTRO BERANDA — memberi pembuka sinematik tanpa mengunci
       scroll seperti komponen video referensi. Selalu tersedia
       tombol masuk, Escape, serta durasi singkat untuk aksesibilitas.
       ------------------------------------------------------------ */
    var initBantenIntro = function () {
        var intro = document.getElementById('bantenIntro');
        if (!intro) return;

        var skip = document.getElementById('bantenIntroSkip');
        var closed = false;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var duration = reduceMotion ? 700 : 4200;
        var timer;

        var closeIntro = function () {
            if (closed) return;
            closed = true;
            window.clearTimeout(timer);
            intro.classList.add('is-leaving');
            document.body.classList.remove('banten-intro-active');
            window.setTimeout(function () {
                intro.setAttribute('hidden', '');
                intro.remove();
            }, reduceMotion ? 0 : 700);
        };

        if (skip) skip.addEventListener('click', closeIntro);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeIntro();
        });
        timer = window.setTimeout(closeIntro, duration);
    };

    initCopyLink();
    initLightbox();
    initTheme();
    initBantenIntro();
})();
