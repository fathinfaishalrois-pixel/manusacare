/* ==========================================================================
   MANUSACARE — frontend interaction system
   One namespace, one initialization, no global variables.
   ========================================================================== */

(() => {
  'use strict';

  const MC = (window.MANUSACARE = window.MANUSACARE || {});
  if (MC.__booted) return;
  MC.__booted = true;
  MC.version = '2.0.0';

  const ready = (fn) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  };

  const reducedMotionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
  const canHoverQuery = window.matchMedia ? window.matchMedia('(hover: hover) and (pointer: fine)') : null;

  const state = {
    reducedMotion: Boolean(reducedMotionQuery && reducedMotionQuery.matches),
    canHover: Boolean(canHoverQuery && canHoverQuery.matches),
    pageTransitionTimer: 0,
  };

  const store = {
    get(key, fallback = null) {
      try {
        const value = window.localStorage.getItem(key);
        return value === null ? fallback : value;
      } catch (_) {
        return fallback;
      }
    },
    set(key, value) {
      try {
        window.localStorage.setItem(key, value);
      } catch (_) {
        /* Preferences are a progressive enhancement. */
      }
    },
  };

  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
  ].join(',');

  const focusTrap = (container) => {
    const onKeydown = (event) => {
      if (event.key !== 'Tab') return;
      const nodes = Array.from(container.querySelectorAll(focusableSelector)).filter((node) => {
        return node.offsetParent !== null || node === document.activeElement;
      });
      if (!nodes.length) return;
      const first = nodes[0];
      const last = nodes[nodes.length - 1];
      if (event.shiftKey && (document.activeElement === first || !container.contains(document.activeElement))) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && (document.activeElement === last || !container.contains(document.activeElement))) {
        event.preventDefault();
        first.focus();
      }
    };
    container.addEventListener('keydown', onKeydown);
    return () => container.removeEventListener('keydown', onKeydown);
  };

  /* ------------------------------------------------------------------------
     Sound engine — local synthesis, opt-in, cooldown protected
     ------------------------------------------------------------------------ */

  const createSound = () => {
    const STORAGE_ENABLED = 'manusacare:sound';
    const STORAGE_VOLUME = 'manusacare:sound-volume';

    let context = null;
    let master = null;
    let enabled = store.get(STORAGE_ENABLED, 'on') === 'on';
    let unlocked = false;
    let resumePending = false;
    let volume = clamp(parseFloat(store.get(STORAGE_VOLUME, '0.35')) || 0.35, 0, 1);
    let voices = 0;
    let lastAny = 0;
    let queued = null;
    const lastPlayed = new Map();

    const presets = {
      click: [
        { f: 760, d: 0.035, g: 0.05, t: 'sine' },
      ],
      navigation: [
        { f: 430, d: 0.055, g: 0.036, t: 'sine' },
        { f: 645, d: 0.05, g: 0.026, t: 'triangle', delay: 0.04 },
      ],
      open: [
        { f: 320, d: 0.06, g: 0.032, t: 'sine' },
        { f: 480, d: 0.065, g: 0.024, t: 'sine', delay: 0.045 },
      ],
      close: [
        { f: 470, d: 0.055, g: 0.03, t: 'sine' },
        { f: 310, d: 0.075, g: 0.024, t: 'sine', delay: 0.045 },
      ],
      select: [
        { f: 620, d: 0.045, g: 0.038, t: 'sine' },
        { f: 930, d: 0.065, g: 0.026, t: 'triangle', delay: 0.04 },
      ],
      toggle: [
        { f: 560, d: 0.032, g: 0.032, t: 'sine' },
        { f: 700, d: 0.036, g: 0.022, t: 'sine', delay: 0.026 },
      ],
      success: [
        { f: 523.25, d: 0.075, g: 0.04, t: 'sine' },
        { f: 659.25, d: 0.075, g: 0.034, t: 'sine', delay: 0.06 },
        { f: 783.99, d: 0.11, g: 0.028, t: 'triangle', delay: 0.12 },
      ],
      error: [
        { f: 225, d: 0.11, g: 0.045, t: 'triangle' },
        { f: 174.61, d: 0.15, g: 0.034, t: 'sine', delay: 0.08 },
      ],
      notify: [
        { f: 880, d: 0.06, g: 0.034, t: 'sine' },
        { f: 1174.66, d: 0.09, g: 0.024, t: 'sine', delay: 0.055 },
      ],
      submit: [
        { f: 392, d: 0.055, g: 0.03, t: 'sine' },
        { f: 523.25, d: 0.075, g: 0.024, t: 'triangle', delay: 0.055 },
      ],
      upload: [
        { f: 500, d: 0.045, g: 0.027, t: 'sine' },
        { f: 700, d: 0.055, g: 0.022, t: 'sine', delay: 0.038 },
      ],
      modalOpen: [
        { f: 290, d: 0.06, g: 0.03, t: 'sine' },
        { f: 430, d: 0.08, g: 0.022, t: 'sine', delay: 0.04 },
      ],
      modalClose: [
        { f: 430, d: 0.055, g: 0.026, t: 'sine' },
        { f: 285, d: 0.08, g: 0.02, t: 'sine', delay: 0.04 },
      ],
    };

    const cooldowns = {
      click: 55,
      navigation: 110,
      open: 160,
      close: 140,
      select: 110,
      toggle: 90,
      success: 650,
      error: 650,
      notify: 500,
      submit: 500,
      upload: 220,
      modalOpen: 220,
      modalClose: 220,
    };

    const ensureContext = () => {
      if (!enabled) return null;
      try {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return null;
        if (!context) {
          context = new AudioContextClass();
          master = context.createGain();
          master.gain.value = 0.9;
          master.connect(context.destination);
        }
        if (context.state === 'suspended' && !resumePending) {
          resumePending = true;
          context.resume().then(() => {
            resumePending = false;
            if (queued && context && context.state === 'running') {
              const next = queued;
              queued = null;
              play(next, { force: true });
            }
          }).catch(() => {
            resumePending = false;
          });
        }
        return context;
      } catch (_) {
        return null;
      }
    };

    const unlock = () => {
      if (!enabled) return;
      unlocked = true;
      const ctx = ensureContext();
      if (queued && ctx && ctx.state === 'running') {
        const next = queued;
        queued = null;
        window.setTimeout(() => play(next, { force: true }), 0);
      }
    };

    const play = (name, options = {}) => {
      if (!enabled) return;
      if (!unlocked) {
        queued = name;
        return;
      }
      const preset = presets[name];
      if (!preset) return;
      const nowMs = Date.now();
      const cooldown = cooldowns[name] || 90;
      if (!options.force && nowMs - (lastPlayed.get(name) || 0) < cooldown) return;
      if (nowMs - lastAny < 28) return;
      if (voices > 14) return;
      const ctx = ensureContext();
      if (!ctx || ctx.state !== 'running') {
        if (!options.queued) queued = name;
        return;
      }
      lastPlayed.set(name, nowMs);
      lastAny = nowMs;
      try {
        const start = ctx.currentTime + 0.008;
        const level = clamp(volume, 0, 1) * 0.24;
        if (level <= 0.0005) return;
        preset.forEach((note) => {
          const begin = start + (note.delay || 0);
          const osc = ctx.createOscillator();
          const gain = ctx.createGain();
          osc.type = note.t || 'sine';
          osc.frequency.setValueAtTime(note.f, begin);
          osc.frequency.exponentialRampToValueAtTime(Math.max(40, note.f * 0.96), begin + note.d);
          gain.gain.setValueAtTime(0.0001, begin);
          gain.gain.exponentialRampToValueAtTime(Math.max(0.0001, note.g * level), begin + 0.006);
          gain.gain.exponentialRampToValueAtTime(0.0001, begin + note.d);
          osc.connect(gain);
          gain.connect(master);
          voices += 1;
          osc.onended = () => {
            voices = Math.max(0, voices - 1);
            try {
              osc.disconnect();
              gain.disconnect();
            } catch (_) {
              /* already disconnected */
            }
          };
          osc.start(begin);
          osc.stop(begin + note.d + 0.02);
        });
      } catch (_) {
        /* Sound is an enhancement only. */
      }
    };

    const setEnabled = (next) => {
      enabled = Boolean(next);
      store.set(STORAGE_ENABLED, enabled ? 'on' : 'off');
      if (enabled) {
        // This method is called by the sound toggle's user gesture.
        unlocked = true;
        ensureContext();
        play('success', { force: true });
      } else {
        queued = null;
      }
      return enabled;
    };

    const setVolume = (next) => {
      volume = clamp(Number(next) || 0, 0, 1);
      store.set(STORAGE_VOLUME, String(volume));
      return volume;
    };

    return {
      play,
      unlock,
      setEnabled,
      setVolume,
      isEnabled: () => enabled,
      isSupported: () => Boolean(window.AudioContext || window.webkitAudioContext),
      getVolume: () => volume,
    };
  };

  MC.sound = MC.sound || createSound();

  /* ------------------------------------------------------------------------
     Motion — page entry, one observer, reveal states, nav state
     ------------------------------------------------------------------------ */

  const createMotion = () => {
    let revealObserver = null;
    let revealNodes = [];

    const reveal = () => {
      const nodes = Array.from(document.querySelectorAll('[data-reveal]'));
      revealNodes = nodes;
      if (!nodes.length) return;

      if (state.reducedMotion || !('IntersectionObserver' in window)) {
        nodes.forEach((node) => {
          node.classList.remove('is-pending');
          node.classList.add('is-revealed');
        });
        return;
      }

      nodes.forEach((node, index) => {
        const group = node.closest('[data-reveal-group]');
        if (group) {
          const peers = Array.from(group.querySelectorAll('[data-reveal]'));
          const peerIndex = peers.indexOf(node);
          node.style.setProperty('--reveal-delay', `${Math.min(Math.max(peerIndex, 0) * 55, 220)}ms`);
        } else if (node.dataset.revealDelay) {
          node.style.setProperty('--reveal-delay', `${Math.min(Number(node.dataset.revealDelay) || 0, 300)}ms`);
        }

        const rect = node.getBoundingClientRect();
        if (rect.top > window.innerHeight * 0.92) {
          node.classList.add('is-pending');
        } else {
          node.style.setProperty('--reveal-delay', '0ms');
          node.classList.add('is-revealed');
        }
        void index;
      });

      revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const node = entry.target;
          node.classList.remove('is-pending');
          node.classList.add('is-revealed');
          revealObserver.unobserve(node);
        });
      }, {
        rootMargin: '0px 0px -7% 0px',
        threshold: 0.08,
      });

      nodes.forEach((node) => {
        if (node.classList.contains('is-pending')) revealObserver.observe(node);
      });
    };

    const navState = () => {
      const header = document.querySelector('[data-site-nav]');
      if (!header) return;
      const nav = header.querySelector('nav');
      let indicator = null;
      let activeLink = null;
      let raf = 0;
      if (nav) {
        indicator = nav.querySelector('[data-nav-indicator]');
        if (!indicator) {
          indicator = document.createElement('span');
          indicator.className = 'nav-liquid-indicator';
          indicator.setAttribute('data-nav-indicator', '');
          indicator.setAttribute('aria-hidden', 'true');
          nav.prepend(indicator);
        }
        nav.classList.add('has-indicator');
        activeLink = nav.querySelector('a[aria-current="page"]') || nav.querySelector('a');
        nav.addEventListener('click', (event) => {
          const target = event.target.closest('a[href]');
          if (!target || !nav.contains(target)) return;
          activeLink = target;
          requestUpdate();
        });
        nav.addEventListener('focusin', (event) => {
          const target = event.target.closest('a[href]');
          if (target && nav.contains(target)) { activeLink = target; requestUpdate(); }
        });
        nav.addEventListener('focusout', () => {
          window.setTimeout(() => {
            if (nav.contains(document.activeElement)) return;
            activeLink = nav.querySelector('a[aria-current="page"]') || nav.querySelector('a');
            requestUpdate();
          }, 0);
        });
      }
      const moveIndicator = () => {
        if (!nav || !indicator || !activeLink || window.matchMedia('(max-width: 960px)').matches) return;
        const navRect = nav.getBoundingClientRect();
        const linkRect = activeLink.getBoundingClientRect();
        if (!navRect.width || !linkRect.width) return;
        const left = linkRect.left - navRect.left;
        indicator.style.width = `${linkRect.width}px`;
        indicator.style.transform = `translate3d(${left}px,0,0)`;
      };
      const update = () => {
        raf = 0;
        header.classList.toggle('is-scrolled', window.scrollY > 24);
        moveIndicator();
      };
      const requestUpdate = () => {
        if (!raf) raf = window.requestAnimationFrame(update);
      };
      window.addEventListener('scroll', requestUpdate, { passive: true });
      window.addEventListener('resize', requestUpdate, { passive: true });
      window.addEventListener('pageshow', requestUpdate);
      if (document.fonts && document.fonts.ready) document.fonts.ready.then(requestUpdate);
      requestUpdate();
    };

    const resetPageState = () => {
      document.body.classList.remove('is-leaving');
      window.clearTimeout(state.pageTransitionTimer);
    };

    return {
      reveal,
      navState,
      resetPageState,
      refreshReveal() {
        if (revealObserver) revealObserver.disconnect();
        reveal();
      },
      get nodes() {
        return revealNodes;
      },
    };
  };

  MC.motion = MC.motion || createMotion();

  /* ------------------------------------------------------------------------
     UI — mobile navigation, notices, password visibility, tabs, modals
     ------------------------------------------------------------------------ */

  const createUI = () => {
    const nav = {
      button: null,
      panel: null,
      scrim: null,
      isOpen: false,
      mq: window.matchMedia ? window.matchMedia('(max-width: 960px)') : null,
    };

    const isMobile = () => (nav.mq ? nav.mq.matches : window.innerWidth <= 960);

    const setPanelInert = (inert) => {
      if (!nav.panel) return;
      if (inert) nav.panel.setAttribute('inert', '');
      else nav.panel.removeAttribute('inert');
    };

    const updateNav = (open, returnFocus = false) => {
      if (!nav.button || !nav.panel) return;
      nav.isOpen = Boolean(open);
      nav.button.setAttribute('aria-expanded', String(nav.isOpen));
      nav.button.setAttribute('aria-label', nav.isOpen ? 'Tutup navigasi' : 'Buka navigasi');
      nav.panel.classList.toggle('is-open', nav.isOpen);
      if (nav.scrim) nav.scrim.classList.toggle('is-open', nav.isOpen);
      document.body.classList.toggle('nav-open', nav.isOpen && isMobile());
      setPanelInert(isMobile() ? !nav.isOpen : false);
      if (nav.isOpen) {
        const first = nav.panel.querySelector('a[href],button');
        if (first) first.focus({ preventScroll: true });
      } else if (returnFocus) {
        nav.button.focus({ preventScroll: true });
      }
    };

    const closeNav = (returnFocus = false) => {
      if (nav.isOpen) updateNav(false, returnFocus);
    };

    const initNav = () => {
      nav.button = document.querySelector('[data-menu-toggle]');
      nav.panel = document.querySelector('[data-nav-panel]');
      nav.scrim = document.querySelector('[data-nav-scrim]');
      if (!nav.button || !nav.panel) return;

      if (!isMobile()) setPanelInert(false);
      else setPanelInert(true);

      nav.button.addEventListener('click', () => {
        updateNav(!nav.isOpen, false);
        MC.sound.play(nav.isOpen ? 'open' : 'close');
      });

      nav.panel.addEventListener('click', (event) => {
        if (event.target.closest('a[href]')) closeNav(false);
      });

      if (nav.scrim) {
        nav.scrim.addEventListener('click', () => closeNav(true));
      }

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && nav.isOpen) {
          closeNav(true);
        }
      });

      const onViewportChange = () => {
        if (!isMobile() && nav.isOpen) closeNav(false);
        if (isMobile()) setPanelInert(!nav.isOpen);
        else setPanelInert(false);
      };

      if (nav.mq) {
        if (nav.mq.addEventListener) nav.mq.addEventListener('change', onViewportChange);
        else if (nav.mq.addListener) nav.mq.addListener(onViewportChange);
      } else {
        window.addEventListener('resize', onViewportChange, { passive: true });
      }
    };

    const initNotices = () => {
      document.querySelectorAll('[data-notice]').forEach((notice) => {
        const close = notice.querySelector('[data-notice-close]');
        if (close) {
          close.addEventListener('click', () => {
            notice.classList.add('is-dismissing');
            window.setTimeout(() => notice.remove(), 210);
          });
        }

        const auto = Number(notice.dataset.autodismiss || 0);
        if (auto > 0) {
          let timer = window.setTimeout(dismiss, auto);
          const pause = () => window.clearTimeout(timer);
          const resume = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(dismiss, 2600);
          };
          const dismiss = () => {
            if (!document.body.contains(notice)) return;
            notice.classList.add('is-dismissing');
            window.setTimeout(() => notice.remove(), 210);
          };
          notice.addEventListener('mouseenter', pause);
          notice.addEventListener('focusin', pause);
          notice.addEventListener('mouseleave', resume);
          notice.addEventListener('focusout', (event) => {
            if (!notice.contains(event.relatedTarget)) resume();
          });
        }
      });
    };

    const initPasswordToggles = () => {
      document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;
        button.addEventListener('click', () => {
          const show = input.type === 'password';
          input.type = show ? 'text' : 'password';
          button.setAttribute('aria-pressed', String(show));
          button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
          MC.sound.play('toggle');
        });
      });
    };

    const initTabs = () => {
      document.querySelectorAll('[data-tabs]').forEach((group) => {
        const tabs = Array.from(group.querySelectorAll('[role="tab"][data-tab]'));
        const panels = Array.from(group.querySelectorAll('[role="tabpanel"][data-tab-panel]'));
        if (!tabs.length || !panels.length) return;

        const select = (tab, focus = false) => {
          const id = tab.dataset.tab;
          tabs.forEach((item) => {
            const active = item === tab;
            item.setAttribute('aria-selected', String(active));
            item.setAttribute('tabindex', active ? '0' : '-1');
            item.classList.toggle('is-active', active);
          });
          panels.forEach((panel) => {
            const active = panel.dataset.tabPanel === id;
            panel.hidden = !active;
            panel.classList.toggle('is-active', active);
          });
          if (focus) tab.focus();
        };

        tabs.forEach((tab, index) => {
          tab.addEventListener('click', () => {
            select(tab);
            MC.sound.play('toggle');
          });
          tab.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            let next = index;
            if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            select(tabs[next], true);
          });
        });

        const active = tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0];
        select(active);
      });
    };

    const initModals = () => {
      let activeModal = null;
      let releaseTrap = null;
      let previousFocus = null;

      const open = (modal) => {
        if (!modal || activeModal) return;
        activeModal = modal;
        previousFocus = document.activeElement;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        document.body.classList.add('modal-open');
        releaseTrap = focusTrap(modal);
        const target = modal.querySelector('[autofocus],button,a[href],input,select,textarea');
        if (target) target.focus({ preventScroll: true });
        MC.sound.play('modalOpen');
      };

      const close = () => {
        if (!activeModal) return;
        const modal = activeModal;
        activeModal = null;
        if (releaseTrap) releaseTrap();
        releaseTrap = null;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus({ preventScroll: true });
        previousFocus = null;
        MC.sound.play('modalClose');
      };

      document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');
        if (opener) {
          event.preventDefault();
          open(document.getElementById(opener.dataset.modalOpen));
          return;
        }
        if (event.target.closest('[data-modal-close]')) {
          event.preventDefault();
          close();
          return;
        }
        if (activeModal && event.target === activeModal) close();
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && activeModal) close();
      });

      return { open, close };
    };

    return {
      initNav,
      initNotices,
      initPasswordToggles,
      initTabs,
      initModals,
      closeNav,
    };
  };

  MC.ui = MC.ui || createUI();

  /* ------------------------------------------------------------------------
     Forms — previews, validation feedback, loading state, password toggles
     ------------------------------------------------------------------------ */

  const createForms = () => {
    const initConditionalFields = () => {
      document.querySelectorAll('[data-conditional-field]').forEach((container) => {
        const key = container.dataset.conditionalField;
        const select = document.getElementById(key);
        const input = container.querySelector('input');
        if (!select || !input) return;

        const sync = () => {
          const visible = select.value === '__other';
          container.hidden = !visible;
          input.required = visible;
          input.disabled = !visible;
          if (visible) {
            input.setAttribute('aria-required', 'true');
          } else {
            input.removeAttribute('aria-required');
            input.removeAttribute('aria-invalid');
          }
        };

        select.addEventListener('change', () => {
          sync();
          MC.sound.play('select');
          if (select.value === '__other') input.focus();
        });
        sync();
      });
    };

    const initPreviews = () => {
      document.querySelectorAll('[data-file-preview]').forEach((input) => {
        const output = document.querySelector(`[data-preview-output="${input.dataset.filePreview}"]`);
        if (!output) return;
        let urls = [];

        input.addEventListener('change', () => {
          urls.forEach((url) => URL.revokeObjectURL(url));
          urls = [];
          output.replaceChildren();

          Array.from(input.files || []).forEach((file) => {
            const row = document.createElement('div');
            row.className = 'upload-preview-item';

            if (file.type.startsWith('image/')) {
              const image = document.createElement('img');
              image.alt = '';
              const url = URL.createObjectURL(file);
              urls.push(url);
              image.src = url;
              row.append(image);
            } else if (file.type.startsWith('video/')) {
              const video = document.createElement('video');
              video.muted = true;
              video.playsInline = true;
              video.preload = 'metadata';
              const url = URL.createObjectURL(file);
              urls.push(url);
              video.src = url;
              row.append(video);
            } else {
              const icon = document.createElement('span');
              icon.className = 'preview-icon';
              icon.innerHTML = '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h6l5 5v13H5V3h3Z"/><path d="M14 3v5h5"/></svg>';
              row.append(icon);
            }

            const copy = document.createElement('span');
            const name = document.createElement('b');
            name.textContent = file.name;
            const meta = document.createElement('small');
            meta.textContent = `${(file.size / 1048576).toFixed(1)} MB`;
            copy.append(name, meta);
            row.append(copy);
            output.append(row);
          });

          MC.sound.play('upload');
        });
      });
    };

    const initValidationFeedback = () => {
      document.addEventListener('invalid', (event) => {
        const field = event.target;
        if (field instanceof HTMLElement && field.matches('input,select,textarea')) {
          field.setAttribute('aria-invalid', 'true');
        }
      }, true);

      document.addEventListener('input', (event) => {
        const field = event.target;
        if (!(field instanceof HTMLElement) || !field.matches('input,select,textarea')) return;
        if (field.checkValidity && field.checkValidity()) field.removeAttribute('aria-invalid');
      }, true);
    };

    const initSubmitStates = () => {
      document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        MC.sound.play('submit');

        if (!form.checkValidity()) {
          MC.sound.play('error');
          return;
        }

        const button = form.querySelector('button[type="submit"],button:not([type])');
        if (!button || button.disabled || button.dataset.noLoading !== undefined) return;

        window.setTimeout(() => {
          if (!document.body.contains(button)) return;
          button.classList.add('is-loading');
          button.setAttribute('aria-busy', 'true');
          button.dataset.originalLabel = button.textContent.trim();
          button.textContent = 'Memproses…';
          button.disabled = true;
        }, 0);
      });
    };

    return { initConditionalFields, initPreviews, initValidationFeedback, initSubmitStates };
  };

  MC.forms = MC.forms || createForms();

  /* ------------------------------------------------------------------------
     Lightbox — images only, link remains a no-JS fallback
     ------------------------------------------------------------------------ */

  const createLightbox = () => {
    let modal = null;
    let image = null;
    let caption = null;
    let releaseTrap = null;
    let previousFocus = null;

    const ensure = () => {
      if (modal) return;
      modal = document.createElement('div');
      modal.className = 'lightbox';
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-hidden', 'true');
      modal.setAttribute('aria-label', 'Pratinjau dokumentasi');
      modal.innerHTML = [
        '<button class="lightbox-close" type="button" data-lightbox-close aria-label="Tutup pratinjau">',
        '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>',
        '</button>',
        '<div class="lightbox-frame"><img alt=""><div class="lightbox-caption"></div></div>',
      ].join('');
      document.body.append(modal);
      image = modal.querySelector('img');
      caption = modal.querySelector('.lightbox-caption');
      modal.querySelector('[data-lightbox-close]').addEventListener('click', close);
      modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
      });
    };

    const open = (trigger) => {
      ensure();
      const img = trigger.querySelector('img');
      if (!img) return;
      previousFocus = document.activeElement;
      image.src = trigger.href;
      image.alt = img.alt || 'Dokumentasi laporan';
      caption.textContent = img.alt || '';
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      releaseTrap = focusTrap(modal);
      const closeButton = modal.querySelector('[data-lightbox-close]');
      if (closeButton) closeButton.focus({ preventScroll: true });
      MC.sound.play('modalOpen');
    };

    const close = () => {
      if (!modal || !modal.classList.contains('is-open')) return;
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
      if (releaseTrap) releaseTrap();
      releaseTrap = null;
      if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus({ preventScroll: true });
      previousFocus = null;
      MC.sound.play('modalClose');
    };

    const init = () => {
      document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-lightbox]');
        if (!trigger) return;
        event.preventDefault();
        open(trigger);
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
      });
    };

    return { init, close };
  };

  MC.lightbox = MC.lightbox || createLightbox();

  /* ------------------------------------------------------------------------
     Sound wiring — delegated, one listener per interaction family
     ------------------------------------------------------------------------ */

  const initSound = () => {
    const buttons = Array.from(document.querySelectorAll('[data-sound-toggle]'));
    const volumeInputs = Array.from(document.querySelectorAll('[data-sound-volume]'));

    const paint = () => {
      const enabled = MC.sound.isEnabled();
      buttons.forEach((button) => {
        button.setAttribute('aria-pressed', String(enabled));
        button.setAttribute('aria-label', enabled ? 'Matikan efek suara' : 'Aktifkan efek suara');
        button.title = enabled ? 'Efek suara aktif' : 'Efek suara nonaktif';
        button.classList.toggle('is-on', enabled);
      });
      document.querySelectorAll('[data-sound-volume-wrap]').forEach((wrap) => {
        wrap.classList.toggle('is-disabled', !enabled);
      });
      volumeInputs.forEach((input) => {
        if (document.activeElement !== input) input.value = String(Math.round(MC.sound.getVolume() * 100));
      });
    };

    paint();

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const next = MC.sound.setEnabled(!MC.sound.isEnabled());
        paint();
        if (next) MC.sound.play('toggle', { force: true });
      });
    });

    volumeInputs.forEach((input) => {
      input.addEventListener('input', () => {
        MC.sound.setVolume(Number(input.value) / 100);
        paint();
      });
      input.addEventListener('change', () => {
        if (MC.sound.isEnabled()) MC.sound.play('toggle', { force: true });
      });
    });

    // One-time audio unlock after a real user gesture.
    const unlock = () => MC.sound.unlock();
    document.addEventListener('pointerdown', unlock, { once: true, passive: true });
    document.addEventListener('keydown', unlock, { once: true });

    // Clicks
    document.addEventListener('click', (event) => {
      const target = event.target.closest('a[href],button,[role="button"],summary');
      if (!target) return;
      if (target.closest('[data-sound-control]')) return;
      if (target.closest('[data-modal-open],[data-modal-close],[data-lightbox]')) return;
      if (target.matches('label')) return;

      if (target.matches('summary')) {
        MC.sound.play('toggle');
        return;
      }

      const inNav = target.closest('.site-nav nav');
      if (inNav) {
        MC.sound.play('navigation');
        return;
      }

      MC.sound.play('click');
    }, { passive: true });

    // Native select opening (pointer and keyboard)
    document.addEventListener('pointerdown', (event) => {
      if (event.target.matches('select')) MC.sound.play('open');
    }, { passive: true });

    document.addEventListener('keydown', (event) => {
      if (!event.target.matches('select')) return;
      if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) MC.sound.play('open');
    });

    // Selections, toggles and uploads
    document.addEventListener('change', (event) => {
      const target = event.target;
      if (target.matches('input[type="file"]')) {
        MC.sound.play('upload');
      } else if (target.matches('select')) {
        MC.sound.play('select');
      } else if (target.matches('input[type="checkbox"],input[type="radio"]')) {
        MC.sound.play('toggle');
      }
    });

    // Pending flash message sound, played on first gesture if needed.
    const flash = document.querySelector('[data-flash-type]');
    if (flash) {
      const kind = flash.dataset.flashType === 'error' ? 'error' : 'success';
      MC.sound.play(kind);
    }
  };

  /* ------------------------------------------------------------------------
     Page transitions
     ------------------------------------------------------------------------ */

  const initPageTransitions = () => {
    document.addEventListener('click', (event) => {
      if (event.defaultPrevented || event.button !== 0) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

      const link = event.target.closest('a[href]');
      if (!link) return;
      if (link.target && link.target !== '_self') return;
      if (link.hasAttribute('download')) return;
      if (link.closest('[data-lightbox]')) return;
      if (link.dataset.noTransition !== undefined) return;

      let url;
      try {
        url = new URL(link.href, window.location.href);
      } catch (_) {
        return;
      }
      if (!/^https?:$/.test(url.protocol)) return;
      if (url.origin !== window.location.origin) return;
      if (url.pathname === window.location.pathname && url.search === window.location.search) return;
      if (state.reducedMotion) return;

      event.preventDefault();
      document.body.classList.add('is-leaving');
      window.clearTimeout(state.pageTransitionTimer);
      state.pageTransitionTimer = window.setTimeout(() => {
        window.location.assign(url.href);
      }, 145);

      // Safety net: never leave the interface faded if navigation is blocked.
      window.setTimeout(() => document.body.classList.remove('is-leaving'), 1200);
    });

    window.addEventListener('pageshow', () => {
      MC.motion.resetPageState();
      MC.ui.closeNav(false);
    });
  };

  /* ------------------------------------------------------------------------
     Reduced motion + hover preference updates
     ------------------------------------------------------------------------ */

  const initMediaQueries = () => {
    const apply = () => {
      state.reducedMotion = Boolean(reducedMotionQuery && reducedMotionQuery.matches);
      state.canHover = Boolean(canHoverQuery && canHoverQuery.matches);
      if (state.reducedMotion && MC.motion.nodes.length) {
        MC.motion.nodes.forEach((node) => {
          node.classList.remove('is-pending');
          node.classList.add('is-revealed');
        });
      }
    };

    [reducedMotionQuery, canHoverQuery].forEach((query) => {
      if (!query) return;
      if (query.addEventListener) query.addEventListener('change', apply);
      else if (query.addListener) query.addListener(apply);
    });
  };

  /* ------------------------------------------------------------------------
     Boot
     ------------------------------------------------------------------------ */

  ready(() => {
    document.documentElement.classList.add('js-ready');

    MC.ui.initNav();
    MC.ui.initNotices();
    MC.ui.initPasswordToggles();
    MC.ui.initTabs();
    MC.ui.initModals();
    MC.lightbox.init();

    MC.motion.reveal();
    MC.motion.navState();

    MC.forms.initConditionalFields();
    MC.forms.initPreviews();
    MC.forms.initValidationFeedback();
    MC.forms.initSubmitStates();

    initSound();
    initPageTransitions();
    initMediaQueries();
  });
})();
