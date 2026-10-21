/* Darul Shofi landing page interactions — plain JavaScript, no dependencies. */
(() => {
  'use strict';
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const motion = () => reduceMotion.matches ? 'auto' : 'smooth';

  // Mobile navigation.
  const menuButton = document.querySelector('[data-mobile-menu-toggle]');
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  if (menuButton && mobileMenu) {
    const setOpen = (open) => {
      mobileMenu.classList.toggle('hidden', !open);
      menuButton.setAttribute('aria-expanded', String(open));
      menuButton.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
      const icon = menuButton.querySelector('i');
      if (icon) icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
    };
    menuButton.addEventListener('click', () => setOpen(menuButton.getAttribute('aria-expanded') !== 'true'));
    mobileMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') setOpen(false); });
    window.addEventListener('resize', () => { if (window.innerWidth >= 1024) setOpen(false); });
  }

  // Three-slide cinematic hero. Each package changes the active video moment and fallback poster.
  const heroCarousel = document.querySelector('[data-hero-carousel]');
  if (heroCarousel) {
    const video = heroCarousel.querySelector('[data-hero-video]');
    const poster = heroCarousel.querySelector('[data-hero-poster]');
    const badge = heroCarousel.querySelector('[data-hero-badge]');
    const headline = heroCarousel.querySelector('[data-hero-headline]');
    const packageName = heroCarousel.querySelector('[data-hero-package-name]');
    const packageSeason = heroCarousel.querySelector('[data-hero-package-season]');
    const packagePrice = heroCarousel.querySelector('[data-hero-package-price]');
    const slides = Array.from(heroCarousel.querySelectorAll('[data-hero-slide]')).map(slide => ({
      badge: slide.dataset.heroBadge || '',
      name: slide.dataset.heroPackageName || '',
      season: slide.dataset.heroPackageSeason || '',
      price: slide.dataset.heroPackagePrice || '',
      poster: slide.dataset.heroPoster || '',
      videoPosition: slide.dataset.heroVideoPosition || 'center',
      videoStart: Number(slide.dataset.heroVideoStart || 0),
    }));
    let active = 0;
    let timer = null;

    const showHeroPoster = () => video.classList.add('ds-cinematic-hero__video--unavailable');
    const setVideoTime = start => {
      if (video.readyState < HTMLMediaElement.HAVE_METADATA) {
        video.addEventListener('loadedmetadata', () => setVideoTime(start), { once: true });
        return;
      }

      const maximumStart = Number.isFinite(video.duration) ? Math.max(video.duration - .1, 0) : start;
      video.currentTime = Math.min(start, maximumStart);
    };
    const animateLetters = (element, text, delay = 0) => {
      element.replaceChildren();

      [...text].forEach((character, index) => {
        const letter = document.createElement('span');
        letter.className = 'ds-cinematic-hero__letter';
        letter.style.setProperty('--letter-delay', `${delay + (index * .035)}s`);
        letter.textContent = character === ' ' ? '\u00a0' : character;
        element.append(letter);
      });
    };
    const updatePoster = source => {
      if (!source || poster.getAttribute('src') === source) return;

      poster.classList.add('ds-cinematic-hero__poster--changing');
      poster.src = source;
      poster.addEventListener('load', () => poster.classList.remove('ds-cinematic-hero__poster--changing'), { once: true });
    };
    const update = index => {
      active = (index + slides.length) % slides.length;
      const slide = slides[active];

      heroCarousel.dataset.activeSlide = String(active + 1);
      updatePoster(slide.poster);
      poster.style.objectPosition = slide.videoPosition;
      video.style.objectPosition = slide.videoPosition;
      badge.textContent = slide.badge;
      headline.setAttribute('aria-label', `${slide.name} ${slide.season}`);
      animateLetters(packageName, slide.name, .05);
      animateLetters(packageSeason, slide.season, .38);
      packagePrice.textContent = slide.price;

      if (!reduceMotion.matches) {
        setVideoTime(slide.videoStart);
        video.play().catch(showHeroPoster);
      }
    };
    const stop = () => {
      if (!timer) return;

      clearTimeout(timer);
      timer = null;
    };
    const start = () => {
      if (reduceMotion.matches || document.hidden || timer || slides.length < 2) return;

      timer = window.setTimeout(() => {
        timer = null;
        update(active + 1);
        start();
      }, 6000);
    };

    video.addEventListener('error', showHeroPoster);
    video.querySelector('source')?.addEventListener('error', showHeroPoster);
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
    reduceMotion.addEventListener?.('change', () => {
      if (reduceMotion.matches) {
        stop();
        showHeroPoster();
      } else {
        video.classList.remove('ds-cinematic-hero__video--unavailable');
        update(active);
        start();
      }
    });

    update(0);
    start();
  }

  // Package buttons: choose and focus a card, scroll it into view on small screens.
  const connectCardButtons = (cardSelector, prevSelector, nextSelector) => {
    const cards = Array.from(document.querySelectorAll(cardSelector));
    if (!cards.length) return;
    let active = 0;
    const select = (delta) => {
      active = (active + delta + cards.length) % cards.length;
      cards.forEach((card, i) => {
        if (i === active) card.setAttribute('data-selected', 'true');
        else card.removeAttribute('data-selected');
      });
      cards[active].scrollIntoView({ behavior: motion(), block: 'nearest', inline: 'nearest' });
      cards[active].focus({ preventScroll: true });
    };
    document.querySelector(prevSelector)?.addEventListener('click', () => select(-1));
    document.querySelector(nextSelector)?.addEventListener('click', () => select(1));
  };
  connectCardButtons('[data-package-card]', '[data-package-prev]', '[data-package-next]');
  connectCardButtons('[data-secondary-card]', '[data-secondary-prev]', '[data-secondary-next]');

  // Functional gallery lightbox with native dialog + keyboard navigation.
  const lightbox = document.querySelector('#darul-gallery-lightbox');
  const gallery = document.querySelector('#galeri');
  const galleryButtons = Array.from(document.querySelectorAll('[data-gallery-open]'));
  if (lightbox && galleryButtons.length && typeof lightbox.showModal === 'function') {
    const image = lightbox.querySelector('[data-gallery-preview]');
    const caption = lightbox.querySelector('[data-gallery-caption]');
    const slides = galleryButtons.map(button => {
      const img = button.querySelector('img');
      return { src: img?.getAttribute('src') || '', alt: img?.getAttribute('alt') || 'Foto galeri' };
    });
    let active = 0;
    const show = n => {
      active = (n + slides.length) % slides.length;
      image.src = slides[active].src;
      image.alt = slides[active].alt;
      caption.textContent = `${slides[active].alt} · ${active + 1} / ${slides.length}`;
    };
    const pauseGallery = () => gallery?.setAttribute('data-gallery-paused', 'true');
    const resumeGallery = () => {
      if (galleryButtons.some(button => button.matches(':hover'))) return;

      gallery?.removeAttribute('data-gallery-paused');
    };
    galleryButtons.forEach((button, i) => {
      button.addEventListener('mouseenter', pauseGallery);
      button.addEventListener('mouseleave', () => { if (!lightbox.open) resumeGallery(); });
      button.addEventListener('click', () => { pauseGallery(); show(i); lightbox.showModal(); });
    });
    lightbox.querySelector('[data-gallery-close]')?.addEventListener('click', () => lightbox.close());
    lightbox.querySelector('[data-gallery-prev]')?.addEventListener('click', () => show(active - 1));
    lightbox.querySelector('[data-gallery-next]')?.addEventListener('click', () => show(active + 1));
    lightbox.addEventListener('click', e => { if (e.target === lightbox) lightbox.close(); });
    lightbox.addEventListener('keydown', e => {
      if (e.key === 'ArrowLeft') { e.preventDefault(); show(active - 1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); show(active + 1); }
    });
    lightbox.addEventListener('close', resumeGallery);
  }

  // Progressive enhancement: content remains visible if script is unavailable.
  if ('IntersectionObserver' in window && !reduceMotion.matches) {
    const nodes = document.querySelectorAll('[data-reveal]');
    const observer = new IntersectionObserver((entries, instance) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('ds-is-visible');
        instance.unobserve(entry.target);
      });
    }, { threshold: 0.06 });
    nodes.forEach(node => { node.classList.add('ds-reveal'); observer.observe(node); });
  }
})();
