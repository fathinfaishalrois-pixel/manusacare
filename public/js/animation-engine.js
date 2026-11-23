/* ==========================================================================
   MANUSACARE — progressive animation runtime
   GSAP: scroll/reveal timelines | Motion: micro-interactions | Lenis: scrolling
   Libraries are loaded as pinned ESM modules. Existing UI remains functional
   when a CDN is unavailable, JavaScript is disabled, or reduced motion is set.
   ========================================================================== */

const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
const coarsePointer = window.matchMedia?.('(pointer: coarse)').matches ?? false;

if (!prefersReducedMotion) {
  const loadAnimationLibraries = async () => {
    try {
      const [gsapModule, scrollTriggerModule, motionModule, lenisModule] = await Promise.all([
        import('https://cdn.jsdelivr.net/npm/gsap@3.14.2/+esm'),
        import('https://cdn.jsdelivr.net/npm/gsap@3.14.2/ScrollTrigger/+esm'),
        import('https://cdn.jsdelivr.net/npm/motion@12.23.24/+esm'),
        import('https://cdn.jsdelivr.net/npm/lenis@1.3.17/+esm'),
      ]);

      const gsap = gsapModule.gsap || gsapModule.default;
      const ScrollTrigger = scrollTriggerModule.ScrollTrigger || scrollTriggerModule.default;
      const { animate, inView } = motionModule;
      const Lenis = lenisModule.default || lenisModule.Lenis;

      if (!gsap || !animate || !inView || !Lenis) {
        throw new Error('Salah satu modul animasi tidak menyediakan API yang diperlukan.');
      }

      window.MANUSACARE = window.MANUSACARE || {};
      window.MANUSACARE.animations = { gsap, animate, Lenis };

      // Lenis is intentionally disabled on coarse-pointer devices to keep
      // native touch momentum and reduce work on entry-level phones.
      if (!coarsePointer && !document.documentElement.dataset.nativeScroll) {
        const lenis = new Lenis({
          autoRaf: true,
          duration: 0.85,
          smoothWheel: true,
          syncTouch: false,
          wheelMultiplier: 0.9,
          touchMultiplier: 1,
          anchors: {
            offset: -84,
            onComplete: () => window.MANUSACARE?.sound?.play?.('navigation'),
          },
        });

        window.MANUSACARE.lenis = lenis;
        document.documentElement.classList.add('has-lenis');

        // Keep browser history, focus and keyboard navigation native.
        document.addEventListener('visibilitychange', () => {
          if (document.hidden) lenis.stop();
          else lenis.start();
        }, { passive: true });
      }

      // GSAP is opt-in: add data-gsap-reveal to a section or component.
      // ScrollTrigger is registered only when the module exposes it.
      if (ScrollTrigger) {
        gsap.registerPlugin(ScrollTrigger);
      }

      const revealTargets = document.querySelectorAll('[data-gsap-reveal]');
      if (revealTargets.length) {
        gsap.set(revealTargets, { autoAlpha: 0, y: 14 });
        revealTargets.forEach((element) => {
          gsap.to(element, {
            autoAlpha: 1,
            y: 0,
            duration: 0.55,
            ease: 'power2.out',
            clearProps: 'transform',
            scrollTrigger: ScrollTrigger ? {
              trigger: element,
              start: 'top 90%',
              once: true,
            } : undefined,
          });
        });
      }

      // Motion handles small, opt-in interactions. No global animation of
      // every button/card, so existing UI transitions remain authoritative.
      document.querySelectorAll('[data-motion-hover]').forEach((element) => {
        if (coarsePointer) return;
        let controls = null;
        element.addEventListener('pointerenter', () => {
          controls?.stop?.();
          controls = animate(element, { y: -2, scale: 1.012 }, {
            duration: 0.18,
            easing: [0.22, 0.8, 0.24, 1],
          });
        }, { passive: true });
        element.addEventListener('pointerleave', () => {
          controls?.stop?.();
          controls = animate(element, { y: 0, scale: 1 }, {
            duration: 0.2,
            easing: [0.22, 0.8, 0.24, 1],
          });
        }, { passive: true });
      });

      // Motion's inView is used for lightweight one-time entrance states.
      document.querySelectorAll('[data-motion-inview]').forEach((element) => {
        inView(element, () => {
          animate(element, { opacity: [0, 1], y: [8, 0] }, {
            duration: 0.38,
            easing: 'ease-out',
          });
        }, { once: true, amount: 0.12 });
      });

      document.documentElement.classList.add('animation-libraries-ready');
      window.dispatchEvent(new CustomEvent('manusacare:animations-ready'));
    } catch (error) {
      // The core application must never depend on third-party animation CDNs.
      console.warn('[MANUSACARE] Animation libraries unavailable; native UI fallback active.', error);
    }
  };

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(() => loadAnimationLibraries(), { timeout: 1800 });
  } else {
    window.setTimeout(loadAnimationLibraries, 250);
  }
}
