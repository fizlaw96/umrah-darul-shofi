/* Darul Shofi landing page interactions — plain JavaScript, no dependencies. */
(() => {
  'use strict';
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const motion = () => reduceMotion.matches ? 'auto' : 'smooth';

  document.querySelectorAll('[data-package-image-frame]').forEach(frame => {
    const image = frame.querySelector('[data-package-image]');
    if (!(image instanceof HTMLImageElement)) return;

    const setImageOrientation = () => {
      if (!image.naturalWidth) return;
      frame.toggleAttribute('data-portrait', image.naturalHeight > image.naturalWidth);
    };

    if (image.complete) {
      setImageOrientation();
    } else {
      image.addEventListener('load', setImageOrientation, { once: true });
    }
  });

  const packageLightbox = document.querySelector('[data-home-package-lightbox]');
  if (packageLightbox instanceof HTMLDialogElement) {
    const image = packageLightbox.querySelector('[data-home-package-lightbox-image]');
    const caption = packageLightbox.querySelector('[data-home-package-lightbox-caption]');
    document.querySelectorAll('[data-home-package-poster]').forEach(trigger => trigger.addEventListener('click', () => {
      if (image instanceof HTMLImageElement) {
        image.src = trigger.dataset.posterSrc || '';
        image.alt = trigger.dataset.posterAlt || '';
      }
      if (caption) caption.textContent = trigger.dataset.posterAlt || '';
      packageLightbox.showModal();
    }));
    packageLightbox.querySelector('[data-home-package-lightbox-close]')?.addEventListener('click', () => packageLightbox.close());
    packageLightbox.addEventListener('click', event => { if (event.target === packageLightbox) packageLightbox.close(); });
    packageLightbox.addEventListener('close', () => { if (image instanceof HTMLImageElement) image.removeAttribute('src'); });
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
    const priceWrapper = heroCarousel.querySelector('[data-hero-price-wrapper]');
    const cta = heroCarousel.querySelector('[data-hero-cta]');
    const slides = Array.from(heroCarousel.querySelectorAll('[data-hero-slide]')).map(slide => ({
      badge: slide.dataset.heroBadge || '',
      name: slide.dataset.heroPackageName || '',
      season: slide.dataset.heroPackageSeason || '',
      price: slide.dataset.heroPackagePrice || '',
      ctaLabel: slide.dataset.heroCtaLabel || 'Lihat Pakej',
      ctaUrl: slide.dataset.heroCtaUrl || '#pakej',
      poster: slide.dataset.heroPoster || '',
      posterSrcset: slide.dataset.heroPosterSrcset || '',
      videoPoster: slide.dataset.heroVideoPoster || slide.dataset.heroPoster || '',
      imagePosition: slide.dataset.heroImagePosition || '50% 50%',
      video: slide.dataset.heroVideo || '',
      videoPosition: slide.dataset.heroVideoPosition || 'center',
      videoStart: Number(slide.dataset.heroVideoStart || 0),
    }));
    let active = 0;
    let timer = null;

    const showHeroPoster = () => video?.classList.add('ds-cinematic-hero__video--unavailable');
    const showHeroVideo = () => video?.classList.remove('ds-cinematic-hero__video--unavailable');
    const setVideoTime = start => {
      if (!video) return;

      if (video.readyState < HTMLMediaElement.HAVE_METADATA) {
        video.addEventListener('loadedmetadata', () => setVideoTime(start), { once: true });
        return;
      }

      const maximumStart = Number.isFinite(video.duration) ? Math.max(video.duration - .1, 0) : start;
      video.currentTime = Math.min(start, maximumStart);
    };
    const animateLetters = (element, text, delay = 0) => {
      element.replaceChildren();

      let characterIndex = 0;

      text.trim().split(/\s+/).filter(Boolean).forEach((word, wordIndex, words) => {
        const wordElement = document.createElement('span');
        wordElement.className = 'ds-cinematic-hero__word';

        [...word].forEach(character => {
          const letter = document.createElement('span');
          letter.className = 'ds-cinematic-hero__letter';
          letter.style.setProperty('--letter-delay', `${delay + (characterIndex * .035)}s`);
          letter.textContent = character;
          wordElement.append(letter);
          characterIndex += 1;
        });

        element.append(wordElement);

        if (wordIndex < words.length - 1) {
          element.append(document.createTextNode(' '));
          characterIndex += 1;
        }
      });
    };
    const updatePoster = (source, srcset) => {
      if (!source || poster.getAttribute('src') === source) return;

      poster.classList.add('ds-cinematic-hero__poster--changing');
      // srcset wins over src, so it must change with it or the old slide stays visible.
      if (srcset) poster.srcset = srcset;
      else poster.removeAttribute('srcset');
      poster.src = source;
      poster.addEventListener('load', () => poster.classList.remove('ds-cinematic-hero__poster--changing'), { once: true });
    };
    const setVideoSource = slide => {
      if (!video) return false;

      if (!slide.video) {
        video.pause();

        if (video.getAttribute('src')) {
          video.removeAttribute('src');
          video.load();
        }

        showHeroPoster();
        return false;
      }

      if (video.getAttribute('src') !== slide.video) {
        video.src = slide.video;
        video.poster = slide.videoPoster;
        video.load();
      }

      showHeroVideo();
      return true;
    };
    const update = index => {
      active = (index + slides.length) % slides.length;
      const slide = slides[active];

      heroCarousel.dataset.activeSlide = String(active + 1);
      updatePoster(slide.poster, slide.posterSrcset);
      poster.style.objectPosition = slide.imagePosition;
      if (video) video.style.objectPosition = slide.videoPosition;
      badge.textContent = slide.badge;
      headline.setAttribute('aria-label', `${slide.name} ${slide.season}`);
      animateLetters(packageName, slide.name, .05);
      animateLetters(packageSeason, slide.season, .38);
      packagePrice.textContent = slide.price;
      priceWrapper?.classList.toggle('hidden', !slide.price);
      if (cta) {
        cta.href = slide.ctaUrl;
        cta.firstChild.textContent = `${slide.ctaLabel} `;
      }

      const hasVideo = setVideoSource(slide);

      if (!reduceMotion.matches && hasVideo) {
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

    video?.addEventListener('error', showHeroPoster);
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

  document.querySelectorAll('[data-package-carousel]').forEach(carousel => {
    const scroller = carousel.querySelector('[data-package-scroller]');
    if (!scroller) return;

    const swipeHint = carousel.querySelector('[data-package-swipe-hint]');
    const swipeHintText = carousel.querySelector('[data-package-swipe-hint-text]');
    const updateSwipeHint = () => {
      if (!swipeHint) return;

      const canScroll = scroller.scrollWidth > scroller.clientWidth + 8;
      swipeHint.dataset.scrollable = String(canScroll);
      if (!swipeHintText) return;

      swipeHintText.textContent = canScroll && scroller.scrollLeft > 24
        ? 'Leret untuk kembali atau lihat lagi'
        : 'Leret untuk lihat pakej lain';
    };

    const amount = () => Math.max(scroller.clientWidth * .78, 260);
    carousel.querySelector('[data-package-scroll="previous"]')?.addEventListener('click', () => {
      scroller.scrollBy({ left: -amount(), behavior: motion() });
    });
    carousel.querySelector('[data-package-scroll="next"]')?.addEventListener('click', () => {
      scroller.scrollBy({ left: amount(), behavior: motion() });
    });
    scroller.addEventListener('scroll', updateSwipeHint, { passive: true });
    window.addEventListener('resize', updateSwipeHint);
    updateSwipeHint();
  });

  // Functional gallery lightbox with native dialog + keyboard navigation.
  const lightbox = document.querySelector('#darul-gallery-lightbox');
  const gallery = document.querySelector('#galeri');
  const galleryButtons = Array.from(document.querySelectorAll('[data-gallery-open]'));
  if (lightbox && galleryButtons.length && typeof lightbox.showModal === 'function') {
    const image = lightbox.querySelector('[data-gallery-preview]');
    const slides = galleryButtons.map(button => {
      const img = button.querySelector('img');
      return { src: img?.getAttribute('src') || '', alt: img?.getAttribute('alt') || 'Foto galeri' };
    });
    let active = 0;
    const show = n => {
      active = (n + slides.length) % slides.length;
      image.src = slides[active].src;
      image.alt = slides[active].alt;
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

  // Full gallery: filter by package category and open a shared lightbox.
  const fullGallery = document.querySelector('[data-full-gallery]');
  if (fullGallery) {
    const categoryButtons = Array.from(fullGallery.querySelectorAll('[data-full-gallery-filter]'));
    const fullGalleryButtons = Array.from(fullGallery.querySelectorAll('[data-full-gallery-open]'));
    const fullGalleryLightbox = fullGallery.querySelector('[data-full-gallery-lightbox]');
    let activeCategory = 'all';
    let activeImage = 0;

    const visibleButtons = () => fullGalleryButtons.filter(button => !button.hidden);
    const showImage = index => {
      if (!(fullGalleryLightbox instanceof HTMLDialogElement)) return;

      const slides = visibleButtons();
      if (!slides.length) return;

      activeImage = (index + slides.length) % slides.length;
      const button = slides[activeImage];
      const preview = fullGalleryLightbox.querySelector('[data-full-gallery-preview]');
      const caption = fullGalleryLightbox.querySelector('[data-full-gallery-caption]');

      if (preview instanceof HTMLImageElement) {
        preview.src = button.dataset.fullGallerySrc || '';
        preview.alt = button.dataset.fullGalleryAlt || '';
      }
      if (caption) {
        caption.textContent = `${button.dataset.fullGalleryCaption || 'Galeri SalamHaji'} · ${activeImage + 1} / ${slides.length}`;
      }
    };
    const filter = category => {
      activeCategory = category;
      fullGalleryButtons.forEach(button => {
        button.hidden = category !== 'all' && button.dataset.fullGalleryCategory !== category;
      });
      categoryButtons.forEach(button => {
        const selected = button.dataset.fullGalleryFilter === category;
        button.classList.toggle('is-active', selected);
        button.setAttribute('aria-pressed', String(selected));
      });
    };

    categoryButtons.forEach(button => button.addEventListener('click', () => filter(button.dataset.fullGalleryFilter || 'all')));
    fullGalleryButtons.forEach(button => button.addEventListener('click', () => {
      const slides = visibleButtons();
      activeImage = slides.indexOf(button);
      showImage(activeImage);
      fullGalleryLightbox?.showModal();
    }));

    if (fullGalleryLightbox instanceof HTMLDialogElement) {
      fullGalleryLightbox.querySelector('[data-full-gallery-close]')?.addEventListener('click', () => fullGalleryLightbox.close());
      fullGalleryLightbox.querySelector('[data-full-gallery-prev]')?.addEventListener('click', () => showImage(activeImage - 1));
      fullGalleryLightbox.querySelector('[data-full-gallery-next]')?.addEventListener('click', () => showImage(activeImage + 1));
      fullGalleryLightbox.addEventListener('click', event => { if (event.target === fullGalleryLightbox) fullGalleryLightbox.close(); });
      fullGalleryLightbox.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') { event.preventDefault(); showImage(activeImage - 1); }
        if (event.key === 'ArrowRight') { event.preventDefault(); showImage(activeImage + 1); }
      });
    }

    filter(activeCategory);
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
