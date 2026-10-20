document.addEventListener('DOMContentLoaded', () => {
    const htmlEl = document.documentElement;

    // =========================================================================
    // 1. MOBILE-LIKE PUSH NOTIFICATION TOASTER + MINI TIMEOUT LOADING BAR
    // =========================================================================
    function ensureSonnerViewport() {
        let vp = document.getElementById('sonnerViewport');
        if (!vp) {
            vp = document.createElement('div');
            vp.id = 'sonnerViewport';
            vp.className = 'sonner-viewport';
            vp.setAttribute('aria-live', 'polite');
            document.body.appendChild(vp);
        }
        return vp;
    }

    function createToast(title, options = {}) {
        const viewport = ensureSonnerViewport();
        const type = options.type || 'info';
        const description = options.description || '';
        const duration = typeof options.duration === 'number' ? options.duration : 3400;
        const action = options.action || null;

        const iconMap = {
            success: {
                icon: 'bi-check-circle-fill',
                badge: 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/15 border-emerald-500/25',
                bar: 'bg-gradient-to-r from-emerald-500 to-teal-400',
                tag: 'Berhasil',
            },
            error: {
                icon: 'bi-x-octagon-fill',
                badge: 'text-rose-600 dark:text-rose-400 bg-rose-500/15 border-rose-500/25',
                bar: 'bg-gradient-to-r from-rose-500 to-orange-500',
                tag: 'Peringatan',
            },
            warning: {
                icon: 'bi-exclamation-triangle-fill',
                badge: 'text-amber-600 dark:text-amber-400 bg-amber-500/15 border-amber-500/25',
                bar: 'bg-gradient-to-r from-amber-500 to-yellow-400',
                tag: 'Perhatian',
            },
            info: {
                icon: 'bi-bell-fill',
                badge: 'text-blue-600 dark:text-blue-400 bg-blue-500/15 border-blue-500/25',
                bar: 'bg-gradient-to-r from-blue-600 to-sky-400',
                tag: 'Info Sistem',
            },
        };
        const style = iconMap[type] || iconMap.info;

        const toastEl = document.createElement('div');
        toastEl.className = 'sonner-toast group';
        toastEl.innerHTML = `
            <div class="w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 ${style.badge}">
                <i class="bi ${style.icon} text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-0.5">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                        Inventaris Diskominfo &bull; ${style.tag}
                    </span>
                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">Baru saja</span>
                </div>
                <div class="text-xs sm:text-[13px] font-bold text-slate-900 dark:text-white leading-snug">${title}</div>
                ${description ? `<p class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed line-clamp-2">${description}</p>` : ''}
                ${action ? `
                    <div class="mt-2">
                        <button type="button" class="sonner-action-btn inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-2xs transition-all cursor-pointer">
                            <span>${action.label || 'Lihat'}</span>
                            <i class="bi bi-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                ` : ''}
            </div>
            <button type="button" class="sonner-close-btn text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0" aria-label="Tutup">
                <i class="bi bi-x-lg text-[11px]"></i>
            </button>
            ${duration > 0 ? `
                <div class="sonner-progress-track">
                    <div class="sonner-progress-bar ${style.bar}" style="animation-duration: ${duration}ms"></div>
                </div>
            ` : ''}
        `;

        let dismissTimer = null;
        const dismiss = () => {
            if (toastEl.classList.contains('is-leaving')) return;
            if (dismissTimer) clearTimeout(dismissTimer);
            toastEl.classList.add('is-leaving');
            setTimeout(() => toastEl.remove(), 180);
        };

        toastEl.querySelector('.sonner-close-btn')?.addEventListener('click', dismiss);

        // Touch swipe up / horizontal to dismiss like native mobile notification
        let touchStartY = 0;
        let touchStartX = 0;
        toastEl.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                touchStartY = e.touches[0].clientY;
                touchStartX = e.touches[0].clientX;
            }
        }, { passive: true });

        toastEl.addEventListener('touchend', (e) => {
            if (e.changedTouches.length === 1) {
                const dy = e.changedTouches[0].clientY - touchStartY;
                const dx = e.changedTouches[0].clientX - touchStartX;
                if (dy < -24 || Math.abs(dx) > 65) {
                    dismiss();
                }
            }
        }, { passive: true });

        if (action) {
            toastEl.querySelector('.sonner-action-btn')?.addEventListener('click', () => {
                if (typeof action.onClick === 'function') {
                    action.onClick();
                } else if (action.href) {
                    window.location.href = action.href;
                }
                dismiss();
            });
        }

        while (viewport.children.length >= 3) {
            viewport.firstElementChild?.remove();
        }

        viewport.appendChild(toastEl);

        if (duration > 0) {
            dismissTimer = setTimeout(dismiss, duration);
        }

        return { dismiss, el: toastEl };
    }

    window.toast = Object.assign(
        (title, opts = {}) => createToast(title, opts),
        {
            success: (title, opts = {}) => createToast(title, { ...opts, type: 'success' }),
            error: (title, opts = {}) => createToast(title, { ...opts, type: 'error' }),
            warning: (title, opts = {}) => createToast(title, { ...opts, type: 'warning' }),
            info: (title, opts = {}) => createToast(title, { ...opts, type: 'info' }),
        }
    );
    window.showToast = (title, type = 'info', opts = {}) => {
        const fn = window.toast[type] || window.toast;
        return fn(title, opts);
    };

    // Trigger server-side flash notification (#initialFlashToastData) with mini timeout bar
    const initialFlashEl = document.getElementById('initialFlashToastData');
    if (initialFlashEl && initialFlashEl.dataset.toast && !initialFlashEl.dataset.shown) {
        initialFlashEl.dataset.shown = '1';
        try {
            const flashPayload = JSON.parse(initialFlashEl.dataset.toast);
            const toastFn = window.toast[flashPayload.type] || window.toast.info;
            toastFn(flashPayload.title || 'Notifikasi Sistem', {
                description: flashPayload.description || '',
                duration: 3500,
            });
        } catch (e) {}
    }

    // Auto-dismiss legacy #flashToast if present
    const flashToast = document.getElementById('flashToast');
    if (flashToast) {
        setTimeout(() => {
            flashToast.classList.add('opacity-0', 'translate-y-2', 'transition-all', 'duration-300');
            setTimeout(() => flashToast.remove(), 320);
        }, 4500);
    }

    // =========================================================================
    // 2. ANIMATED DARK / LIGHT / SYSTEM THEME ENGINE (ZERO-BLINK VIEW TRANSITION)
    // =========================================================================
    const systemDarkMedia = window.matchMedia('(prefers-color-scheme: dark)');

    function getThemePreference() {
        try {
            const saved = localStorage.getItem('theme') || localStorage.getItem('color-theme');
            if (saved === 'dark' || saved === 'light' || saved === 'system') {
                return saved;
            }
        } catch (e) {}
        return 'light';
    }

    function resolveEffectiveDark(pref) {
        if (pref === 'system') {
            return systemDarkMedia.matches;
        }
        return pref === 'dark';
    }

    function syncThemeUI(pref, isDark) {
        const darkModeIcon = document.getElementById('darkModeIcon');
        const darkModeIconMobile = document.getElementById('darkModeIconMobile');
        const darkModeTextMobile = document.getElementById('darkModeTextMobile');

        if (darkModeIcon) {
            darkModeIcon.className = isDark
                ? 'bi bi-sun-fill text-lg text-amber-400'
                : 'bi bi-moon-stars text-lg text-slate-600';
        }
        if (darkModeIconMobile) {
            darkModeIconMobile.className = isDark
                ? 'bi bi-sun-fill text-amber-400'
                : 'bi bi-moon-stars text-slate-500';
        }
        if (darkModeTextMobile) {
            darkModeTextMobile.textContent = pref === 'system'
                ? 'Tema Sistem'
                : (isDark ? 'Mode Terang' : 'Mode Gelap');
        }

        document.querySelectorAll('[data-theme-option]').forEach((card) => {
            const val = card.getAttribute('data-theme-option');
            const isSelected = val === pref;
            card.classList.toggle('border-blue-600', isSelected);
            card.classList.toggle('ring-2', isSelected);
            card.classList.toggle('ring-blue-500/20', isSelected);
            card.classList.toggle('bg-blue-50/40', isSelected && !isDark);
            card.classList.toggle('dark:bg-blue-950/30', isSelected);

            const checkBadge = card.querySelector('.theme-check-indicator');
            if (checkBadge) {
                checkBadge.classList.toggle('hidden', !isSelected);
            }
        });
    }

    function applyTheme(preference, options = { animate: true, notify: false }) {
        const pref = ['light', 'dark', 'system'].includes(preference) ? preference : 'light';
        const isDark = resolveEffectiveDark(pref);

        const performUpdate = () => {
            htmlEl.classList.toggle('dark', isDark);
            htmlEl.setAttribute('data-theme', isDark ? 'dark' : 'light');
            htmlEl.dataset.themeMode = pref;
            try {
                localStorage.setItem('theme', pref);
                localStorage.setItem('color-theme', pref);
            } catch (e) {}
            syncThemeUI(pref, isDark);
        };

        if (options.animate) {
            if (typeof document.startViewTransition === 'function') {
                try {
                    htmlEl.setAttribute('data-theme-switching', '1');
                    const vt = document.startViewTransition(() => performUpdate());
                    vt.finished.finally(() => {
                        htmlEl.removeAttribute('data-theme-switching');
                    });
                } catch (e) {
                    htmlEl.removeAttribute('data-theme-switching');
                    performUpdate();
                }
            } else {
                htmlEl.classList.add('theme-transitioning');
                performUpdate();
                setTimeout(() => htmlEl.classList.remove('theme-transitioning'), 260);
            }
        } else {
            performUpdate();
        }

        if (options.notify && window.toast) {
            const labels = {
                light: 'Mode Terang diaktifkan',
                dark: 'Mode Gelap diaktifkan',
                system: `Mengikuti Tema Sistem (${isDark ? 'Gelap' : 'Terang'})`,
            };
            window.toast.info(labels[pref] || 'Tema diperbarui', { duration: 2200 });
        }
    }

    window.applyTheme = applyTheme;
    window.getThemePreference = getThemePreference;

    // Initial theme sync without animation
    applyTheme(getThemePreference(), { animate: false, notify: false });

    // Listen to OS theme changes when preference is 'system'
    systemDarkMedia.addEventListener?.('change', () => {
        if (getThemePreference() === 'system') {
            applyTheme('system', { animate: true, notify: false });
        }
    });

    function toggleDarkLightQuick() {
        const currentDark = htmlEl.classList.contains('dark');
        applyTheme(currentDark ? 'light' : 'dark', { animate: true, notify: true });
    }

    document.getElementById('darkModeToggle')?.addEventListener('click', toggleDarkLightQuick);
    document.getElementById('darkModeToggleMobile')?.addEventListener('click', toggleDarkLightQuick);

    document.querySelectorAll('[data-theme-option]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetTheme = btn.getAttribute('data-theme-option') || 'light';
            applyTheme(targetTheme, { animate: true, notify: true });
        });
    });

    // =========================================================================
    // 3. GLOBAL TORN-PAPER BROKEN IMAGE FALLBACK
    // =========================================================================
    const TORN_PAPER_FALLBACK = '/images/fallback-torn-paper.svg';

    function attachBrokenImageFallback(img) {
        if (!img || img.dataset.fallbackBound === '1') return;
        img.dataset.fallbackBound = '1';
        img.addEventListener('error', function () {
            if (this.src && !this.src.includes('fallback-torn-paper.svg')) {
                this.src = TORN_PAPER_FALLBACK;
                this.classList.add('object-contain', 'p-1');
            }
        });
        if (img.complete && img.naturalWidth === 0 && img.getAttribute('src')) {
            if (!img.src.includes('fallback-torn-paper.svg')) {
                img.src = TORN_PAPER_FALLBACK;
            }
        }
    }

    document.querySelectorAll('img').forEach(attachBrokenImageFallback);
    window.addEventListener('error', (event) => {
        const target = event.target;
        if (target && target.tagName === 'IMG' && !target.src.includes('fallback-torn-paper.svg')) {
            target.src = TORN_PAPER_FALLBACK;
        }
    }, true);

    // =========================================================================
    // 4. COPY-TO-CLIPBOARD ENGINE ([data-copy])
    // =========================================================================
    async function copyTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return true;
        }
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            return true;
        } finally {
            ta.remove();
        }
    }

    document.addEventListener('click', async (event) => {
        const copyTrigger = event.target.closest('[data-copy]');
        if (!copyTrigger) return;

        event.preventDefault();
        event.stopPropagation();

        const value = copyTrigger.getAttribute('data-copy') || '';
        const label = copyTrigger.getAttribute('data-copy-label') || 'Kode';
        if (!value) return;

        try {
            await copyTextToClipboard(value);
            const icon = copyTrigger.querySelector('i.bi');
            const origClass = icon ? icon.className : '';
            if (icon) {
                icon.className = 'bi bi-check2 text-emerald-500 font-bold';
                setTimeout(() => {
                    icon.className = origClass;
                }, 1600);
            }
            window.toast?.success(`${label} disalin!`, {
                description: value,
                duration: 2400,
            });
        } catch (err) {
            window.toast?.error('Gagal menyalin teks ke clipboard');
        }
    });

    // =========================================================================
    // 5. CUSTOM SEARCHABLE SELECT (TOP-LAYER PORTAL + AUTO-FLIP + STACKING FIX)
    // =========================================================================
    function positionFloatingOverlay(triggerEl, floatingEl, opts = {}) {
        if (!triggerEl || !floatingEl) return;
        const rect = triggerEl.getBoundingClientRect();
        if (rect.width === 0 && rect.height === 0) return;

        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const margin = 8;
        const gap = 6;

        let width = opts.fixedWidth
            ? Math.min(opts.fixedWidth, vw - margin * 2)
            : Math.max(rect.width, opts.minWidth || 210);
        width = Math.min(width, vw - margin * 2);

        const left = Math.max(margin, Math.min(rect.left, vw - width - margin));
        const spaceBelow = vh - rect.bottom - margin;
        const spaceAbove = rect.top - margin;
        const estimatedHeight = Math.min(floatingEl.scrollHeight || 260, 290);

        floatingEl.style.position = 'fixed';
        floatingEl.style.left = `${Math.round(left)}px`;
        floatingEl.style.right = 'auto';
        floatingEl.style.width = `${Math.round(width)}px`;
        floatingEl.style.zIndex = '99999';

        if (spaceBelow < estimatedHeight && spaceAbove > spaceBelow) {
            floatingEl.style.top = 'auto';
            floatingEl.style.bottom = `${Math.max(margin, Math.round(vh - rect.top + gap))}px`;
            floatingEl.style.transformOrigin = 'bottom center';
        } else {
            floatingEl.style.bottom = 'auto';
            floatingEl.style.top = `${Math.max(margin, Math.round(rect.bottom + gap))}px`;
            floatingEl.style.transformOrigin = 'top center';
        }
    }

    function syncAncestorDropdownState() {
        document.querySelectorAll('.has-open-dropdown').forEach((el) => {
            if (!el.querySelector('.ss-wrapper.is-open, .cdp-wrapper.is-open')) {
                el.classList.remove('has-open-dropdown');
            }
        });

        document.querySelectorAll('.ss-wrapper.is-open, .cdp-wrapper.is-open').forEach((openWrap) => {
            let curr = openWrap.parentElement;
            while (curr && curr !== document.body && curr.tagName !== 'MAIN') {
                if (
                    curr.hasAttribute('data-framer-card') ||
                    curr.tagName === 'FORM' ||
                    curr.classList.contains('rounded-2xl') ||
                    curr.classList.contains('rounded-3xl') ||
                    curr.classList.contains('grid')
                ) {
                    curr.classList.add('has-open-dropdown');
                }
                curr = curr.parentElement;
            }
        });
    }

    function repositionActiveFloatingOverlays() {
        document.querySelectorAll('.ss-wrapper.is-open').forEach((w) => {
            const trig = w._ssTrigger || w.querySelector('.ss-trigger');
            const drop = w._ssDropdown;
            if (trig && drop) {
                const r = trig.getBoundingClientRect();
                if (r.width === 0 && r.height === 0) {
                    closeSearchableSelectWrapper(w, true);
                } else {
                    positionFloatingOverlay(trig, drop, { minWidth: 210 });
                }
            }
        });

        document.querySelectorAll('.cdp-wrapper.is-open').forEach((w) => {
            const trig = w._cdpTrigger || w.querySelector('.cdp-trigger');
            const pop = w._cdpPopover;
            if (trig && pop) {
                const r = trig.getBoundingClientRect();
                if (r.width === 0 && r.height === 0) {
                    w._cdpClose?.(true);
                } else {
                    positionFloatingOverlay(trig, pop, { fixedWidth: 288 });
                }
            }
        });
    }

    window.addEventListener('scroll', (e) => {
        if (e.target instanceof Element && (e.target.closest('.ss-dropdown') || e.target.closest('.cdp-popover'))) {
            return;
        }
        repositionActiveFloatingOverlays();
    }, true);
    window.addEventListener('resize', repositionActiveFloatingOverlays);

    function closeSearchableSelectWrapper(wrapper, immediate = false) {
        if (!wrapper || !wrapper.classList.contains('is-open')) return;
        wrapper.classList.remove('is-open');
        const dropdown = wrapper._ssDropdown || wrapper.querySelector('.ss-dropdown');
        const trigger = wrapper._ssTrigger || wrapper.querySelector('.ss-trigger');
        trigger?.setAttribute('aria-expanded', 'false');

        if (!dropdown) {
            syncAncestorDropdownState();
            return;
        }

        if (wrapper._ssCloseTimer) {
            clearTimeout(wrapper._ssCloseTimer);
            wrapper._ssCloseTimer = null;
        }

        if (immediate) {
            dropdown.classList.remove('is-closing');
            dropdown.classList.add('hidden');
            syncAncestorDropdownState();
            return;
        }

        dropdown.classList.add('is-closing');
        wrapper._ssCloseTimer = setTimeout(() => {
            wrapper._ssCloseTimer = null;
            if (!wrapper.classList.contains('is-open')) {
                dropdown.classList.add('hidden');
                dropdown.classList.remove('is-closing');
                syncAncestorDropdownState();
            }
        }, 165);
    }

    function initSearchableSelects(root = document) {
        const selects = root.querySelectorAll('select:not([data-native-select="true"]):not([data-no-custom-select="true"])');

        selects.forEach((select) => {
            if (select.dataset.ssEnhanced === '1') {
                if (typeof select._ssRefresh === 'function') {
                    select._ssRefresh();
                }
                return;
            }
            select.dataset.ssEnhanced = '1';

            const isFullWidth = select.classList.contains('w-full') || select.classList.contains('block');
            const isFlex1 = select.classList.contains('flex-1');
            const isCompact = select.classList.contains('text-xs') || select.classList.contains('py-1.5') || select.classList.contains('py-2') || select.classList.contains('h-9');

            // Hide original select visually while keeping it accessible for forms & tests
            select.classList.add('sr-only');
            select.tabIndex = -1;

            const wrapper = document.createElement('div');
            wrapper.className = [
                'ss-wrapper',
                isFullWidth ? 'ss-full w-full' : '',
                isFlex1 ? 'flex-1' : '',
                isCompact ? 'ss-compact' : '',
            ].filter(Boolean).join(' ');
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'ss-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');

            const dropdown = document.createElement('div');
            dropdown.className = 'ss-dropdown hidden';
            dropdown.innerHTML = `
                <div class="ss-search-box relative">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" class="ss-search-input" placeholder="Ketik untuk mencari pilihan..." autocomplete="off">
                </div>
                <div class="ss-options" role="listbox"></div>
            `;

            wrapper._ssTrigger = trigger;
            wrapper._ssDropdown = dropdown;
            dropdown._ssWrapper = wrapper;

            wrapper.appendChild(trigger);
            wrapper.appendChild(dropdown);

            const searchInput = dropdown.querySelector('.ss-search-input');
            const optionsContainer = dropdown.querySelector('.ss-options');

            function getSelectedText() {
                const opt = select.options[select.selectedIndex];
                return opt ? opt.textContent.trim() : 'Pilih...';
            }

            function renderTrigger() {
                trigger.innerHTML = `
                    <span class="truncate">${getSelectedText()}</span>
                    <i class="bi bi-chevron-down ss-trigger-chevron text-xs"></i>
                `;
                trigger.disabled = select.disabled;
            }

            function renderOptions(filterText = '') {
                const q = filterText.trim().toLowerCase();
                optionsContainer.innerHTML = '';
                let matchCount = 0;

                Array.from(select.options).forEach((opt, idx) => {
                    const text = opt.textContent.trim();
                    if (q && !text.toLowerCase().includes(q)) return;

                    matchCount++;
                    const item = document.createElement('div');
                    const isSelected = select.selectedIndex === idx;
                    item.className = `ss-option ${isSelected ? 'is-selected' : ''}`;
                    item.setAttribute('role', 'option');
                    item.setAttribute('data-value', opt.value);
                    item.innerHTML = `
                        <span class="truncate">${text}</span>
                        ${isSelected ? '<i class="bi bi-check2 text-xs shrink-0"></i>' : ''}
                    `;

                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (select.value !== opt.value || select.selectedIndex !== idx) {
                            select.selectedIndex = idx;
                            select.value = opt.value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        renderTrigger();
                        closeDropdown();
                    });

                    optionsContainer.appendChild(item);
                });

                if (matchCount === 0) {
                    optionsContainer.innerHTML = `
                        <div class="px-3 py-4 text-center text-xs text-slate-400 dark:text-slate-500">
                            Pilihan tidak ditemukan
                        </div>
                    `;
                }
            }

            function openDropdown() {
                if (select.disabled) return;
                document.querySelectorAll('.ss-wrapper.is-open').forEach((other) => {
                    if (other !== wrapper) {
                        closeSearchableSelectWrapper(other, true);
                    }
                });
                document.querySelectorAll('.cdp-wrapper.is-open').forEach((cdp) => {
                    cdp._cdpClose?.(true);
                });

                // Portal dropdown to document.body so it floats in the top layer outside modals & overflow tables
                if (dropdown.parentElement !== document.body) {
                    document.body.appendChild(dropdown);
                }

                if (searchInput) {
                    searchInput.value = '';
                    renderOptions('');
                }

                dropdown.classList.remove('is-closing', 'hidden');
                wrapper.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                positionFloatingOverlay(trigger, dropdown, { minWidth: 210 });
                syncAncestorDropdownState();

                if (searchInput && window.innerWidth >= 640) {
                    setTimeout(() => searchInput.focus(), 15);
                }
            }

            function closeDropdown(immediate = false) {
                closeSearchableSelectWrapper(wrapper, immediate);
            }

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (wrapper.classList.contains('is-open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });

            searchInput?.addEventListener('input', () => {
                renderOptions(searchInput.value);
                positionFloatingOverlay(trigger, dropdown, { minWidth: 210 });
            });

            searchInput?.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeDropdown();
                    trigger.focus();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const firstOpt = optionsContainer.querySelector('.ss-option');
                    if (firstOpt) firstOpt.click();
                }
            });

            select.addEventListener('change', () => {
                renderTrigger();
                renderOptions(searchInput ? searchInput.value : '');
            });

            select._ssRefresh = () => {
                renderTrigger();
                renderOptions(searchInput ? searchInput.value : '');
            };

            renderTrigger();
        });
    }

    window.initSearchableSelects = initSearchableSelects;
    initSearchableSelects();

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.ss-wrapper') && !e.target.closest('.ss-dropdown')) {
            document.querySelectorAll('.ss-wrapper.is-open').forEach((w) => {
                closeSearchableSelectWrapper(w);
            });
        }
        if (!e.target.closest('.cdp-wrapper') && !e.target.closest('.cdp-popover')) {
            document.querySelectorAll('.cdp-wrapper.is-open').forEach((c) => {
                c._cdpClose?.();
            });
        }
    });

    // =========================================================================
    // 5B. CUSTOM INTERACTIVE DATE PICKER (UPGRADES <input type="date">)
    // =========================================================================
    const ID_MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const ID_MONTHS_FULL = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const ID_WEEKDAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    function parseIsoDate(val) {
        if (!val || !/^\d{4}-\d{2}-\d{2}$/.test(val)) return null;
        const [y, m, d] = val.split('-').map(Number);
        const dt = new Date(y, m - 1, d);
        if (Number.isNaN(dt.getTime())) return null;
        return { year: y, month: m - 1, day: d, date: dt };
    }

    function formatIsoDate(year, monthZeroBased, day) {
        const y = String(year).padStart(4, '0');
        const m = String(monthZeroBased + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function formatHumanIdDate(isoStr, placeholder = 'Pilih tanggal...') {
        const parsed = parseIsoDate(isoStr);
        if (!parsed) return placeholder;
        return `${String(parsed.day).padStart(2, '0')} ${ID_MONTHS_SHORT[parsed.month]} ${parsed.year}`;
    }

    function initCustomDatePickers(root = document) {
        const dateInputs = root.querySelectorAll('input[type="date"]:not([data-native-date="true"])');

        dateInputs.forEach((input) => {
            if (input.dataset.cdpEnhanced === '1') {
                input._cdpRefresh?.();
                return;
            }
            input.dataset.cdpEnhanced = '1';

            const isFullWidth = input.classList.contains('w-full') || input.classList.contains('block');
            const isFlex1 = input.classList.contains('flex-1');
            const isCompact = input.classList.contains('text-xs') || input.classList.contains('py-1.5') || input.classList.contains('py-2') || input.classList.contains('h-9');
            const placeholder = input.getAttribute('placeholder') || 'Pilih tanggal...';
            input.classList.add('sr-only');
            input.tabIndex = -1;

            const wrapper = document.createElement('div');
            wrapper.className = [
                'cdp-wrapper',
                isFullWidth ? 'cdp-full w-full' : '',
                isFlex1 ? 'flex-1' : '',
                isCompact ? 'cdp-compact' : '',
            ].filter(Boolean).join(' ');
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'cdp-trigger';
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.setAttribute('aria-expanded', 'false');

            const popover = document.createElement('div');
            popover.className = 'cdp-popover hidden';

            wrapper._cdpTrigger = trigger;
            wrapper._cdpPopover = popover;
            popover._cdpWrapper = wrapper;

            wrapper.appendChild(trigger);
            wrapper.appendChild(popover);

            const now = new Date();
            const initialParsed = parseIsoDate(input.value);
            let viewYear = initialParsed ? initialParsed.year : now.getFullYear();
            let viewMonth = initialParsed ? initialParsed.month : now.getMonth();

            function renderTrigger() {
                const hasVal = Boolean(parseIsoDate(input.value));
                const text = formatHumanIdDate(input.value, placeholder);
                trigger.innerHTML = `
                    <span class="inline-flex items-center gap-2 truncate ${hasVal ? 'text-slate-900 dark:text-white font-bold' : 'text-slate-400 dark:text-slate-500 font-medium'}">
                        <i class="bi bi-calendar3 text-blue-600 dark:text-blue-400 text-xs shrink-0"></i>
                        <span class="truncate">${text}</span>
                    </span>
                    <i class="bi bi-chevron-down ss-trigger-chevron text-xs"></i>
                `;
                trigger.disabled = input.disabled;
            }

            function selectDateValue(isoVal) {
                if (input.value !== isoVal) {
                    input.value = isoVal;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
                const parsed = parseIsoDate(isoVal);
                if (parsed) {
                    viewYear = parsed.year;
                    viewMonth = parsed.month;
                }
                renderTrigger();
                closePopover();
            }

            function renderCalendar() {
                const selectedParsed = parseIsoDate(input.value);
                const todayIso = formatIsoDate(now.getFullYear(), now.getMonth(), now.getDate());

                const firstDayOfMonth = new Date(viewYear, viewMonth, 1);
                const startOffset = (firstDayOfMonth.getDay() + 6) % 7;
                const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
                const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

                const isBirthdate = input.getAttribute('data-datepicker-mode') === 'birthdate';
                const minYear = isBirthdate ? now.getFullYear() - 80 : now.getFullYear() - 10;
                const maxYear = isBirthdate ? now.getFullYear() : now.getFullYear() + 5;
                let yearOptsHtml = '';
                for (let y = maxYear; y >= minYear; y--) {
                    yearOptsHtml += `<option value="${y}" ${y === viewYear ? 'selected' : ''}>${y}</option>`;
                }

                let monthOptsHtml = '';
                ID_MONTHS_FULL.forEach((mName, mIdx) => {
                    monthOptsHtml += `<option value="${mIdx}" ${mIdx === viewMonth ? 'selected' : ''}>${mName}</option>`;
                });

                let daysHtml = '';
                for (let i = startOffset - 1; i >= 0; i--) {
                    const d = daysInPrevMonth - i;
                    const pm = viewMonth === 0 ? 11 : viewMonth - 1;
                    const py = viewMonth === 0 ? viewYear - 1 : viewYear;
                    const iso = formatIsoDate(py, pm, d);
                    daysHtml += `<button type="button" data-cdp-date="${iso}" class="cdp-day is-muted">${d}</button>`;
                }

                for (let d = 1; d <= daysInMonth; d++) {
                    const iso = formatIsoDate(viewYear, viewMonth, d);
                    const isSelected = selectedParsed && input.value === iso;
                    const isToday = iso === todayIso;
                    daysHtml += `
                        <button type="button"
                                data-cdp-date="${iso}"
                                class="cdp-day ${isSelected ? 'is-selected' : ''} ${!isSelected && isToday ? 'is-today' : ''}">
                            ${d}
                        </button>
                    `;
                }

                const totalCells = startOffset + daysInMonth;
                const trailingCount = (7 - (totalCells % 7)) % 7;
                for (let d = 1; d <= trailingCount; d++) {
                    const nm = viewMonth === 11 ? 0 : viewMonth + 1;
                    const ny = viewMonth === 11 ? viewYear + 1 : viewYear;
                    const iso = formatIsoDate(ny, nm, d);
                    daysHtml += `<button type="button" data-cdp-date="${iso}" class="cdp-day is-muted">${d}</button>`;
                }

                popover.innerHTML = `
                    <div class="flex items-center justify-between gap-1.5 pb-2.5 mb-2 border-b border-slate-100 dark:border-slate-800">
                        <button type="button" data-cdp-nav="-1" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <i class="bi bi-chevron-left text-xs"></i>
                        </button>
                        <div class="flex items-center gap-1 flex-1 justify-center">
                            <select data-native-select="true" data-cdp-month class="px-2 py-1 rounded-lg text-xs font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 border-0 focus:ring-2 focus:ring-blue-500/40 cursor-pointer">
                                ${monthOptsHtml}
                            </select>
                            <select data-native-select="true" data-cdp-year class="px-2 py-1 rounded-lg text-xs font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 border-0 focus:ring-2 focus:ring-blue-500/40 cursor-pointer">
                                ${yearOptsHtml}
                            </select>
                        </div>
                        <button type="button" data-cdp-nav="1" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <i class="bi bi-chevron-right text-xs"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-0.5 text-center mb-1">
                        ${ID_WEEKDAYS.map((w) => `<span class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 py-1">${w}</span>`).join('')}
                    </div>

                    <div class="grid grid-cols-7 gap-0.5 place-items-center">
                        ${daysHtml}
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-1.5">
                        <div class="flex items-center gap-1">
                            <button type="button" data-cdp-preset="today" class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/15 hover:bg-blue-100 transition-colors">
                                Hari Ini
                            </button>
                            <button type="button" data-cdp-preset="yesterday" class="px-2 py-1 rounded-lg text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                Kemarin
                            </button>
                        </div>
                        ${!input.required ? `
                            <button type="button" data-cdp-preset="clear" class="px-2 py-1 rounded-lg text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                                Reset
                            </button>
                        ` : ''}
                    </div>
                `;

                popover.querySelectorAll('[data-cdp-nav]').forEach((navBtn) => {
                    navBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const delta = parseInt(navBtn.getAttribute('data-cdp-nav') || '0', 10);
                        viewMonth += delta;
                        if (viewMonth < 0) {
                            viewMonth = 11;
                            viewYear--;
                        } else if (viewMonth > 11) {
                            viewMonth = 0;
                            viewYear++;
                        }
                        renderCalendar();
                    });
                });

                popover.querySelector('[data-cdp-month]')?.addEventListener('change', (e) => {
                    e.stopPropagation();
                    viewMonth = parseInt(e.target.value, 10);
                    renderCalendar();
                });

                popover.querySelector('[data-cdp-year]')?.addEventListener('change', (e) => {
                    e.stopPropagation();
                    viewYear = parseInt(e.target.value, 10);
                    renderCalendar();
                });

                popover.querySelectorAll('[data-cdp-date]').forEach((dayBtn) => {
                    dayBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectDateValue(dayBtn.getAttribute('data-cdp-date') || '');
                    });
                });

                popover.querySelectorAll('[data-cdp-preset]').forEach((presetBtn) => {
                    presetBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const preset = presetBtn.getAttribute('data-cdp-preset');
                        if (preset === 'today') {
                            const t = new Date();
                            selectDateValue(formatIsoDate(t.getFullYear(), t.getMonth(), t.getDate()));
                        } else if (preset === 'yesterday') {
                            const y = new Date();
                            y.setDate(y.getDate() - 1);
                            selectDateValue(formatIsoDate(y.getFullYear(), y.getMonth(), y.getDate()));
                        } else if (preset === 'clear') {
                            selectDateValue('');
                        }
                    });
                });
            }

            function openPopover() {
                if (input.disabled) return;
                document.querySelectorAll('.ss-wrapper.is-open').forEach((w) => closeSearchableSelectWrapper(w, true));
                document.querySelectorAll('.cdp-wrapper.is-open').forEach((other) => {
                    if (other !== wrapper) other._cdpClose?.(true);
                });

                if (popover.parentElement !== document.body) {
                    document.body.appendChild(popover);
                }

                const parsed = parseIsoDate(input.value);
                if (parsed) {
                    viewYear = parsed.year;
                    viewMonth = parsed.month;
                }
                renderCalendar();
                popover.classList.remove('is-closing', 'hidden');
                wrapper.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                positionFloatingOverlay(trigger, popover, { fixedWidth: 288 });
                syncAncestorDropdownState();
            }

            function closePopover(immediate = false) {
                if (!wrapper.classList.contains('is-open')) return;
                wrapper.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');

                if (wrapper._cdpCloseTimer) {
                    clearTimeout(wrapper._cdpCloseTimer);
                    wrapper._cdpCloseTimer = null;
                }

                if (immediate) {
                    popover.classList.remove('is-closing');
                    popover.classList.add('hidden');
                    syncAncestorDropdownState();
                    return;
                }

                popover.classList.add('is-closing');
                wrapper._cdpCloseTimer = setTimeout(() => {
                    wrapper._cdpCloseTimer = null;
                    if (!wrapper.classList.contains('is-open')) {
                        popover.classList.add('hidden');
                        popover.classList.remove('is-closing');
                        syncAncestorDropdownState();
                    }
                }, 165);
            }

            wrapper._cdpClose = closePopover;
            input._cdpRefresh = renderTrigger;

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (wrapper.classList.contains('is-open')) {
                    closePopover();
                } else {
                    openPopover();
                }
            });

            input.addEventListener('change', renderTrigger);
            renderTrigger();
        });
    }

    window.initCustomDatePickers = initCustomDatePickers;
    initCustomDatePickers();

    // =========================================================================
    // 6. NAVBAR DROPDOWNS, COLLAPSIBLES, MOBILE SHEETS & FRAMER MOTION PHYSICS
    // =========================================================================
    const isReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
    const EASE_SHADCN_OUT = 'cubic-bezier(0.16, 1, 0.3, 1)';
    const EASE_SHADCN_IN_OUT = 'cubic-bezier(0.4, 0, 0.2, 1)';
    const EASE_VAUL_DRAWER = 'cubic-bezier(0.32, 0.72, 0, 1)';

    function cancelElementAnimations(el) {
        if (!el || typeof el.getAnimations !== 'function') return;
        try {
            el.getAnimations().forEach((anim) => anim.cancel());
        } catch (e) {}
    }

    function animateSpringElement(el, keyframes, options = {}) {
        if (!el || isReducedMotion || typeof el.animate !== 'function') return null;
        if (options.cancelPrevious !== false) {
            cancelElementAnimations(el);
        }
        const delay = options.delay || 0;
        const fill = options.fill || (delay > 0 ? 'backwards' : 'none');
        try {
            return el.animate(keyframes, {
                duration: options.duration || 260,
                delay,
                easing: options.easing || EASE_SHADCN_OUT,
                fill,
            });
        } catch (e) {
            return null;
        }
    }

    // Smooth Collapsible Panel Animator (Shadcn Accordion/Collapsible Height + Opacity)
    function animateCollapsiblePanel(panelEl, willShow) {
        if (!panelEl) return;
        cancelElementAnimations(panelEl);

        if (isReducedMotion || typeof panelEl.animate !== 'function') {
            panelEl.classList.toggle('hidden', !willShow);
            panelEl.style.overflow = '';
            return;
        }

        const isFloatingMenu =
            window.getComputedStyle(panelEl).position === 'absolute' ||
            window.getComputedStyle(panelEl).position === 'fixed';

        if (willShow) {
            panelEl.classList.remove('hidden');
            if (isFloatingMenu) {
                animateSpringElement(panelEl, [
                    { opacity: 0, transform: 'translate3d(0, -6px, 0) scale(0.96)' },
                    { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                ], { duration: 210, easing: EASE_SHADCN_OUT, fill: 'none' });
                return;
            }

            const targetHeight = panelEl.scrollHeight;
            panelEl.style.overflow = 'hidden';
            const anim = animateSpringElement(panelEl, [
                { opacity: 0, height: '0px', transform: 'translate3d(0, -4px, 0)' },
                { opacity: 1, height: `${Math.max(targetHeight, 1)}px`, transform: 'translate3d(0, 0, 0)' },
            ], { duration: 250, easing: EASE_SHADCN_OUT, fill: 'none' });

            if (anim) {
                anim.onfinish = () => {
                    panelEl.style.overflow = '';
                };
            } else {
                panelEl.style.overflow = '';
            }
        } else {
            if (isFloatingMenu) {
                const anim = animateSpringElement(panelEl, [
                    { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                    { opacity: 0, transform: 'translate3d(0, -4px, 0) scale(0.97)' },
                ], { duration: 165, easing: EASE_SHADCN_IN_OUT, fill: 'forwards' });

                if (anim) {
                    anim.onfinish = () => {
                        panelEl.classList.add('hidden');
                        anim.cancel();
                    };
                } else {
                    panelEl.classList.add('hidden');
                }
                return;
            }

            const currentHeight = panelEl.offsetHeight || panelEl.scrollHeight;
            panelEl.style.overflow = 'hidden';
            const anim = animateSpringElement(panelEl, [
                { opacity: 1, height: `${currentHeight}px`, transform: 'translate3d(0, 0, 0)' },
                { opacity: 0, height: '0px', transform: 'translate3d(0, -4px, 0)' },
            ], { duration: 200, easing: EASE_SHADCN_IN_OUT, fill: 'forwards' });

            if (anim) {
                anim.onfinish = () => {
                    panelEl.classList.add('hidden');
                    panelEl.style.overflow = '';
                    anim.cancel();
                };
            } else {
                panelEl.classList.add('hidden');
                panelEl.style.overflow = '';
            }
        }
    }

    window.animateCollapsiblePanel = animateCollapsiblePanel;

    function positionNotificationBellPopover() {
        if (!notifDropdown || !notifBellBtn) return;
        const isMobile = window.innerWidth < 640;
        const bellRect = notifBellBtn.getBoundingClientRect();
        const arrowEl = document.getElementById('notificationBellPointerArrow');

        if (isMobile) {
            const topPx = Math.max(54, Math.round((bellRect.bottom || 52) + 8));
            notifDropdown.style.position = 'fixed';
            notifDropdown.style.top = `${topPx}px`;
            notifDropdown.style.right = '12px';
            notifDropdown.style.left = '12px';
            notifDropdown.style.bottom = 'auto';
            notifDropdown.style.transformOrigin = 'top right';

            if (arrowEl && bellRect.width > 0) {
                const rightOffset = Math.max(16, Math.round(window.innerWidth - (bellRect.left + bellRect.width / 2) - 12 - 7));
                arrowEl.style.right = `${rightOffset}px`;
            }
        } else {
            notifDropdown.style.position = '';
            notifDropdown.style.top = '';
            notifDropdown.style.right = '';
            notifDropdown.style.left = '';
            notifDropdown.style.bottom = '';
            notifDropdown.style.transformOrigin = 'top right';
            if (arrowEl) {
                arrowEl.style.right = '';
            }
        }
    }

    function animatePopoverOrSheetOpen(panelEl, backdropEl = null) {
        if (!panelEl) return;
        cancelElementAnimations(panelEl);
        panelEl.style.transform = '';
        panelEl.classList.remove('hidden');
        const isMobile = window.innerWidth < 640;
        const isNotifPopover = panelEl.id === 'notificationDropdownPanel';

        if (isNotifPopover) {
            positionNotificationBellPopover();
        }

        if (backdropEl && isMobile) {
            cancelElementAnimations(backdropEl);
            backdropEl.classList.remove('hidden', 'opacity-0');
            backdropEl.style.opacity = '1';
            animateSpringElement(backdropEl, [
                { opacity: 0 },
                { opacity: 1 },
            ], { duration: 220, easing: EASE_SHADCN_OUT, fill: 'none' });
        }

        if (isReducedMotion || typeof panelEl.animate !== 'function') return;

        if (isMobile && !isNotifPopover) {
            animateSpringElement(panelEl, [
                { opacity: 0, transform: 'translate3d(0, 40px, 0) scale(0.97)' },
                { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
            ], { duration: 310, easing: EASE_VAUL_DRAWER, fill: 'none' });
        } else {
            animateSpringElement(panelEl, [
                { opacity: 0, transform: 'translate3d(0, -8px, 0) scale(0.96)' },
                { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
            ], { duration: 215, easing: EASE_SHADCN_OUT, fill: 'none' });
        }
    }

    function animatePopoverOrSheetClose(panelEl, backdropEl = null) {
        if (!panelEl || panelEl.classList.contains('hidden')) return;
        const isMobile = window.innerWidth < 640;
        const isNotifPopover = panelEl.id === 'notificationDropdownPanel';

        if (backdropEl && !backdropEl.classList.contains('hidden')) {
            const bAnim = animateSpringElement(backdropEl, [
                { opacity: 1 },
                { opacity: 0 },
            ], { duration: 185, easing: EASE_SHADCN_IN_OUT, fill: 'forwards' });
            if (bAnim) {
                bAnim.onfinish = () => {
                    backdropEl.classList.add('hidden');
                    backdropEl.style.opacity = '';
                    bAnim.cancel();
                };
            } else {
                backdropEl.classList.add('hidden');
                backdropEl.style.opacity = '';
            }
        }

        if (isReducedMotion || typeof panelEl.animate !== 'function') {
            panelEl.classList.add('hidden');
            return;
        }

        const keyframes = (isMobile && !isNotifPopover)
            ? [
                { opacity: 1, transform: panelEl.style.transform || 'translate3d(0, 0, 0) scale(1)' },
                { opacity: 0, transform: 'translate3d(0, 32px, 0) scale(0.97)' },
            ]
            : [
                { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                { opacity: 0, transform: 'translate3d(0, -6px, 0) scale(0.96)' },
            ];

        const anim = animateSpringElement(panelEl, keyframes, {
            duration: (isMobile && !isNotifPopover) ? 220 : 165,
            easing: (isMobile && !isNotifPopover) ? EASE_VAUL_DRAWER : EASE_SHADCN_IN_OUT,
            fill: 'forwards',
        });
        if (anim) {
            anim.onfinish = () => {
                panelEl.classList.add('hidden');
                panelEl.style.transform = '';
                anim.cancel();
            };
        } else {
            panelEl.classList.add('hidden');
        }
    }

    // Profile & Notification Bell Dropdowns / Top-Anchored Mobile Notification Popover
    const userMenuBtn = document.getElementById('userMenuButton');
    const userMenuDropdown = document.getElementById('userMenuDropdown');
    const notifDropdownRoot = document.getElementById('notificationDropdownRoot');
    const notifBellBtn = document.getElementById('notificationBellBtn');
    const notifDropdown = document.getElementById('notificationDropdownPanel');
    const notifBackdrop = document.getElementById('notificationSheetBackdrop');
    const closeNotifSheetBtn = document.getElementById('closeNotificationSheetBtn');

    // Escape <header>'s backdrop-filter containing block on mobile (< 640px) while anchoring right below #notificationBellBtn
    function syncNotificationPanelMount() {
        if (!notifDropdown || !notifDropdownRoot) return;
        const isMobile = window.innerWidth < 640;
        if (isMobile) {
            if (notifBackdrop && notifBackdrop.parentElement !== document.body) {
                document.body.appendChild(notifBackdrop);
            }
            if (notifDropdown.parentElement !== document.body) {
                document.body.appendChild(notifDropdown);
            }
            positionNotificationBellPopover();
        } else {
            if (notifBackdrop && notifBackdrop.parentElement !== notifDropdownRoot) {
                notifBackdrop.classList.add('hidden');
                notifDropdownRoot.appendChild(notifBackdrop);
            }
            if (notifDropdown.parentElement !== notifDropdownRoot) {
                notifDropdownRoot.appendChild(notifDropdown);
            }
            positionNotificationBellPopover();
        }
    }

    window.addEventListener('resize', () => {
        if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
            syncNotificationPanelMount();
        }
    });

    function closeNotificationCenter() {
        notifBellBtn?.setAttribute('aria-expanded', 'false');
        animatePopoverOrSheetClose(notifDropdown, notifBackdrop);
    }

    function openNotificationCenter() {
        if (userMenuDropdown && !userMenuDropdown.classList.contains('hidden')) {
            userMenuBtn?.setAttribute('aria-expanded', 'false');
            animatePopoverOrSheetClose(userMenuDropdown);
        }
        syncNotificationPanelMount();
        notifBellBtn?.setAttribute('aria-expanded', 'true');
        animatePopoverOrSheetOpen(notifDropdown, notifBackdrop);
    }

    userMenuBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
            closeNotificationCenter();
        }
        const isClosed = userMenuDropdown?.classList.contains('hidden');
        if (isClosed && userMenuDropdown) {
            userMenuBtn.setAttribute('aria-expanded', 'true');
            animatePopoverOrSheetOpen(userMenuDropdown);
        } else if (userMenuDropdown) {
            userMenuBtn.setAttribute('aria-expanded', 'false');
            animatePopoverOrSheetClose(userMenuDropdown);
        }
    });

    notifBellBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isClosed = notifDropdown?.classList.contains('hidden');
        if (isClosed) {
            openNotificationCenter();
        } else {
            closeNotificationCenter();
        }
    });

    closeNotifSheetBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        closeNotificationCenter();
    });

    notifBackdrop?.addEventListener('click', (e) => {
        e.stopPropagation();
        closeNotificationCenter();
    });

    document.addEventListener('click', (e) => {
        if (userMenuDropdown && !userMenuDropdown.classList.contains('hidden') && !userMenuBtn?.contains(e.target) && !userMenuDropdown.contains(e.target)) {
            userMenuBtn?.setAttribute('aria-expanded', 'false');
            animatePopoverOrSheetClose(userMenuDropdown);
        }
        if (notifDropdown && !notifDropdown.classList.contains('hidden') && !notifBellBtn?.contains(e.target) && !notifDropdown.contains(e.target)) {
            closeNotificationCenter();
        }

        // Global [data-dropdown-toggle] handler for Card Action Menus
        const dropdownBtn = e.target instanceof Element ? e.target.closest('[data-dropdown-toggle]') : null;
        if (dropdownBtn) {
            e.preventDefault();
            e.stopPropagation();
            const targetId = dropdownBtn.getAttribute('data-dropdown-toggle');
            const targetMenu = targetId ? document.getElementById(targetId) : null;
            document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
                if (menu !== targetMenu && !menu.classList.contains('hidden')) {
                    animatePopoverOrSheetClose(menu);
                }
            });
            if (targetMenu) {
                const isHidden = targetMenu.classList.contains('hidden');
                if (isHidden) {
                    animatePopoverOrSheetOpen(targetMenu);
                } else {
                    animatePopoverOrSheetClose(targetMenu);
                }
            }
            return;
        }

        if (!(e.target instanceof Element) || !e.target.closest('[data-dropdown-menu]')) {
            document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
                if (!menu.classList.contains('hidden')) {
                    animatePopoverOrSheetClose(menu);
                }
            });
        }
    });

    // Global Modal & Mobile Bottom Sheet Open/Close System with Shadcn + Vaul Physics
    function openModalById(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        if (modal.parentElement !== document.body && !modal.closest('form')) {
            document.body.appendChild(modal);
        }
        cancelElementAnimations(modal);
        delete modal.dataset.isClosing;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden', 'has-open-modal');

        animateSpringElement(modal, [
            { opacity: 0 },
            { opacity: 1 },
        ], { duration: 220, easing: EASE_SHADCN_OUT, fill: 'none' });

        const dialog = modal.querySelector('.sheet-dialog') || modal.firstElementChild;
        if (dialog) {
            cancelElementAnimations(dialog);
            dialog.style.transform = '';
        }

        if (dialog && !isReducedMotion && typeof dialog.animate === 'function') {
            const isMobileSheet = window.innerWidth < 768 && modal.classList.contains('responsive-bottom-sheet');
            if (isMobileSheet) {
                animateSpringElement(dialog, [
                    { opacity: 0.85, transform: 'translate3d(0, 100%, 0)' },
                    { opacity: 1, transform: 'translate3d(0, 0, 0)' },
                ], { duration: 330, easing: EASE_VAUL_DRAWER, fill: 'none' });
            } else {
                animateSpringElement(dialog, [
                    { opacity: 0, transform: 'translate3d(0, 12px, 0) scale(0.96)' },
                    { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                ], { duration: 260, easing: EASE_SHADCN_OUT, fill: 'none' });
            }

            if (modalId === 'mobileAppMenuSheet') {
                const tiles = dialog.querySelectorAll('.grid a, .grid button');
                tiles.forEach((tile, idx) => {
                    animateSpringElement(tile, [
                        { opacity: 0, transform: 'translate3d(0, 8px, 0) scale(0.95)' },
                        { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                    ], {
                        duration: 260,
                        delay: Math.min(idx * 16, 120),
                        easing: EASE_SHADCN_OUT,
                        fill: 'backwards',
                    });
                });
            }
        }
    }

    function closeModalEl(modal, immediate = false) {
        if (!modal || modal.classList.contains('hidden')) return;

        const finishClose = (modalAnim = null, dialogAnim = null) => {
            delete modal.dataset.isClosing;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            const dialog = modal.querySelector('.sheet-dialog') || modal.firstElementChild;
            if (dialog) dialog.style.transform = '';
            if (modalAnim) {
                try { modalAnim.cancel(); } catch (e) {}
            }
            if (dialogAnim) {
                try { dialogAnim.cancel(); } catch (e) {}
            }
            if (!document.querySelector('.modal-container.flex')) {
                document.body.classList.remove('overflow-hidden', 'has-open-modal');
            }
        };

        if (immediate || isReducedMotion) {
            finishClose();
            return;
        }

        if (modal.dataset.isClosing === '1') return;
        modal.dataset.isClosing = '1';

        const dialog = modal.querySelector('.sheet-dialog') || modal.firstElementChild;
        const isMobileSheet = window.innerWidth < 768 && modal.classList.contains('responsive-bottom-sheet');

        const modalAnim = animateSpringElement(modal, [
            { opacity: 1 },
            { opacity: 0 },
        ], { duration: isMobileSheet ? 225 : 190, easing: EASE_SHADCN_IN_OUT, fill: 'forwards' });

        if (dialog && typeof dialog.animate === 'function') {
            const keyframes = isMobileSheet
                ? [
                    { opacity: 1, transform: dialog.style.transform || 'translate3d(0, 0, 0)' },
                    { opacity: 0.7, transform: 'translate3d(0, 100%, 0)' },
                ]
                : [
                    { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                    { opacity: 0, transform: 'translate3d(0, 8px, 0) scale(0.96)' },
                ];
            const dialogAnim = animateSpringElement(dialog, keyframes, {
                duration: isMobileSheet ? 235 : 190,
                easing: isMobileSheet ? EASE_VAUL_DRAWER : EASE_SHADCN_IN_OUT,
                fill: 'forwards',
            });
            if (dialogAnim) {
                dialogAnim.onfinish = () => finishClose(modalAnim, dialogAnim);
                return;
            }
        }

        setTimeout(() => finishClose(modalAnim, null), 195);
    }

    window.openModalById = openModalById;
    window.closeModalEl = closeModalEl;

    // Interactive Up & Down Drag for Mobile Bottom Sheets (.sheet-grab-handle & #mobileBottomMenuBtn)
    function initBottomSheetDragHandles() {
        document.querySelectorAll('.sheet-grab-handle, [data-sheet-drag-handle="true"]').forEach((handle) => {
            if (handle.dataset.dragBound === '1') return;
            handle.dataset.dragBound = '1';

            let startY = 0;
            let currentDy = 0;
            let dragging = false;

            const sheetTarget = handle.closest('.sheet-dialog') || handle.closest('#notificationDropdownPanel');
            if (!sheetTarget) return;
            const parentModal = sheetTarget.closest('.modal-container');

            const onStart = (clientY) => {
                startY = clientY;
                currentDy = 0;
                dragging = true;
                cancelElementAnimations(sheetTarget);
                sheetTarget.style.transition = 'none';
            };

            const onMove = (clientY) => {
                if (!dragging) return;
                const rawDy = clientY - startY;
                // When dragging upward (negative rawDy), expand maxHeight/paddingBottom while keeping
                // translate3d(0, 0, 0) anchored flush to the bottom edge so the sheet never floats or cuts off!
                if (rawDy < 0) {
                    currentDy = Math.max(-140, rawDy * 0.65);
                    const extraHeightPx = Math.min(120, Math.round(Math.abs(rawDy) * 0.55));
                    sheetTarget.style.transform = 'translate3d(0, 0, 0)';
                    sheetTarget.style.maxHeight = `calc(88dvh + ${extraHeightPx}px)`;
                } else {
                    currentDy = rawDy;
                    sheetTarget.style.transform = `translate3d(0, ${Math.round(currentDy)}px, 0)`;
                }
            };

            const onEnd = () => {
                if (!dragging) return;
                dragging = false;

                const snapState = parentModal?.getAttribute('data-snap-state') || 'compact';

                if (currentDy < -42 && parentModal) {
                    // Dragged upward -> expand sheet to full height snap point flush with bottom
                    parentModal.setAttribute('data-snap-state', 'expanded');
                    sheetTarget.style.maxHeight = '92dvh';
                    sheetTarget.style.transition = `transform 280ms ${EASE_VAUL_DRAWER}, max-height 280ms ${EASE_VAUL_DRAWER}`;
                    sheetTarget.style.transform = 'translate3d(0, 0, 0)';
                    setTimeout(() => {
                        sheetTarget.style.transition = '';
                        sheetTarget.style.transform = '';
                    }, 285);
                    return;
                }

                if (currentDy > 42 && currentDy <= 115 && snapState === 'expanded' && parentModal) {
                    // Dragged downward slightly from expanded -> snap back to compact
                    parentModal.setAttribute('data-snap-state', 'compact');
                    sheetTarget.style.maxHeight = '';
                    sheetTarget.style.transition = `transform 280ms ${EASE_VAUL_DRAWER}, max-height 280ms ${EASE_VAUL_DRAWER}`;
                    sheetTarget.style.transform = 'translate3d(0, 0, 0)';
                    setTimeout(() => {
                        sheetTarget.style.transition = '';
                        sheetTarget.style.transform = '';
                    }, 285);
                    return;
                }

                if (currentDy > 68) {
                    sheetTarget.style.transition = '';
                    if (parentModal) {
                        parentModal.setAttribute('data-snap-state', 'compact');
                        sheetTarget.style.maxHeight = '';
                        closeModalEl(parentModal);
                    } else if (sheetTarget.id === 'notificationDropdownPanel') {
                        closeNotificationCenter();
                    }
                } else {
                    sheetTarget.style.transition = `transform 260ms ${EASE_SHADCN_OUT}`;
                    sheetTarget.style.transform = 'translate3d(0, 0, 0)';
                    setTimeout(() => {
                        sheetTarget.style.transition = '';
                        sheetTarget.style.transform = '';
                    }, 265);
                }
            };

            handle.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) onStart(e.touches[0].clientY);
            }, { passive: true });

            handle.addEventListener('touchmove', (e) => {
                if (dragging && e.touches.length === 1) onMove(e.touches[0].clientY);
            }, { passive: true });

            handle.addEventListener('touchend', onEnd);
            handle.addEventListener('touchcancel', onEnd);
            handle.addEventListener('pointerdown', (e) => {
                if (e.pointerType === 'mouse' && e.button === 0) {
                    onStart(e.clientY);
                    const moveListener = (ev) => onMove(ev.clientY);
                    const upListener = () => {
                        onEnd();
                        window.removeEventListener('pointermove', moveListener);
                        window.removeEventListener('pointerup', upListener);
                    };
                    window.addEventListener('pointermove', moveListener);
                    window.addEventListener('pointerup', upListener);
                }
            });
        });

        // Allow dragging #mobileBottomMenuBtn upward to pull #mobileAppMenuSheet open interactively
        const openerBtn = document.getElementById('mobileBottomMenuBtn');
        const menuSheetModal = document.getElementById('mobileAppMenuSheet');
        const menuSheetDialog = document.getElementById('mobileAppMenuDialog');
        if (openerBtn && menuSheetModal && menuSheetDialog && openerBtn.dataset.openerDragBound !== '1') {
            openerBtn.dataset.openerDragBound = '1';
            let startY = 0;
            let pulledUpPx = 0;
            let isPullingMenu = false;

            openerBtn.addEventListener('touchstart', (e) => {
                if (e.touches.length !== 1) return;
                startY = e.touches[0].clientY;
                pulledUpPx = 0;
                isPullingMenu = false;
            }, { passive: true });

            openerBtn.addEventListener('touchmove', (e) => {
                if (e.touches.length !== 1) return;
                const dy = e.touches[0].clientY - startY;
                if (dy < -10) {
                    if (!isPullingMenu) {
                        isPullingMenu = true;
                        menuSheetModal.classList.remove('hidden');
                        menuSheetModal.classList.add('flex');
                        menuSheetDialog.style.transition = 'none';
                    }
                    pulledUpPx = Math.min(360, Math.abs(dy));
                    const remainPercent = Math.max(0, 100 - (pulledUpPx / 3.2));
                    menuSheetDialog.style.transform = `translate3d(0, ${remainPercent}%, 0)`;
                }
            }, { passive: true });

            openerBtn.addEventListener('touchend', () => {
                if (!isPullingMenu) return;
                isPullingMenu = false;
                menuSheetDialog.style.transition = '';
                if (pulledUpPx >= 36) {
                    openModalById('mobileAppMenuSheet');
                } else {
                    closeModalEl(menuSheetModal, true);
                }
            });
        }
    }

    initBottomSheetDragHandles();

    // Desktop Collapsible Compact Icon-Only Sidebar Toggle (#desktopSidebarCollapseBtn)
    function initDesktopSidebarCollapse() {
        const sidebarEl = document.getElementById('sidebarOffcanvas');
        const collapseBtn = document.getElementById('desktopSidebarCollapseBtn');
        if (!sidebarEl || !collapseBtn) return;

        const applyCollapsedState = (collapsed) => {
            if (collapsed) {
                htmlEl.setAttribute('data-sidebar-collapsed', 'true');
                sidebarEl.classList.add('is-collapsed');
            } else {
                htmlEl.removeAttribute('data-sidebar-collapsed');
                sidebarEl.classList.remove('is-collapsed');
            }
            try {
                localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0');
            } catch (e) {}
        };

        try {
            if (localStorage.getItem('sidebar_collapsed') === '1') {
                applyCollapsedState(true);
            }
        } catch (e) {}

        collapseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isCurrentlyCollapsed = htmlEl.getAttribute('data-sidebar-collapsed') === 'true' || sidebarEl.classList.contains('is-collapsed');
            applyCollapsedState(!isCurrentlyCollapsed);
        });
    }

    initDesktopSidebarCollapse();

    document.addEventListener('click', (event) => {
        const openTrigger = event.target.closest('[data-modal-target]');
        if (openTrigger) {
            event.preventDefault();
            if (openTrigger.hasAttribute('data-modal-close')) {
                closeModalEl(openTrigger.closest('.modal-container'), true);
            }
            openModalById(openTrigger.getAttribute('data-modal-target'));
            return;
        }

        const closeTrigger = event.target.closest('[data-modal-close]');
        if (closeTrigger) {
            event.preventDefault();
            closeModalEl(closeTrigger.closest('.modal-container'));
            return;
        }

        if (event.target.classList.contains('modal-container')) {
            closeModalEl(event.target);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-container.flex').forEach((m) => closeModalEl(m));
            document.querySelectorAll('[data-dropdown-menu]').forEach((m) => animatePopoverOrSheetClose(m));
            if (userMenuDropdown && !userMenuDropdown.classList.contains('hidden')) {
                animatePopoverOrSheetClose(userMenuDropdown);
            }
            if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
                closeNotificationCenter();
            }
        }
    });

    // =========================================================================
    // 6B. DYNAMIC AJAX FRAGMENT ENTRANCE & INSTANT PAGE PREFETCH ENGINE
    // =========================================================================
    function runFramerMotionEntrance(root = document) {
        if (isReducedMotion) return;
        // Full-document navigation is already smoothly animated via CSS @view-transition
        // on #appMainContent. Re-animating the entire document on DOMContentLoaded caused
        // painted cards to snap backwards and blink. Only run on dynamic AJAX fragments.
        if (root === document) return;

        const framerCards = Array.from(root.querySelectorAll('[data-framer-card]')).slice(0, 12);
        framerCards.forEach((card, idx) => {
            animateSpringElement(card, [
                { opacity: 0, transform: 'translate3d(0, 6px, 0)' },
                { opacity: 1, transform: 'translate3d(0, 0, 0)' },
            ], {
                duration: 210,
                delay: Math.min(idx * 14, 70),
                easing: EASE_SHADCN_OUT,
                fill: 'backwards',
            });
        });

        const staggerContainers = Array.from(root.querySelectorAll('[data-framer-stagger]'));
        staggerContainers.forEach((container) => {
            const children = Array.from(container.children).filter((el) => el.offsetParent !== null || el.tagName === 'DIV' || el.tagName === 'A');
            children.slice(0, 16).forEach((child, idx) => {
                animateSpringElement(child, [
                    { opacity: 0, transform: 'translate3d(0, 5px, 0)' },
                    { opacity: 1, transform: 'translate3d(0, 0, 0)' },
                ], {
                    duration: 200,
                    delay: Math.min(idx * 12, 60),
                    easing: EASE_SHADCN_OUT,
                    fill: 'backwards',
                });
            });
        });
    }

    window.runFramerMotionEntrance = runFramerMotionEntrance;

    // Instant Same-Origin Link Prefetching & Top Progress Bar for Responsive Navigation
    function initFastPageNavigation() {
        let progressBar = document.getElementById('topNavProgressBar');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'topNavProgressBar';
            progressBar.className = 'top-nav-progress-bar';
            document.body.appendChild(progressBar);
        }

        const prefetchedUrls = new Set();

        function shouldPrefetchUrl(anchor) {
            if (!anchor || !anchor.href) return false;
            if (anchor.target && anchor.target !== '_self') return false;
            if (anchor.hasAttribute('download') || anchor.hasAttribute('data-no-prefetch')) return false;
            try {
                const u = new URL(anchor.href, window.location.origin);
                if (u.origin !== window.location.origin) return false;
                if (u.href === window.location.href || u.pathname.includes('logout') || u.pathname.includes('export')) return false;
                return true;
            } catch (e) {
                return false;
            }
        }

        function prefetchAnchor(anchor) {
            if (!shouldPrefetchUrl(anchor)) return;
            const href = anchor.href;
            if (prefetchedUrls.has(href)) return;
            prefetchedUrls.add(href);

            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = href;
            link.as = 'document';
            document.head.appendChild(link);
        }

        document.addEventListener('mouseover', (e) => {
            const a = e.target instanceof Element ? e.target.closest('a[href]') : null;
            if (a) prefetchAnchor(a);
        }, { passive: true });

        document.addEventListener('touchstart', (e) => {
            const a = e.target instanceof Element ? e.target.closest('a[href]') : null;
            if (a) prefetchAnchor(a);
        }, { passive: true });

        document.addEventListener('click', (e) => {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            const a = e.target instanceof Element ? e.target.closest('a[href]') : null;
            if (!a || !shouldPrefetchUrl(a)) return;
            if (a.hasAttribute('data-modal-target') || a.hasAttribute('data-share-code') || a.hasAttribute('data-barcode-modal') || a.hasAttribute('data-copy')) {
                return;
            }
            progressBar.classList.remove('is-finishing');
            progressBar.classList.add('is-active');

            // Smooth uniform exit transition across all pages and mobile menu navigation
            const mobileMenuSheet = document.getElementById('mobileAppMenuSheet');
            if (mobileMenuSheet && !mobileMenuSheet.classList.contains('hidden')) {
                closeModalEl(mobileMenuSheet, false);
            }
            document.documentElement.classList.add('page-is-exiting');
        });

        window.addEventListener('pageshow', () => {
            document.documentElement.classList.remove('page-is-exiting');
            progressBar.classList.remove('is-active');
            progressBar.classList.add('is-finishing');
            setTimeout(() => progressBar.classList.remove('is-finishing'), 200);
        });
    }

    initFastPageNavigation();

    // =========================================================================
    // 6C. MOBILE SWIPEABLE NOTIFICATIONS ([data-swipe-notification])
    // =========================================================================
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function updateUnreadBadgeCount(unreadCount) {
        const count = Math.max(0, Number(unreadCount || 0));
        const unreadBadge = document.getElementById('navbarUnreadBadge');
        if (unreadBadge) {
            unreadBadge.textContent = count > 9 ? '9+' : String(count);
            unreadBadge.classList.toggle('hidden', count <= 0);
        }
    }

    function initNotificationSwipe(root = document) {
        root.querySelectorAll('[data-swipe-notification]').forEach((item) => {
            if (item.dataset.swipeBound === '1') return;
            item.dataset.swipeBound = '1';

            const contentEl = item.querySelector('.swipe-item-content') || item.querySelector('.notif-swipe-card');
            const bgRead = item.querySelector('.swipe-bg-read') || item.querySelector('.notif-swipe-bg-left');
            const bgDelete = item.querySelector('.swipe-bg-delete') || item.querySelector('.notif-swipe-bg-right');
            if (!contentEl) return;

            let startX = 0;
            let startY = 0;
            let dx = 0;
            let swiping = false;
            let lockedAxis = null;

            contentEl.addEventListener('click', (e) => {
                if (Math.abs(dx) > 14) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            item.addEventListener('touchstart', (e) => {
                if (e.touches.length !== 1) return;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                dx = 0;
                swiping = true;
                lockedAxis = null;
                contentEl.style.transition = 'none';
            }, { passive: true });

            item.addEventListener('touchmove', (e) => {
                if (!swiping || e.touches.length !== 1) return;
                const curX = e.touches[0].clientX;
                const curY = e.touches[0].clientY;
                const diffX = curX - startX;
                const diffY = curY - startY;

                if (!lockedAxis) {
                    if (Math.abs(diffX) > 8 || Math.abs(diffY) > 8) {
                        lockedAxis = Math.abs(diffX) > Math.abs(diffY) ? 'x' : 'y';
                    }
                }

                if (lockedAxis !== 'x') return;

                dx = Math.max(-130, Math.min(130, diffX));
                contentEl.style.transform = `translate3d(${dx}px, 0, 0)`;

                if (bgRead) bgRead.style.opacity = dx > 16 ? String(Math.min(1, dx / 72)) : '0';
                if (bgDelete) bgDelete.style.opacity = dx < -16 ? String(Math.min(1, Math.abs(dx) / 72)) : '0';
            }, { passive: true });

            const resetPosition = () => {
                contentEl.style.transition = 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)';
                contentEl.style.transform = 'translate3d(0, 0, 0)';
                if (bgRead) bgRead.style.opacity = '0';
                if (bgDelete) bgDelete.style.opacity = '0';
                setTimeout(() => {
                    dx = 0;
                }, 60);
            };

            const finishSwipe = async () => {
                if (!swiping) return;
                swiping = false;

                const readUrl = item.getAttribute('data-read-url') || item.getAttribute('data-mark-read-url');
                const deleteUrl = item.getAttribute('data-delete-url');

                if (dx > 68 && readUrl) {
                    resetPosition();
                    try {
                        const res = await fetch(readUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        if (res.ok) {
                            const data = await res.json();
                            item.setAttribute('data-unread', '0');
                            item.setAttribute('data-is-unread', '0');
                            item.querySelectorAll('.notif-new-badge, [data-notif-unread-pill], [data-notif-red-dot]').forEach((b) => b.remove());
                            contentEl.classList.remove('bg-blue-50/40', 'dark:bg-blue-950/20', 'bg-blue-50/25', 'dark:bg-blue-950/15', 'bg-blue-50/35', 'dark:bg-blue-950/25');
                            if (typeof data.unread_count === 'number') {
                                updateUnreadBadgeCount(data.unread_count);
                            }
                            window.toast?.success('Notifikasi ditandai sudah dibaca');
                        }
                    } catch (err) {}
                    return;
                }

                if (dx < -68 && deleteUrl) {
                    contentEl.style.transition = 'transform 200ms ease, opacity 200ms ease';
                    contentEl.style.transform = 'translate3d(-100%, 0, 0)';
                    contentEl.style.opacity = '0';

                    try {
                        const res = await fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (typeof data.unread_count === 'number') {
                                updateUnreadBadgeCount(data.unread_count);
                            }
                        }
                    } catch (err) {}

                    setTimeout(() => {
                        item.remove();
                        window.toast?.info('Notifikasi dihapus');
                    }, 190);
                    return;
                }

                resetPosition();
            };

            item.addEventListener('touchend', finishSwipe);
            item.addEventListener('touchcancel', resetPosition);
        });
    }

    window.initNotificationSwipe = initNotificationSwipe;
    initNotificationSwipe(document);

    // =========================================================================
    // 6D. SMART INDONESIAN & INTERNATIONAL PHONE DETECTOR + FORMATTER
    // =========================================================================
    const SMART_COUNTRIES = [
        { dial: '62', iso: 'ID', name: 'Indonesia', min: 8, max: 13 },
        { dial: '60', iso: 'MY', name: 'Malaysia', min: 8, max: 11 },
        { dial: '65', iso: 'SG', name: 'Singapura', min: 8, max: 9 },
        { dial: '1', iso: 'US', name: 'Amerika Serikat / Kanada', min: 10, max: 10 },
        { dial: '44', iso: 'GB', name: 'Inggris (UK)', min: 9, max: 10 },
        { dial: '61', iso: 'AU', name: 'Australia', min: 9, max: 9 },
        { dial: '81', iso: 'JP', name: 'Jepang', min: 9, max: 10 },
        { dial: '82', iso: 'KR', name: 'Korea Selatan', min: 9, max: 10 },
        { dial: '86', iso: 'CN', name: 'Tiongkok', min: 11, max: 11 },
        { dial: '91', iso: 'IN', name: 'India', min: 10, max: 10 },
        { dial: '966', iso: 'SA', name: 'Arab Saudi', min: 9, max: 9 },
        { dial: '971', iso: 'AE', name: 'Uni Emirat Arab', min: 9, max: 9 },
        { dial: '49', iso: 'DE', name: 'Jerman', min: 9, max: 11 },
        { dial: '31', iso: 'NL', name: 'Belanda', min: 9, max: 9 },
        { dial: '33', iso: 'FR', name: 'Prancis', min: 9, max: 9 },
        { dial: '63', iso: 'PH', name: 'Filipina', min: 10, max: 10 },
        { dial: '66', iso: 'TH', name: 'Thailand', min: 9, max: 9 },
        { dial: '84', iso: 'VN', name: 'Vietnam', min: 9, max: 10 },
    ];

    function detectIndonesianProvider(nsn) {
        if (/^(811|812|813|821|822|823|852|853)/.test(nsn)) return 'Telkomsel';
        if (/^(851)/.test(nsn)) return 'By.U / Telkomsel';
        if (/^(814|815|816|855|856|857|858)/.test(nsn)) return 'Indosat Ooredoo';
        if (/^(817|818|819|859|877|878)/.test(nsn)) return 'XL Axiata';
        if (/^(831|832|833|838)/.test(nsn)) return 'AXIS';
        if (/^(895|896|897|898|899)/.test(nsn)) return 'Tri (3)';
        if (/^(881|882|883|884|885|886|887|888|889)/.test(nsn)) return 'Smartfren';
        if (/^2\d+/.test(nsn)) return 'PSTN / Kantor';
        return 'Seluler Indonesia';
    }

    function formatNationalNumber(dial, nsn) {
        if (!nsn) return '';
        if (dial === '62') {
            if (nsn.length <= 3) return nsn;
            if (nsn.length <= 7) return `${nsn.slice(0, 3)}-${nsn.slice(3)}`;
            return `${nsn.slice(0, 3)}-${nsn.slice(3, 7)}-${nsn.slice(7, 13)}`;
        }
        if (dial === '1' && nsn.length >= 6) {
            return `(${nsn.slice(0, 3)}) ${nsn.slice(3, 6)}-${nsn.slice(6, 10)}`;
        }
        if (nsn.length <= 4) return nsn;
        if (nsn.length <= 8) return `${nsn.slice(0, 4)}-${nsn.slice(4)}`;
        return `${nsn.slice(0, 4)}-${nsn.slice(4, 8)}-${nsn.slice(8, 13)}`;
    }

    function parseSmartPhone(raw, fallbackDial = '62') {
        const cleanFallback = String(fallbackDial || '62').replace(/^\+/, '');
        const cleaned = String(raw || '').trim();
        if (!cleaned) {
            const country = SMART_COUNTRIES.find((c) => c.dial === cleanFallback) || SMART_COUNTRIES[0];
            return { empty: true, valid: true, country, nsn: '', formatted: '', provider: null };
        }

        let digitsOnly = cleaned.replace(/[^\d+]/g, '');
        if (digitsOnly.startsWith('00')) {
            digitsOnly = '+' + digitsOnly.slice(2);
        }

        let detectedCountry = SMART_COUNTRIES.find((c) => c.dial === cleanFallback) || SMART_COUNTRIES[0];
        let nsn = '';

        const sortedByDial = [...SMART_COUNTRIES].sort((a, b) => b.dial.length - a.dial.length);

        if (digitsOnly.startsWith('+')) {
            const withoutPlus = digitsOnly.slice(1).replace(/\D/g, '');
            const matched = sortedByDial.find((c) => withoutPlus.startsWith(c.dial));
            if (matched) {
                detectedCountry = matched;
                nsn = withoutPlus.slice(matched.dial.length).replace(/^0+/, '');
            } else {
                nsn = withoutPlus;
            }
        } else {
            const pureDigits = digitsOnly.replace(/\D/g, '');
            if (pureDigits.startsWith('08') || pureDigits.startsWith('02')) {
                detectedCountry = SMART_COUNTRIES[0];
                nsn = pureDigits.replace(/^0+/, '');
            } else if (pureDigits.startsWith('62') && pureDigits.length >= 9) {
                detectedCountry = SMART_COUNTRIES[0];
                nsn = pureDigits.slice(2).replace(/^0+/, '');
            } else {
                const matched = sortedByDial.find((c) => pureDigits.startsWith(c.dial) && pureDigits.length >= c.dial.length + c.min);
                if (matched) {
                    detectedCountry = matched;
                    nsn = pureDigits.slice(matched.dial.length).replace(/^0+/, '');
                } else {
                    nsn = pureDigits.replace(/^0+/, '');
                }
            }
        }

        nsn = nsn.slice(0, 13);
        const valid = nsn.length >= detectedCountry.min && nsn.length <= detectedCountry.max;
        const grouped = formatNationalNumber(detectedCountry.dial, nsn);
        const formatted = nsn ? `+${detectedCountry.dial} ${grouped}` : `+${detectedCountry.dial} `;
        const provider = detectedCountry.dial === '62' && nsn ? detectIndonesianProvider(nsn) : null;

        return {
            empty: false,
            valid,
            country: detectedCountry,
            nsn,
            formatted,
            e164: nsn ? `+${detectedCountry.dial}${nsn}` : '',
            provider,
        };
    }

    function initSmartPhoneInputs() {
        const phoneInput = document.getElementById('smartPhoneInput');
        const countrySelect = document.getElementById('smartPhoneCountrySelect');
        const badgeTextEl = document.getElementById('smartPhoneDetectedText') || document.getElementById('smartPhoneBadge');
        const badgeWrapEl = document.getElementById('smartPhoneDetectedBadge') || document.getElementById('smartPhoneBadge');
        const statusIcon = document.getElementById('smartPhoneStatusIcon');
        const hintEl = document.getElementById('smartPhoneHelper') || document.getElementById('smartPhoneHint');
        const clearBtn = document.getElementById('phoneClearBtn');

        if (!phoneInput || !countrySelect) return;

        function setCountrySelectDial(dialDigits) {
            const plain = String(dialDigits).replace(/^\+/, '');
            const withPlus = `+${plain}`;
            const hasPlain = Array.from(countrySelect.options).some((o) => o.value === plain);
            const hasPlus = Array.from(countrySelect.options).some((o) => o.value === withPlus);
            if (hasPlain) {
                countrySelect.value = plain;
            } else if (hasPlus) {
                countrySelect.value = withPlus;
            }
        }

        function refreshPhoneState(reformatInput = false) {
            const currentDial = String(countrySelect.value || '62').replace(/^\+/, '');
            const parsed = parseSmartPhone(phoneInput.value, currentDial);

            setCountrySelectDial(parsed.country.dial);

            if (reformatInput && !parsed.empty) {
                phoneInput.value = parsed.formatted;
            }

            if (badgeTextEl) {
                if (parsed.empty) {
                    badgeTextEl.textContent = `${parsed.country.name} (+${parsed.country.dial})`;
                } else if (parsed.valid) {
                    badgeTextEl.textContent = `${parsed.provider || parsed.country.name} (+${parsed.country.dial}) • Valid`;
                } else {
                    badgeTextEl.textContent = `${parsed.country.name} (+${parsed.country.dial}) • Belum Lengkap`;
                }
            }

            if (badgeWrapEl) {
                if (parsed.empty) {
                    badgeWrapEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-500/20';
                } else if (parsed.valid) {
                    badgeWrapEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25';
                } else {
                    badgeWrapEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/25';
                }
            }

            if (statusIcon) {
                if (parsed.empty) {
                    statusIcon.innerHTML = '<i class="bi bi-telephone text-slate-400 text-xs"></i>';
                } else if (parsed.valid) {
                    statusIcon.innerHTML = '<i class="bi bi-check-circle-fill text-emerald-500 text-xs"></i>';
                } else {
                    statusIcon.innerHTML = '<i class="bi bi-exclamation-circle-fill text-amber-500 text-xs"></i>';
                }
            }

            if (hintEl && !parsed.empty) {
                hintEl.textContent = parsed.valid
                    ? `Format E.164 siap disimpan: ${parsed.e164} (${parsed.provider || parsed.country.name}).`
                    : `Masukkan ${parsed.country.min}–${parsed.country.max} digit nomor ${parsed.country.name} (+${parsed.country.dial}).`;
            }
        }

        phoneInput.addEventListener('input', () => {
            refreshPhoneState(false);
        });

        phoneInput.addEventListener('blur', () => {
            refreshPhoneState(true);
        });

        countrySelect.addEventListener('change', () => {
            const newDial = String(countrySelect.value || '62').replace(/^\+/, '');
            const raw = phoneInput.value.trim();
            if (raw) {
                const stripped = raw.replace(/^\+\d{1,3}\s*/, '').replace(/^0+/, '').replace(/\D/g, '');
                phoneInput.value = stripped ? `+${newDial} ${formatNationalNumber(newDial, stripped)}` : `+${newDial} `;
            } else {
                phoneInput.value = `+${newDial} `;
            }
            refreshPhoneState(true);
            phoneInput.focus();
        });

        clearBtn?.addEventListener('click', () => {
            phoneInput.value = '';
            refreshPhoneState(false);
            phoneInput.focus();
        });

        refreshPhoneState(true);
    }

    initSmartPhoneInputs();

    // =========================================================================
    // 6E. INTERACTIVE 1:1 SQUARE AVATAR CROPPER (MAX 8MB) & LIVE THUMBNAIL
    // =========================================================================
    function initAvatarCropper() {
        const fileInput = document.getElementById('avatarFileInput') || document.getElementById('avatarInput');
        const cropperPanel = document.getElementById('avatarCropperPanel');
        const toggleCropBtn = document.getElementById('toggleAvatarCropStudioBtn');
        const toggleCropLabel = document.getElementById('toggleAvatarCropStudioLabel');
        const canvas = document.getElementById('avatarCropCanvas');
        const zoomRange = document.getElementById('avatarCropZoomRange') || document.getElementById('avatarZoomRange');
        const zoomValueLabel = document.getElementById('avatarZoomValueLabel');
        const rotateLeftBtn = document.getElementById('avatarCropRotateLeftBtn') || document.getElementById('avatarRotateLeftBtn');
        const rotateRightBtn = document.getElementById('avatarCropRotateBtn') || document.getElementById('avatarRotateRightBtn');
        const resetCropBtn = document.getElementById('avatarCropResetBtn') || document.getElementById('avatarResetCropBtn');
        const applyCropBtn = document.getElementById('applyAvatarCropBtn') || document.getElementById('avatarApplyCropBtn');
        const cancelCropBtn = document.getElementById('cancelAvatarCropBtn') || document.getElementById('avatarCancelCropBtn');
        const croppedInput = document.getElementById('croppedAvatarInput');
        const formPreviewImg = document.getElementById('avatarFormPreviewImg');
        const formPreviewInitials = document.getElementById('avatarFormPreviewInitials');
        const heroPreviewImg = document.getElementById('heroAvatarImg');
        const heroPreviewInitials = document.getElementById('heroAvatarInitials');
        const lightboxImg = document.getElementById('avatarLightboxImg');
        const heroLightboxTrigger = document.getElementById('heroAvatarPreviewTrigger');
        const formPreviewTrigger = document.getElementById('avatarFormPreviewTrigger');
        const lightboxOpenEditBtn = document.getElementById('lightboxOpenEditBtn');
        const sizeBadge = document.getElementById('avatarSizeBadge');
        const errorMsg = document.getElementById('avatarErrorMsg');

        heroLightboxTrigger?.addEventListener('click', () => {
            const hasAvatar = heroPreviewImg && !heroPreviewImg.classList.contains('hidden') && heroPreviewImg.getAttribute('src');
            if (hasAvatar) {
                openModalById('avatarLightboxModal');
            } else {
                openModalById('editProfileModal');
            }
        });

        lightboxOpenEditBtn?.addEventListener('click', () => {
            closeModalEl(document.getElementById('avatarLightboxModal'), true);
            openModalById('editProfileModal');
        });

        if (!fileInput || !canvas || !cropperPanel) return;

        const ctx = canvas.getContext('2d');
        const MAX_BYTES = 8 * 1024 * 1024; // 8MB

        let loadedImage = null;
        let zoom = 1;
        let rotation = 0;
        let offsetX = 0;
        let offsetY = 0;
        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;

        function updateZoomLabel() {
            if (zoomValueLabel) {
                zoomValueLabel.textContent = `${zoom.toFixed(1)}x`;
            }
        }

        function drawCropCanvas() {
            if (!ctx || !loadedImage) return;
            const size = canvas.width;
            ctx.clearRect(0, 0, size, size);

            ctx.save();
            ctx.translate(size / 2 + offsetX, size / 2 + offsetY);
            ctx.rotate((rotation * Math.PI) / 180);

            const isRotated90 = Math.abs(rotation % 180) === 90;
            const imgW = isRotated90 ? loadedImage.height : loadedImage.width;
            const imgH = isRotated90 ? loadedImage.width : loadedImage.height;

            const baseScale = Math.max(size / imgW, size / imgH);
            const finalScale = baseScale * zoom;

            const drawW = loadedImage.width * finalScale;
            const drawH = loadedImage.height * finalScale;

            ctx.drawImage(loadedImage, -drawW / 2, -drawH / 2, drawW, drawH);
            ctx.restore();
        }

        function exportCroppedDataUrl() {
            if (!loadedImage) return '';
            const outCanvas = document.createElement('canvas');
            outCanvas.width = 512;
            outCanvas.height = 512;
            const outCtx = outCanvas.getContext('2d');
            if (!outCtx) return '';

            const ratio = 512 / canvas.width;
            outCtx.save();
            outCtx.translate(256 + offsetX * ratio, 256 + offsetY * ratio);
            outCtx.rotate((rotation * Math.PI) / 180);

            const isRotated90 = Math.abs(rotation % 180) === 90;
            const imgW = isRotated90 ? loadedImage.height : loadedImage.width;
            const imgH = isRotated90 ? loadedImage.width : loadedImage.height;
            const baseScale = Math.max(512 / imgW, 512 / imgH);
            const finalScale = baseScale * zoom;

            outCtx.drawImage(
                loadedImage,
                (-loadedImage.width * finalScale) / 2,
                (-loadedImage.height * finalScale) / 2,
                loadedImage.width * finalScale,
                loadedImage.height * finalScale
            );
            outCtx.restore();

            return outCanvas.toDataURL('image/jpeg', 0.92);
        }

        function showAvatarThumbnails(dataUrl) {
            if (!dataUrl) return;
            if (formPreviewImg) {
                formPreviewImg.src = dataUrl;
                formPreviewImg.classList.remove('hidden');
            }
            if (formPreviewInitials) {
                formPreviewInitials.classList.add('hidden');
            }
            if (heroPreviewImg) {
                heroPreviewImg.src = dataUrl;
                heroPreviewImg.classList.remove('hidden');
            }
            if (heroPreviewInitials) {
                heroPreviewInitials.classList.add('hidden');
            }
            if (lightboxImg) {
                lightboxImg.src = dataUrl;
            }
        }

        function dataUrlToFile(dataUrl, filename = 'avatar-cropped.jpg') {
            try {
                const parts = dataUrl.split(',');
                if (parts.length < 2) return null;
                const mimeMatch = parts[0].match(/:(.*?);/);
                const mime = mimeMatch ? mimeMatch[1] : 'image/jpeg';
                const bstr = atob(parts[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename, { type: mime, lastModified: Date.now() });
            } catch (e) {
                return null;
            }
        }

        function syncPreviewAndHiddenInput(showToastMsg = false) {
            const dataUrl = exportCroppedDataUrl();
            if (!dataUrl) return;
            if (croppedInput) croppedInput.value = dataUrl;
            try {
                const croppedFile = dataUrlToFile(dataUrl, 'avatar-cropped-1x1.jpg');
                if (croppedFile && typeof DataTransfer !== 'undefined') {
                    const dt = new DataTransfer();
                    dt.items.add(croppedFile);
                    fileInput.files = dt.files;
                }
            } catch (e) {}
            showAvatarThumbnails(dataUrl);
            if (showToastMsg) {
                window.toast?.success('Potongan foto 1:1 diterapkan!', {
                    description: 'Thumbnail foto profil telah diperbarui. Klik Simpan untuk menyimpan.',
                    duration: 2800,
                });
            }
        }

        function loadImageSourceIntoCropper(src, openPanel = true) {
            if (!src) return;
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                loadedImage = img;
                zoom = 1;
                rotation = 0;
                offsetX = 0;
                offsetY = 0;
                if (zoomRange) zoomRange.value = '1';
                updateZoomLabel();
                if (openPanel) {
                    animateCollapsiblePanel(cropperPanel, true);
                    if (toggleCropLabel) toggleCropLabel.textContent = 'Sembunyikan Editor Crop';
                }
                drawCropCanvas();
                syncPreviewAndHiddenInput(false);
            };
            img.src = src;
        }

        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;

            if (errorMsg) {
                errorMsg.classList.add('hidden');
                errorMsg.textContent = '';
            }

            if (file.size > MAX_BYTES) {
                const msg = 'Ukuran foto melebihi batas maksimal 8MB!';
                if (errorMsg) {
                    errorMsg.textContent = msg;
                    errorMsg.classList.remove('hidden');
                }
                window.toast?.error(msg, {
                    description: 'Pilih foto dengan ukuran maksimal 8MB (JPG, PNG, atau WebP).',
                });
                fileInput.value = '';
                return;
            }

            if (!file.type.startsWith('image/')) {
                window.toast?.error('Format file harus berupa gambar (JPG, PNG, WebP).');
                fileInput.value = '';
                return;
            }

            if (sizeBadge) {
                const kb = Math.max(1, Math.round(file.size / 1024));
                const sizeText = kb >= 1024 ? `${(kb / 1024).toFixed(2)} MB` : `${kb} KB`;
                sizeBadge.textContent = `${sizeText} • 1:1 Siap`;
                sizeBadge.classList.remove('hidden');
            }

            const reader = new FileReader();
            reader.onload = (ev) => {
                const dataUrl = String(ev.target?.result || '');
                if (!dataUrl) return;
                // Immediately show thumbnail preview before & during crop
                showAvatarThumbnails(dataUrl);
                loadImageSourceIntoCropper(dataUrl, true);
            };
            reader.readAsDataURL(file);
        });

        function handleToggleCropper() {
            const isHidden = cropperPanel.classList.contains('hidden');
            if (!isHidden) {
                animateCollapsiblePanel(cropperPanel, false);
                if (toggleCropLabel) toggleCropLabel.textContent = 'Sesuaikan Crop 1:1';
                return;
            }

            if (loadedImage) {
                animateCollapsiblePanel(cropperPanel, true);
                if (toggleCropLabel) toggleCropLabel.textContent = 'Sembunyikan Editor Crop';
                drawCropCanvas();
                return;
            }

            const existingSrc = formPreviewImg && !formPreviewImg.classList.contains('hidden')
                ? formPreviewImg.getAttribute('src')
                : '';
            if (existingSrc) {
                loadImageSourceIntoCropper(existingSrc, true);
            } else {
                fileInput.click();
            }
        }

        toggleCropBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            handleToggleCropper();
        });

        formPreviewTrigger?.addEventListener('click', (e) => {
            e.preventDefault();
            handleToggleCropper();
        });

        zoomRange?.addEventListener('input', () => {
            zoom = parseFloat(zoomRange.value || '1') || 1;
            updateZoomLabel();
            drawCropCanvas();
            syncPreviewAndHiddenInput(false);
        });

        rotateLeftBtn?.addEventListener('click', () => {
            if (!loadedImage) return;
            rotation = (rotation - 90) % 360;
            drawCropCanvas();
            syncPreviewAndHiddenInput(false);
        });

        rotateRightBtn?.addEventListener('click', () => {
            if (!loadedImage) return;
            rotation = (rotation + 90) % 360;
            drawCropCanvas();
            syncPreviewAndHiddenInput(false);
        });

        resetCropBtn?.addEventListener('click', () => {
            if (!loadedImage) return;
            zoom = 1;
            rotation = 0;
            offsetX = 0;
            offsetY = 0;
            if (zoomRange) zoomRange.value = '1';
            updateZoomLabel();
            drawCropCanvas();
            syncPreviewAndHiddenInput(false);
        });

        applyCropBtn?.addEventListener('click', () => {
            syncPreviewAndHiddenInput(true);
            animateCollapsiblePanel(cropperPanel, false);
            if (toggleCropLabel) toggleCropLabel.textContent = 'Sesuaikan Crop 1:1';
        });

        cancelCropBtn?.addEventListener('click', () => {
            animateCollapsiblePanel(cropperPanel, false);
            if (toggleCropLabel) toggleCropLabel.textContent = 'Sesuaikan Crop 1:1';
        });

        const startDrag = (x, y) => {
            if (!loadedImage) return;
            isDragging = true;
            dragStartX = x - offsetX;
            dragStartY = y - offsetY;
        };

        const moveDrag = (x, y) => {
            if (!isDragging || !loadedImage) return;
            offsetX = Math.max(-180, Math.min(180, x - dragStartX));
            offsetY = Math.max(-180, Math.min(180, y - dragStartY));
            drawCropCanvas();
        };

        const endDrag = () => {
            if (isDragging) {
                isDragging = false;
                syncPreviewAndHiddenInput(false);
            }
        };

        canvas.addEventListener('mousedown', (e) => startDrag(e.clientX, e.clientY));
        window.addEventListener('mousemove', (e) => moveDrag(e.clientX, e.clientY));
        window.addEventListener('mouseup', endDrag);

        canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                e.preventDefault();
                startDrag(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: false });

        canvas.addEventListener('touchmove', (e) => {
            if (isDragging && e.touches.length === 1) {
                e.preventDefault();
                moveDrag(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: false });

        canvas.addEventListener('touchend', endDrag);

        // Ensure cropped image is synced on form submit
        cropperPanel.closest('form')?.addEventListener('submit', () => {
            if (loadedImage) {
                syncPreviewAndHiddenInput(false);
            }
        });
    }

    initAvatarCropper();

    // =========================================================================
    // 7. STICKER BARCODE POPUP (DESKTOP) / HALF-SCREEN BOTTOM SHEET (MOBILE)
    // =========================================================================
    function renderStickerPreviewSvg(code) {
        const svgEl = document.getElementById('stickerPopupBarcodeSvg');
        if (!svgEl || !code) return;

        const draw = () => {
            if (typeof window.JsBarcode === 'function') {
                try {
                    window.JsBarcode(svgEl, code, {
                        format: 'CODE128',
                        width: 1.8,
                        height: 48,
                        displayValue: false,
                        margin: 0,
                    });
                } catch (e) {}
            }
        };

        if (typeof window.JsBarcode === 'function') {
            draw();
        } else {
            let script = document.getElementById('jsbarcode-cdn-script');
            if (!script) {
                script = document.createElement('script');
                script.id = 'jsbarcode-cdn-script';
                script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js';
                script.onload = draw;
                document.head.appendChild(script);
            } else {
                script.addEventListener('load', draw, { once: true });
            }
        }
    }

    function openBarcodeModal(data = {}) {
        const modal = document.getElementById('globalBarcodeSheetModal');
        if (!modal) return;

        const code = (data.barcode || data.kode || 'BRG-000001').toUpperCase();
        const name = data.name || 'Barang Inventaris';
        const room = data.room || 'Diskominfo Kabupaten Garut';
        const category = data.category || 'Inventaris Aset';
        const printUrl = data.printUrl || '';

        const nameEl = document.getElementById('stickerPopupProductName');
        const codeEl = document.getElementById('stickerPopupBarcodeText');
        const metaEl = document.getElementById('stickerPopupMetaText');
        const copyBtn = document.getElementById('stickerPopupCopyBtn');
        const fullPrintLink = document.getElementById('stickerPopupOpenPrintUrl');

        if (nameEl) nameEl.textContent = name;
        if (codeEl) codeEl.textContent = code;
        if (metaEl) metaEl.textContent = `${category} • ${room}`;
        if (copyBtn) copyBtn.setAttribute('data-copy', code);
        if (fullPrintLink && printUrl) fullPrintLink.href = printUrl;

        modal.dataset.currentCode = code;
        modal.dataset.currentName = name;
        modal.dataset.currentRoom = room;
        modal.dataset.currentCategory = category;

        openModalById('globalBarcodeSheetModal');
        renderStickerPreviewSvg(code);
    }

    window.openBarcodeModal = openBarcodeModal;

    document.addEventListener('click', (event) => {
        const barcodeTrigger = event.target.closest('[data-barcode-modal]');
        if (!barcodeTrigger) return;

        event.preventDefault();
        openBarcodeModal({
            barcode: barcodeTrigger.getAttribute('data-barcode') || '',
            name: barcodeTrigger.getAttribute('data-product-name') || '',
            room: barcodeTrigger.getAttribute('data-product-room') || '',
            category: barcodeTrigger.getAttribute('data-product-category') || '',
            printUrl: barcodeTrigger.getAttribute('data-print-url') || barcodeTrigger.getAttribute('href') || '',
        });
    });

    document.getElementById('stickerPopupPrintNowBtn')?.addEventListener('click', () => {
        const modal = document.getElementById('globalBarcodeSheetModal');
        const svgEl = document.getElementById('stickerPopupBarcodeSvg');
        if (!modal || !svgEl) return;

        const copies = parseInt(document.getElementById('stickerCopiesSelect')?.value || '1', 10) || 1;
        const name = modal.dataset.currentName || 'Barang Inventaris';
        const code = modal.dataset.currentCode || '';
        const room = modal.dataset.currentRoom || 'Diskominfo Garut';
        const svgMarkup = svgEl.outerHTML;

        let cardsHtml = '';
        for (let i = 0; i < copies; i++) {
            cardsHtml += `
                <div class="sticker-item">
                    <div class="sticker-head">INVENTARIS DISKOMINFO KAB. GARUT</div>
                    <div class="sticker-name">${name}</div>
                    <div class="sticker-svg">${svgMarkup}</div>
                    <div class="sticker-code">${code}</div>
                    <div class="sticker-room">${room}</div>
                </div>
            `;
        }

        let printFrame = document.getElementById('hiddenStickerPrintFrame');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'hiddenStickerPrintFrame';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = '0';
            document.body.appendChild(printFrame);
        }

        const doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak Stiker Barcode — ${code}</title>
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    body { font-family: Arial, sans-serif; padding: 6mm; }
                    .grid { display: flex; flex-wrap: wrap; gap: 4mm; }
                    .sticker-item {
                        width: 68mm;
                        border: 1.5px solid #0f172a;
                        border-radius: 3mm;
                        padding: 3mm;
                        text-align: center;
                        page-break-inside: avoid;
                    }
                    .sticker-head { font-size: 7pt; font-weight: 800; letter-spacing: 0.05em; border-bottom: 1px solid #cbd5e1; padding-bottom: 1mm; margin-bottom: 1.5mm; }
                    .sticker-name { font-size: 8.5pt; font-weight: 700; margin-bottom: 1.5mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                    .sticker-svg svg { max-width: 100%; height: 12mm; }
                    .sticker-code { font-family: monospace; font-size: 9pt; font-weight: 800; margin-top: 1mm; }
                    .sticker-room { font-size: 6.5pt; color: #475569; margin-top: 0.5mm; }
                </style>
            </head>
            <body>
                <div class="grid">${cardsHtml}</div>
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 180);
    });

    // =========================================================================
    // 8. SHORT DEEP LINK & RICH SHARE MODAL / SHEET
    // =========================================================================
    function openShareModal(opts = {}) {
        const modal = document.getElementById('globalShareSheetModal');
        if (!modal) return;

        const code = opts.code || document.body.dataset.pageShareCode || 'dash';
        const title = opts.title || document.body.dataset.pageShareTitle || document.title;
        const description = opts.description || document.body.dataset.pageShareDescription || 'Sistem Informasi Inventarisasi Barang & Aset Diskominfo Kabupaten Garut.';
        const image = opts.image || document.body.dataset.pageShareImage || '/images/logo-diskominfo.png';
        const shortUrl = `${window.location.origin}/s/${code}`;

        const imgEl = document.getElementById('shareModalThumbnail');
        const titleEl = document.getElementById('shareModalTitle');
        const descEl = document.getElementById('shareModalDescription');
        const inputEl = document.getElementById('shareModalShortUrlInput');
        const copyBtn = document.getElementById('shareModalCopyBtn');
        const waBtn = document.getElementById('shareModalWhatsappBtn');
        const tgBtn = document.getElementById('shareModalTelegramBtn');
        const nativeBtn = document.getElementById('shareModalNativeBtn');

        if (imgEl) imgEl.src = image;
        if (titleEl) titleEl.textContent = title;
        if (descEl) descEl.textContent = description;
        if (inputEl) inputEl.value = shortUrl;
        if (copyBtn) copyBtn.setAttribute('data-copy', shortUrl);

        const shareText = `${title}\n${description}\n${shortUrl}`;
        if (waBtn) waBtn.href = `https://wa.me/?text=${encodeURIComponent(shareText)}`;
        if (tgBtn) tgBtn.href = `https://t.me/share/url?url=${encodeURIComponent(shortUrl)}&text=${encodeURIComponent(title + ' — ' + description)}`;

        if (nativeBtn) {
            if (typeof navigator.share === 'function') {
                nativeBtn.classList.remove('hidden');
                nativeBtn.onclick = async () => {
                    try {
                        await navigator.share({ title, text: description, url: shortUrl });
                    } catch (e) {}
                };
            } else {
                nativeBtn.classList.add('hidden');
            }
        }

        openModalById('globalShareSheetModal');
    }

    window.openShareModal = openShareModal;

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const shareBtn = event.target.closest('button[data-share-code], a[data-share-code], [data-share-trigger][data-share-code]');
        if (!shareBtn || shareBtn === document.body || shareBtn === document.documentElement) return;

        event.preventDefault();
        event.stopPropagation();
        openShareModal({
            code: shareBtn.getAttribute('data-share-code'),
            title: shareBtn.getAttribute('data-share-title'),
            description: shareBtn.getAttribute('data-share-description'),
            image: shareBtn.getAttribute('data-share-image'),
        });
    });

    // =========================================================================
    // 9. SEMI-REALTIME LIVE STATE ENGINE + PWA SERVICE WORKER
    // =========================================================================
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'NAVIGATE_DEEP_LINK' && event.data.url) {
                window.location.href = event.data.url;
            }
        });
    }

    // Request Browser Notification Permission Handler
    document.getElementById('enableWebPushBtn')?.addEventListener('click', async () => {
        if (!('Notification' in window)) {
            window.toast?.warning('Browser ini tidak mendukung Web Notification.');
            return;
        }
        const perm = await Notification.requestPermission();
        if (perm === 'granted') {
            window.toast?.success('Notifikasi Perangkat Aktif!', {
                description: 'Anda akan menerima notifikasi persetujuan mutasi dan peringatan stok secara langsung.',
            });
            if ('serviceWorker' in navigator) {
                const reg = await navigator.serviceWorker.ready;
                reg.showNotification('Inventaris Diskominfo Garut', {
                    body: 'Notifikasi perangkat berhasil diaktifkan. Ketuk untuk membuka dashboard.',
                    icon: '/images/logo-diskominfo.png',
                    data: { url: '/dashboard' },
                }).catch(() => {});
            }
        } else {
            window.toast?.warning('Izin notifikasi belum diberikan oleh browser.');
        }
    });

    // Webhook-Driven Realtime Notification Listener & BroadcastChannel Sync
    const liveChannel = typeof BroadcastChannel !== 'undefined'
        ? new BroadcastChannel('diskominfo_inventory_live')
        : null;

    let currentLiveVersion = null;
    let currentWebhookCursor = null;
    let knownNotificationIds = new Set();
    let isFirstPoll = true;

    function escapeHtmlText(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderNavbarNotificationItems(items) {
        const listEl = document.getElementById('navbarNotificationList');
        if (!listEl || !Array.isArray(items)) return;

        if (items.length === 0) {
            listEl.innerHTML = `
                <div class="px-4 py-8 text-center">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <i class="bi bi-bell-slash text-base"></i>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada notifikasi</p>
                </div>
            `;
            return;
        }

        const colorMap = {
            approved: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/15 dark:text-emerald-400',
            approval: 'text-amber-600 bg-amber-50 dark:bg-amber-500/15 dark:text-amber-400',
            low_stock: 'text-amber-600 bg-amber-50 dark:bg-amber-500/15 dark:text-amber-400',
            rejected: 'text-rose-600 bg-rose-50 dark:bg-rose-500/15 dark:text-rose-400',
            out_of_stock: 'text-rose-600 bg-rose-50 dark:bg-rose-500/15 dark:text-rose-400',
            info: 'text-blue-600 bg-blue-50 dark:bg-blue-500/15 dark:text-blue-400',
        };

        listEl.innerHTML = items.map((n) => {
            const isUnread = n.is_unread !== false;
            const lvlColor = colorMap[n.category] || colorMap.info;
            const iconClass = escapeHtmlText(n.icon || 'bi-bell-fill');
            const redDotHtml = isUnread
                ? `<span data-notif-unread-pill data-notif-red-dot class="inline-flex items-center justify-center shrink-0" title="Belum dibaca" aria-label="Belum dibaca">
                       <span class="w-2.5 h-2.5 rounded-full bg-rose-500 ring-4 ring-rose-500/20 animate-pulse"></span>
                   </span>`
                : '';

            return `
                <div class="notif-swipe-row relative overflow-hidden select-none"
                     data-swipe-notification="${escapeHtmlText(n.id)}"
                     data-is-unread="${isUnread ? '1' : '0'}"
                     data-mark-read-url="${escapeHtmlText(n.mark_read_url)}"
                     data-delete-url="${escapeHtmlText(n.delete_url)}">
                    <div class="notif-swipe-bg-left absolute inset-y-0 left-0 w-1/2 bg-emerald-500/90 text-white flex items-center justify-start pl-4 gap-1.5 text-[11px] font-bold opacity-0 pointer-events-none transition-opacity">
                        <i class="bi bi-check2-all text-sm"></i>
                        <span>Dibaca</span>
                    </div>
                    <div class="notif-swipe-bg-right absolute inset-y-0 right-0 w-1/2 bg-rose-600/90 text-white flex items-center justify-end pr-4 gap-1.5 text-[11px] font-bold opacity-0 pointer-events-none transition-opacity">
                        <span>Hapus</span>
                        <i class="bi bi-trash3-fill text-xs"></i>
                    </div>
                    <a href="${escapeHtmlText(n.go_url)}"
                       class="notif-swipe-card relative z-10 flex items-start gap-3 px-4 py-3 bg-white dark:bg-[#0f1b38] hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors group ${isUnread ? 'bg-blue-50/40 dark:bg-blue-950/25' : ''}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 ${lvlColor} group-hover:scale-105 transition-transform">
                            <i class="bi ${iconClass} text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">${escapeHtmlText(n.title)}</p>
                                ${redDotHtml}
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5 leading-snug">${escapeHtmlText(n.message)}</p>
                            <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5">
                                <i class="bi bi-clock text-[9px]"></i>
                                <span>${escapeHtmlText(n.created_at_human || 'Baru saja')}</span>
                            </p>
                        </div>
                    </a>
                </div>
            `;
        }).join('');

        initNotificationSwipe(listEl);
    }

    function applyWebhookNotifications(notifications, webhookStream = null, fromBroadcast = false) {
        if (!notifications) return;

        const unreadCount = Number(notifications.unread_count || 0);
        updateUnreadBadgeCount(unreadCount);

        const latestList = notifications.latest || [];
        let hasNewArrival = false;

        latestList.forEach((n) => {
            if (!isFirstPoll && !knownNotificationIds.has(n.id)) {
                hasNewArrival = true;
                window.toast?.info(n.title, {
                    description: n.message,
                    duration: 6000,
                    action: {
                        label: 'Lihat Detail',
                        href: n.go_url,
                    },
                });

                if ('Notification' in window && Notification.permission === 'granted' && !fromBroadcast && 'serviceWorker' in navigator) {
                    navigator.serviceWorker.ready.then((reg) => {
                        reg.showNotification(n.title, {
                            body: n.message,
                            icon: '/images/logo-diskominfo.png',
                            data: { url: n.go_url },
                        }).catch(() => {});
                    });
                }
            }
            knownNotificationIds.add(n.id);
        });

        const newCursor = webhookStream?.cursor || null;
        if (hasNewArrival || (newCursor && newCursor !== currentWebhookCursor)) {
            renderNavbarNotificationItems(latestList);
            if (newCursor) {
                currentWebhookCursor = newCursor;
                window.dispatchEvent(new CustomEvent('diskominfo:webhook-event', {
                    detail: webhookStream,
                }));
            }
        }
    }

    function applyLiveStatePayload(payload, fromBroadcast = false) {
        if (!payload || !payload.ok) return;

        // 1. Update Product Summary Cards if on Products page
        if (payload.summary) {
            const map = {
                total: payload.summary.total,
                active: payload.summary.active,
                low_stock: payload.summary.low_stock,
                out_of_stock: payload.summary.out_of_stock,
            };
            Object.entries(map).forEach(([key, val]) => {
                document.querySelectorAll(`[data-live-summary="${key}"]`).forEach((el) => {
                    const formatted = Number(val).toLocaleString('id-ID');
                    if (el.textContent.trim() !== String(val) && el.textContent.trim() !== formatted) {
                        el.textContent = val;
                        el.classList.add('scale-105', 'transition-transform');
                        setTimeout(() => el.classList.remove('scale-105'), 260);
                    }
                });
            });
        }

        // 2. Update Dashboard Metric Cards if on Dashboard
        if (payload.dashboard) {
            Object.entries(payload.dashboard).forEach(([key, val]) => {
                document.querySelectorAll(`[data-live-metric="${key}"]`).forEach((el) => {
                    const formatted = Number(val).toLocaleString('id-ID');
                    if (el.textContent.trim() !== formatted) {
                        el.textContent = formatted;
                    }
                });
            });

            // Sidebar Pending Mutation Badge
            const sidebarBadge = document.getElementById('sidebarPendingMutationBadge');
            if (sidebarBadge) {
                const pendingCount = Number(payload.dashboard.pending_mutations || 0);
                sidebarBadge.textContent = pendingCount;
                sidebarBadge.classList.toggle('hidden', pendingCount <= 0);
            }
        }

        // 3. Update Navbar Unread Notification Badge & Webhook-Driven Preview List
        let notifToastFired = false;
        if (payload.notifications) {
            const prevKnownSize = knownNotificationIds.size;
            applyWebhookNotifications(payload.notifications, payload.webhook_stream || null, fromBroadcast);
            if (!isFirstPoll && knownNotificationIds.size > prevKnownSize) {
                notifToastFired = true;
            }
        }

        // 4. Detect Data Manipulation by Another User
        if (currentLiveVersion && payload.version && currentLiveVersion !== payload.version) {
            if (typeof window.refreshProductsTableRealtime === 'function') {
                window.refreshProductsTableRealtime();
            }
            if (!fromBroadcast && !notifToastFired && payload.latest_product) {
                window.toast?.info('Data Inventaris Diperbarui', {
                    description: `Sinkronisasi semi-realtime: ${payload.latest_product.name} (${payload.latest_product.stock} unit)`,
                    duration: 4000,
                    action: {
                        label: 'Lihat',
                        href: payload.latest_product.url,
                    },
                });
            }
        }

        currentLiveVersion = payload.version;
        isFirstPoll = false;
    }

    async function pollLiveState() {
        if (document.visibilityState === 'hidden') return;
        if (!document.getElementById('navbarUnreadBadge') && !document.querySelector('[data-live-metric]') && !document.querySelector('[data-live-summary]')) {
            return;
        }
        try {
            const res = await fetch('/api/live-state', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            applyLiveStatePayload(data, false);
            liveChannel?.postMessage(data);
        } catch (e) {}
    }

    async function listenWebhookNotificationStream() {
        if (document.visibilityState === 'hidden') return;
        if (!document.getElementById('navbarUnreadBadge')) return;
        try {
            const res = await fetch('/api/webhooks/stream', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data && data.ok && data.notifications) {
                applyWebhookNotifications(data.notifications, data, false);
                isFirstPoll = false;
            }
        } catch (e) {}
    }

    // =========================================================================
    // 10. REALTIME AJAX NOTIFICATION ACTIONS (ZERO MANUAL REFRESH)
    // =========================================================================
    function initRealtimeNotificationActions() {
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            const isSyncForm = form.hasAttribute('data-ajax-notif-sync');
            const isReadAllForm = form.hasAttribute('data-ajax-notif-read-all');
            if (!isSyncForm && !isReadAllForm) return;

            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const res = await fetch(form.action, {
                    method: (form.getAttribute('method') || 'POST').toUpperCase(),
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (res.ok) {
                    const data = await res.json();
                    if (typeof data.unread_count === 'number') {
                        updateUnreadBadgeCount(data.unread_count);
                    }
                    if (Array.isArray(data.recent_notifications)) {
                        renderNavbarNotificationItems(data.recent_notifications);
                    }
                    if (isReadAllForm) {
                        document.querySelectorAll('[data-notif-unread-pill], [data-notif-red-dot], #notificationsPageUnreadBadge').forEach((el) => el.remove());
                        document.querySelectorAll('[data-swipe-notification]').forEach((row) => {
                            row.setAttribute('data-is-unread', '0');
                            row.setAttribute('data-unread', '0');
                        });
                    }
                    window.toast?.success(data.message || (isReadAllForm ? 'Semua notifikasi ditandai dibaca' : 'Notifikasi disinkronkan secara realtime'));
                }
            } catch (err) {
                window.toast?.error('Gagal memproses aksi notifikasi secara realtime.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    initRealtimeNotificationActions();

    // =========================================================================
    // 11. REUSABLE MEDIA UPLOADER WITH INSTANT CLIENT-SIDE THUMBNAIL PREVIEW
    // =========================================================================
    function openUniversalImageCropperModal(file, onCroppedFileReady) {
        if (!file || !file.type.startsWith('image/')) return;

        let modal = document.getElementById('universalImageCropperModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'universalImageCropperModal';
            modal.className = 'fixed inset-0 z-[120] hidden items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs';
            modal.innerHTML = `
                <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col max-h-[90dvh]">
                    <div class="px-4 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-blue-600/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="bi bi-crop text-xs"></i>
                            </span>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Potong & Sesuaikan Gambar (1:1)</h3>
                        </div>
                        <button type="button" data-ucrop-close class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center cursor-pointer">
                            <i class="bi bi-x-lg text-xs"></i>
                        </button>
                    </div>
                    <div class="p-4 space-y-3 overflow-y-auto">
                        <div class="flex justify-center">
                            <div class="relative w-60 h-60 rounded-2xl overflow-hidden border-2 border-blue-500/50 bg-slate-950 shadow-inner select-none touch-none">
                                <canvas data-ucrop-canvas width="320" height="320" class="w-full h-full cursor-grab active:cursor-grabbing"></canvas>
                                <div class="pointer-events-none absolute inset-0 border border-white/25 grid grid-cols-3 grid-rows-3">
                                    <div class="border-r border-b border-white/15"></div>
                                    <div class="border-r border-b border-white/15"></div>
                                    <div class="border-b border-white/15"></div>
                                    <div class="border-r border-b border-white/15"></div>
                                    <div class="border-r border-b border-white/15"></div>
                                    <div class="border-b border-white/15"></div>
                                    <div class="border-r border-white/15"></div>
                                    <div class="border-r border-white/15"></div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                <span>Zoom</span>
                                <span data-ucrop-zoom-label class="font-mono text-blue-600 dark:text-blue-400">100%</span>
                            </div>
                            <input type="range" data-ucrop-zoom min="1" max="3" step="0.05" value="1" class="w-full accent-blue-600 cursor-pointer">
                            <div class="flex items-center justify-center gap-2 pt-1">
                                <button type="button" data-ucrop-rot-left class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 cursor-pointer">
                                    <i class="bi bi-arrow-counterclockwise mr-1"></i> Putar Kiri
                                </button>
                                <button type="button" data-ucrop-rot-right class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 cursor-pointer">
                                    <i class="bi bi-arrow-clockwise mr-1"></i> Putar Kanan
                                </button>
                                <button type="button" data-ucrop-reset class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 cursor-pointer">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2 bg-slate-50/70 dark:bg-slate-900/80">
                        <button type="button" data-ucrop-close class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200/70 dark:hover:bg-slate-800 cursor-pointer">
                            Batal
                        </button>
                        <button type="button" data-ucrop-apply class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm cursor-pointer">
                            <i class="bi bi-check2-circle mr-1"></i> Terapkan Crop
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        const canvas = modal.querySelector('[data-ucrop-canvas]');
        const ctx = canvas?.getContext('2d');
        const zoomInput = modal.querySelector('[data-ucrop-zoom]');
        const zoomLabel = modal.querySelector('[data-ucrop-zoom-label]');
        const rotLeftBtn = modal.querySelector('[data-ucrop-rot-left]');
        const rotRightBtn = modal.querySelector('[data-ucrop-rot-right]');
        const resetBtn = modal.querySelector('[data-ucrop-reset]');
        const applyBtn = modal.querySelector('[data-ucrop-apply]');
        const closeBtns = modal.querySelectorAll('[data-ucrop-close]');

        if (!canvas || !ctx) return;

        let imgObj = null;
        let uZoom = 1;
        let uRot = 0;
        let uOffX = 0;
        let uOffY = 0;
        let uDrag = false;
        let uStartX = 0;
        let uStartY = 0;

        function drawUCanvas() {
            if (!imgObj) return;
            const w = canvas.width;
            const h = canvas.height;
            ctx.clearRect(0, 0, w, h);
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, w, h);

            ctx.save();
            ctx.translate(w / 2 + uOffX, h / 2 + uOffY);
            ctx.rotate((uRot * Math.PI) / 180);

            const rot90 = Math.abs(uRot % 180) === 90;
            const iw = rot90 ? imgObj.height : imgObj.width;
            const ih = rot90 ? imgObj.width : imgObj.height;
            const scale = Math.max(w / iw, h / ih) * uZoom;

            ctx.drawImage(imgObj, (-imgObj.width * scale) / 2, (-imgObj.height * scale) / 2, imgObj.width * scale, imgObj.height * scale);
            ctx.restore();
        }

        function closeUModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        const reader = new FileReader();
        reader.onload = (ev) => {
            const img = new Image();
            img.onload = () => {
                imgObj = img;
                uZoom = 1;
                uRot = 0;
                uOffX = 0;
                uOffY = 0;
                if (zoomInput) zoomInput.value = '1';
                if (zoomLabel) zoomLabel.textContent = '100%';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                drawUCanvas();
            };
            img.src = String(ev.target?.result || '');
        };
        reader.readAsDataURL(file);

        if (zoomInput) {
            zoomInput.oninput = () => {
                uZoom = parseFloat(zoomInput.value || '1') || 1;
                if (zoomLabel) zoomLabel.textContent = `${Math.round(uZoom * 100)}%`;
                drawUCanvas();
            };
        }
        if (rotLeftBtn) {
            rotLeftBtn.onclick = () => {
                uRot = (uRot - 90) % 360;
                drawUCanvas();
            };
        }
        if (rotRightBtn) {
            rotRightBtn.onclick = () => {
                uRot = (uRot + 90) % 360;
                drawUCanvas();
            };
        }
        if (resetBtn) {
            resetBtn.onclick = () => {
                uZoom = 1;
                uRot = 0;
                uOffX = 0;
                uOffY = 0;
                if (zoomInput) zoomInput.value = '1';
                if (zoomLabel) zoomLabel.textContent = '100%';
                drawUCanvas();
            };
        }
        closeBtns.forEach((btn) => {
            btn.onclick = closeUModal;
        });

        canvas.onmousedown = (e) => {
            uDrag = true;
            uStartX = e.clientX - uOffX;
            uStartY = e.clientY - uOffY;
        };
        window.addEventListener('mousemove', (e) => {
            if (!uDrag) return;
            uOffX = Math.max(-180, Math.min(180, e.clientX - uStartX));
            uOffY = Math.max(-180, Math.min(180, e.clientY - uStartY));
            drawUCanvas();
        });
        window.addEventListener('mouseup', () => {
            uDrag = false;
        });

        canvas.ontouchstart = (e) => {
            if (e.touches.length === 1) {
                e.preventDefault();
                uDrag = true;
                uStartX = e.touches[0].clientX - uOffX;
                uStartY = e.touches[0].clientY - uOffY;
            }
        };
        canvas.ontouchmove = (e) => {
            if (uDrag && e.touches.length === 1) {
                e.preventDefault();
                uOffX = Math.max(-180, Math.min(180, e.touches[0].clientX - uStartX));
                uOffY = Math.max(-180, Math.min(180, e.touches[0].clientY - uStartY));
                drawUCanvas();
            }
        };
        canvas.ontouchend = () => {
            uDrag = false;
        };

        if (applyBtn) {
            applyBtn.onclick = () => {
                if (!imgObj) return;
                const out = document.createElement('canvas');
                out.width = 720;
                out.height = 720;
                const octx = out.getContext('2d');
                if (!octx) return;

                const ratio = 720 / canvas.width;
                octx.save();
                octx.translate(360 + uOffX * ratio, 360 + uOffY * ratio);
                octx.rotate((uRot * Math.PI) / 180);
                const rot90 = Math.abs(uRot % 180) === 90;
                const iw = rot90 ? imgObj.height : imgObj.width;
                const ih = rot90 ? imgObj.width : imgObj.height;
                const scale = Math.max(720 / iw, 720 / ih) * uZoom;
                octx.drawImage(imgObj, (-imgObj.width * scale) / 2, (-imgObj.height * scale) / 2, imgObj.width * scale, imgObj.height * scale);
                octx.restore();

                out.toBlob((blob) => {
                    if (!blob) return;
                    const baseName = file.name.replace(/\.[^.]+$/, '');
                    const croppedFile = new File([blob], `${baseName}-cropped.jpg`, {
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                    });
                    onCroppedFileReady(croppedFile);
                    closeUModal();
                    window.toast?.success('Potongan gambar berhasil diterapkan!');
                }, 'image/jpeg', 0.92);
            };
        }
    }

    function initReusableMediaUploaders(root = document) {
        root.querySelectorAll('[data-reusable-media-uploader="true"]').forEach((uploader) => {
            if (uploader.dataset.uploaderBound === '1') return;
            uploader.dataset.uploaderBound = '1';

            const fileInput = uploader.querySelector('[data-uploader-file-input]');
            const dropzone = uploader.querySelector('[data-uploader-dropzone]');
            const previewWrap = uploader.querySelector('[data-uploader-preview-wrap], [data-uploader-preview-wrapper]');
            const previewGrid = uploader.querySelector('[data-uploader-preview-grid]');
            const countBadge = uploader.querySelector('[data-uploader-count-badge], [data-uploader-count-text]');
            const clearBtn = uploader.querySelector('[data-uploader-clear-btn]');
            const existingWrap = uploader.querySelector('[data-uploader-existing-wrap]');
            const removeExistingBtn = uploader.querySelector('[data-uploader-remove-existing-btn]');
            const removeFlagInput = uploader.querySelector('[data-uploader-remove-flag]');
            const maxFiles = parseInt(uploader.dataset.maxFiles || '8', 10) || 8;

            if (!fileInput || !previewGrid || !previewWrap) return;

            let selectedFiles = [];

            function syncInputFiles() {
                try {
                    const dt = new DataTransfer();
                    selectedFiles.forEach((f) => dt.items.add(f));
                    fileInput.files = dt.files;
                } catch (e) {}
            }

            function renderThumbnails() {
                previewGrid.querySelectorAll('[data-selected-media-card]').forEach((el) => el.remove());
                const existingCardsCount = previewGrid.querySelectorAll('[data-existing-media-card]').length;

                if (selectedFiles.length === 0 && existingCardsCount === 0) {
                    previewWrap.classList.add('hidden');
                    clearBtn?.classList.add('hidden');
                    if (countBadge) countBadge.textContent = `0 / ${maxFiles} Foto`;
                    return;
                }

                previewWrap.classList.remove('hidden');
                if (selectedFiles.length > 0) {
                    clearBtn?.classList.remove('hidden');
                    if (countBadge) countBadge.textContent = `${selectedFiles.length} foto siap diunggah`;
                } else {
                    clearBtn?.classList.add('hidden');
                    if (countBadge) countBadge.textContent = `${existingCardsCount} / ${maxFiles} Foto`;
                }

                selectedFiles.forEach((file, idx) => {
                    const card = document.createElement('div');
                    card.setAttribute('data-selected-media-card', '1');
                    card.className = 'group relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xs';
                    const sizeKb = Math.max(1, Math.round(file.size / 1024));
                    const sizeLabel = sizeKb >= 1024 ? `${(sizeKb / 1024).toFixed(1)} MB` : `${sizeKb} KB`;
                    const objectUrl = URL.createObjectURL(file);

                    card.innerHTML = `
                        <div class="aspect-square w-full bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                            <img src="${objectUrl}" alt="${escapeHtmlText(file.name)}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            ${idx === 0 ? '<span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-md bg-blue-600/90 text-white text-[9px] font-extrabold uppercase tracking-wider shadow-xs">Utama</span>' : ''}
                            <div class="absolute top-1.5 right-1.5 flex items-center gap-1">
                                <button type="button" data-crop-thumb-idx="${idx}" class="w-6 h-6 rounded-lg bg-slate-900/75 hover:bg-blue-600 text-white flex items-center justify-center text-xs transition-colors cursor-pointer" title="Crop 1:1 gambar">
                                    <i class="bi bi-crop text-[10px]"></i>
                                </button>
                                <button type="button" data-remove-thumb-idx="${idx}" class="w-6 h-6 rounded-lg bg-slate-900/75 hover:bg-rose-600 text-white flex items-center justify-center text-xs transition-colors cursor-pointer" title="Hapus gambar">
                                    <i class="bi bi-x-lg text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-2 flex items-center justify-between gap-1">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate">${escapeHtmlText(file.name)}</p>
                                <p class="text-[10px] font-mono text-slate-400">${sizeLabel}</p>
                            </div>
                            <button type="button" data-crop-thumb-btn="${idx}" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-500/20 shrink-0 cursor-pointer">
                                Crop
                            </button>
                        </div>
                    `;

                    const handleOpenCrop = (ev) => {
                        ev.preventDefault();
                        ev.stopPropagation();
                        openUniversalImageCropperModal(file, (croppedFile) => {
                            selectedFiles[idx] = croppedFile;
                            syncInputFiles();
                            renderThumbnails();
                        });
                    };

                    card.querySelector('[data-crop-thumb-idx]')?.addEventListener('click', handleOpenCrop);
                    card.querySelector('[data-crop-thumb-btn]')?.addEventListener('click', handleOpenCrop);

                    card.querySelector('[data-remove-thumb-idx]')?.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        ev.stopPropagation();
                        selectedFiles.splice(idx, 1);
                        syncInputFiles();
                        renderThumbnails();
                    });

                    previewGrid.appendChild(card);
                });
            }

            function handleNewFiles(fileList) {
                const incoming = Array.from(fileList || []).filter((f) => f.type.startsWith('image/'));
                if (incoming.length === 0) return;

                const isMultiple = fileInput.hasAttribute('multiple');
                if (isMultiple) {
                    selectedFiles = [...selectedFiles, ...incoming].slice(0, maxFiles);
                } else {
                    selectedFiles = [incoming[0]];
                }
                syncInputFiles();
                renderThumbnails();
            }

            fileInput.addEventListener('change', () => {
                selectedFiles = Array.from(fileInput.files || []).filter((f) => f.type.startsWith('image/'));
                renderThumbnails();
            });

            if (dropzone) {
                ['dragenter', 'dragover'].forEach((evt) => {
                    dropzone.addEventListener(evt, (e) => {
                        e.preventDefault();
                        dropzone.classList.add('border-blue-500', 'bg-blue-50/40', 'dark:bg-blue-950/20');
                    });
                });
                ['dragleave', 'drop'].forEach((evt) => {
                    dropzone.addEventListener(evt, (e) => {
                        e.preventDefault();
                        dropzone.classList.remove('border-blue-500', 'bg-blue-50/40', 'dark:bg-blue-950/20');
                    });
                });
                dropzone.addEventListener('drop', (e) => {
                    if (e.dataTransfer?.files?.length) {
                        handleNewFiles(e.dataTransfer.files);
                    }
                });
            }

            clearBtn?.addEventListener('click', () => {
                selectedFiles = [];
                fileInput.value = '';
                renderThumbnails();
            });

            removeExistingBtn?.addEventListener('click', () => {
                if (removeFlagInput) removeFlagInput.value = '1';
                existingWrap?.classList.add('hidden');
                window.toast?.info('Foto saat ini ditandai untuk dihapus saat disimpan.');
            });
        });
    }

    window.initReusableMediaUploaders = initReusableMediaUploaders;
    initReusableMediaUploaders(document);

    // =========================================================================
    // =========================================================================
    // 12. NATIVE CONTENT-PUSHING PULL-TO-REFRESH ON ANY PAGE (SWIPE DOWN & RELEASE)
    // =========================================================================
    function initMobilePullToRefresh() {
        const ptrSlot = document.getElementById('nativePtrSlot');
        const ptrSpinner = document.getElementById('nativePtrSpinner');
        const ptrLabel = document.getElementById('nativePtrLabel');
        const mainContent = document.getElementById('appMainContent');
        if (!ptrSlot) return;

        let startY = 0;
        let startX = 0;
        let pullDistance = 0;
        let pulling = false;
        const PULL_THRESHOLD = 68;

        document.addEventListener('touchstart', (e) => {
            if (window.innerWidth >= 1024 || e.touches.length !== 1) return;
            if (window.scrollY > 2) return;
            if (document.body.classList.contains('scanner-fullscreen-active')) return;
            if (document.querySelector('.modal-container.flex') || (notifDropdown && !notifDropdown.classList.contains('hidden'))) return;

            startY = e.touches[0].clientY;
            startX = e.touches[0].clientX;
            pullDistance = 0;
            pulling = true;
            ptrSlot.style.transition = 'none';
            if (mainContent) mainContent.style.transition = 'none';
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!pulling || e.touches.length !== 1) return;
            if (window.scrollY > 2) {
                pulling = false;
                ptrSlot.style.height = '0px';
                ptrSlot.style.opacity = '0';
                ptrSlot.classList.remove('is-active');
                return;
            }

            const dy = e.touches[0].clientY - startY;
            const dx = e.touches[0].clientX - startX;

            if (dy <= 8 || Math.abs(dx) > Math.abs(dy)) {
                return;
            }

            pullDistance = Math.min(115, Math.round(dy * 0.58));
            if (pullDistance > 10) {
                ptrSlot.classList.add('is-active');
                ptrSlot.style.height = `${pullDistance}px`;
                ptrSlot.style.opacity = String(Math.min(1, pullDistance / 46));

                const ready = pullDistance >= PULL_THRESHOLD;
                if (ptrLabel) {
                    ptrLabel.textContent = ready
                        ? 'Lepaskan untuk memuat ulang halaman...'
                        : 'Tarik ke bawah untuk memuat ulang';
                }
                if (ptrSpinner && !ptrSpinner.classList.contains('is-spinning')) {
                    ptrSpinner.style.transform = `rotate(${Math.round(pullDistance * 3.4)}deg)`;
                }
            }
        }, { passive: true });

        const finishPull = () => {
            if (!pulling) return;
            pulling = false;

            ptrSlot.style.transition = 'height 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 220ms ease';

            if (pullDistance >= PULL_THRESHOLD) {
                ptrSlot.style.height = '62px';
                ptrSlot.style.opacity = '1';
                if (ptrSpinner) {
                    ptrSpinner.style.transform = '';
                    ptrSpinner.classList.add('is-spinning');
                }
                if (ptrLabel) {
                    ptrLabel.textContent = 'Memuat ulang halaman...';
                }
                setTimeout(() => {
                    window.location.reload();
                }, 240);
            } else {
                ptrSlot.style.height = '0px';
                ptrSlot.style.opacity = '0';
                ptrSlot.classList.remove('is-active');
                if (ptrSpinner) {
                    ptrSpinner.classList.remove('is-spinning');
                    ptrSpinner.style.transform = '';
                }
            }
            pullDistance = 0;
        };

        document.addEventListener('touchend', finishPull, { passive: true });
        document.addEventListener('touchcancel', finishPull, { passive: true });
    }

    initMobilePullToRefresh();

    // =========================================================================
    // 13. MOBILE / TABLET PWA HOME SCREEN 1-CLICK INSTALL + CAMERA, LOCATION & DEEPLINK PERMISSIONS
    // =========================================================================
    function initMobilePwaInstallAndPermissions() {
        let deferredInstallPrompt = null;
        const bannerEl = document.getElementById('mobilePwaInstallPromptModal');
        const oneClickInstallBtn = document.getElementById('pwaOneClickInstallBtn');
        const iosGuideBox = document.getElementById('pwaManualInstallGuideBox');
        const toggleManualGuideBtn = document.getElementById('pwaToggleManualGuideBtn');
        const openPwaModalBtn = document.getElementById('openPwaInstallModalBtn');
        const requestPermsBtn = document.getElementById('pwaRequestPermissionsBtn');
        const dismissPwaBtn = document.getElementById('pwaDismissInstallBtn');
        const permCameraStatus = document.getElementById('pwaPermCameraBadge');
        const permGeoStatus = document.getElementById('pwaPermLocationBadge');
        const permDeeplinkStatus = document.getElementById('pwaPermDeeplinkBadge');

        const isStandalone = window.matchMedia?.('(display-mode: standalone)')?.matches || window.navigator.standalone === true;
        const ua = navigator.userAgent || '';
        const isMobileOrTablet = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Tablet/i.test(ua) || window.innerWidth <= 1024;

        // Register ServiceWorker for PWA standalone + Deep Link Notification Click routing
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data?.type === 'NAVIGATE_DEEP_LINK' && event.data?.url) {
                    window.location.href = event.data.url;
                }
            });
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            if (oneClickInstallBtn) {
                oneClickInstallBtn.classList.remove('hidden');
            }
            if (iosGuideBox) {
                iosGuideBox.classList.add('hidden');
            }
        });

        async function requestDeviceCapabilities(showFeedback = true) {
            // 1. Camera Permission (for Barcode Scanner & Asset Photo)
            if (navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function') {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    stream.getTracks().forEach((t) => t.stop());
                    if (permCameraStatus) {
                        permCameraStatus.textContent = 'Kamera: Aktif';
                        permCameraStatus.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-300';
                    }
                } catch (e) {
                    if (permCameraStatus) {
                        permCameraStatus.textContent = 'Siap Saat Scan';
                    }
                }
            }

            // 2. Geolocation Permission (for Asset Room & Mutation Audit Location)
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        try {
                            sessionStorage.setItem('inv_last_geo', JSON.stringify({
                                lat: pos.coords.latitude,
                                lng: pos.coords.longitude,
                                ts: Date.now(),
                            }));
                        } catch (e) {}
                        if (permGeoStatus) {
                            permGeoStatus.textContent = 'Lokasi: Aktif';
                            permGeoStatus.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-300';
                        }
                    },
                    () => {
                        if (permGeoStatus) {
                            permGeoStatus.textContent = 'Lokasi: Opsional';
                        }
                    },
                    { timeout: 6000, maximumAge: 60000 }
                );
            }

            // 3. Push Notification & Deep Link Protocol Handler Readiness
            if ('Notification' in window && Notification.permission === 'default') {
                try {
                    await Notification.requestPermission();
                } catch (e) {}
            }
            if (typeof navigator.registerProtocolHandler === 'function') {
                try {
                    navigator.registerProtocolHandler('web+inventaris', `${window.location.origin}/s/%s`);
                } catch (e) {}
            }
            if (permDeeplinkStatus) {
                permDeeplinkStatus.textContent = 'DeepLink: Aktif';
                permDeeplinkStatus.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-300';
            }

            if (showFeedback) {
                window.toast?.success('Izin Kamera, Lokasi & DeepLink Siap!', {
                    description: 'Aplikasi siap digunakan layaknya aplikasi native.',
                    duration: 3200,
                });
            }
        }

        window.requestInventoryDevicePermissions = requestDeviceCapabilities;

        const openPwaModal = () => {
            if (!bannerEl) return;
            openModalById('mobilePwaInstallPromptModal');
        };

        const closePwaModal = () => {
            if (!bannerEl) return;
            closeModalEl(bannerEl);
        };

        openPwaModalBtn?.addEventListener('click', () => {
            const mobileMenuSheet = document.getElementById('mobileAppMenuSheet');
            if (mobileMenuSheet) closeModalEl(mobileMenuSheet, true);
            openPwaModal();
        });

        toggleManualGuideBtn?.addEventListener('click', () => {
            iosGuideBox?.classList.toggle('hidden');
        });

        const lanHelperBox = document.getElementById('pwaLanHttpsHelperBox');
        const lanIpLabel = document.getElementById('pwaLanIpLabel');
        const switchToLanHttpsBtn = document.getElementById('pwaSwitchToLanHttpsBtn');
        const lanProtocolBadge = document.getElementById('pwaLanProtocolBadge');

        const host = window.location.hostname || '';
        const isLocalhost = host === 'localhost' || host === '127.0.0.1' || host === '[::1]';
        const isPrivateLanHost = !isLocalhost && (
            /^(192\.168\.|10\.|172\.(1[6-9]|2\d|3[0-1])\.)/.test(host) ||
            host.endsWith('.local')
        );
        const isInsecureContext = window.isSecureContext === false || (window.location.protocol === 'http:' && !isLocalhost);

        if (lanHelperBox && (isPrivateLanHost || isInsecureContext)) {
            lanHelperBox.classList.remove('hidden');
            const httpsPort = 8443;
            const targetHttpsUrl = `https://${host}:${httpsPort}${window.location.pathname}${window.location.search}`;
            if (lanIpLabel) lanIpLabel.textContent = host;
            if (switchToLanHttpsBtn) {
                if (window.location.protocol === 'https:') {
                    switchToLanHttpsBtn.href = '#';
                    switchToLanHttpsBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>HTTPS LAN Aktif</span>';
                    switchToLanHttpsBtn.classList.replace('bg-indigo-600', 'bg-emerald-600');
                    if (lanProtocolBadge) {
                        lanProtocolBadge.textContent = 'HTTPS AKTIF';
                        lanProtocolBadge.className = 'px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
                    }
                } else {
                    switchToLanHttpsBtn.href = targetHttpsUrl;
                }
            }

            // Also fetch dynamic LAN HTTPS metadata from backend if available
            fetch('/api/lan-https-info', { headers: { Accept: 'application/json' } })
                .then((r) => (r.ok ? r.json() : null))
                .then((info) => {
                    if (info && info.https_url && window.location.protocol !== 'https:' && switchToLanHttpsBtn) {
                        switchToLanHttpsBtn.href = `${info.https_url}${window.location.pathname}${window.location.search}`;
                    }
                })
                .catch(() => {});
        }

        oneClickInstallBtn?.addEventListener('click', async () => {
            await requestDeviceCapabilities(false);
            if (deferredInstallPrompt && typeof deferredInstallPrompt.prompt === 'function') {
                deferredInstallPrompt.prompt();
                const choice = await deferredInstallPrompt.userChoice;
                deferredInstallPrompt = null;
                if (choice?.outcome === 'accepted') {
                    localStorage.setItem('inv_pwa_installed', '1');
                    closePwaModal();
                    window.toast?.success('Aplikasi berhasil diinstal ke Home Screen!');
                }
            } else if (isInsecureContext && switchToLanHttpsBtn && switchToLanHttpsBtn.href && switchToLanHttpsBtn.href !== '#') {
                lanHelperBox?.classList.remove('hidden');
                window.toast?.warning('Membuka jalur HTTPS LAN (:8443) agar Chrome mengizinkan instalasi PWA...', {
                    duration: 3500,
                });
                setTimeout(() => {
                    window.location.href = switchToLanHttpsBtn.href;
                }, 900);
            } else if (iosGuideBox) {
                iosGuideBox.classList.remove('hidden');
                window.toast?.info('Ikuti panduan di bawah untuk menambahkan ke Home Screen perangkat Anda.');
            }
        });

        requestPermsBtn?.addEventListener('click', async () => {
            await requestDeviceCapabilities(true);
        });

        document.querySelectorAll('[data-pwa-close]').forEach((btn) => {
            btn.addEventListener('click', () => {
                try {
                    sessionStorage.setItem('inv_pwa_prompt_dismissed', '1');
                } catch (e) {}
                closePwaModal();
            });
        });

        dismissPwaBtn?.addEventListener('click', () => {
            try {
                sessionStorage.setItem('inv_pwa_prompt_dismissed', '1');
            } catch (e) {}
            closePwaModal();
        });

        if (bannerEl && isMobileOrTablet && !isStandalone) {
            const dismissed = sessionStorage.getItem('inv_pwa_prompt_dismissed') === '1';
            const installed = localStorage.getItem('inv_pwa_installed') === '1';
            if (!dismissed && !installed) {
                setTimeout(() => {
                    openPwaModal();
                }, 1400);
            }
        }
    }

    initMobilePwaInstallAndPermissions();

    liveChannel?.addEventListener('message', (event) => {
        if (event.data) {
            applyLiveStatePayload(event.data, true);
        }
    });

    setTimeout(pollLiveState, 800);
    setInterval(pollLiveState, 5000);
    setInterval(listenWebhookNotificationStream, 2000);
});
