const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const initializeHeroCarousel = (carousel) => {
    const slides = [...carousel.querySelectorAll('[data-hero-slide]')];
    const content = [...carousel.querySelectorAll('[data-hero-content]')];
    const indicators = [...carousel.querySelectorAll('[data-hero-indicator]')];
    const previousButton = carousel.querySelector('[data-hero-prev]');
    const nextButton = carousel.querySelector('[data-hero-next]');
    const pauseButton = carousel.querySelector('[data-hero-pause]');
    const pauseIcon = carousel.querySelector('[data-hero-pause-icon]');

    if (slides.length < 2) {
        const video = slides[0]?.querySelector('[data-hero-video]');

        if (video && !prefersReducedMotion.matches) {
            loadAndPlayVideo(video);
        }

        return;
    }

    let activeIndex = 0;
    let timer;
    let isManuallyPaused = prefersReducedMotion.matches;
    let isInteractionPaused = false;
    let pointerStartX = 0;

    const clearTimer = () => window.clearTimeout(timer);

    const schedule = () => {
        clearTimer();

        if (isManuallyPaused || isInteractionPaused || prefersReducedMotion.matches) {
            return;
        }

        timer = window.setTimeout(() => activate(activeIndex + 1), 7000);
    };

    const setPauseButton = () => {
        if (!pauseButton || !pauseIcon) {
            return;
        }

        pauseButton.setAttribute('aria-pressed', String(isManuallyPaused));
        pauseButton.setAttribute('aria-label', isManuallyPaused ? 'Sambung tayangan automatik' : 'Jeda tayangan automatik');
        pauseIcon.textContent = isManuallyPaused ? 'play_arrow' : 'pause';
    };

    const activate = (nextIndex) => {
        activeIndex = (nextIndex + slides.length) % slides.length;

        slides.forEach((slide, index) => {
            const isActive = index === activeIndex;
            const video = slide.querySelector('[data-hero-video]');

            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));

            if (video) {
                if (isActive && !prefersReducedMotion.matches) {
                    loadAndPlayVideo(video);
                } else {
                    video.pause();
                }
            }
        });

        content.forEach((item, index) => {
            const isActive = index === activeIndex;

            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-hidden', String(!isActive));
        });

        indicators.forEach((indicator, index) => {
            const isActive = index === activeIndex;

            indicator.classList.toggle('is-active', isActive);
            indicator.setAttribute('aria-selected', String(isActive));
        });

        schedule();
    };

    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => activate(index));
    });

    previousButton?.addEventListener('click', () => activate(activeIndex - 1));
    nextButton?.addEventListener('click', () => activate(activeIndex + 1));
    pauseButton?.addEventListener('click', () => {
        isManuallyPaused = !isManuallyPaused;
        setPauseButton();
        schedule();
    });

    carousel.addEventListener('pointerenter', () => {
        isInteractionPaused = true;
        clearTimer();
    });
    carousel.addEventListener('pointerleave', () => {
        isInteractionPaused = false;
        schedule();
    });
    carousel.addEventListener('focusin', () => {
        isInteractionPaused = true;
        clearTimer();
    });
    carousel.addEventListener('focusout', () => {
        window.setTimeout(() => {
            if (!carousel.contains(document.activeElement)) {
                isInteractionPaused = false;
                schedule();
            }
        });
    });
    carousel.addEventListener('pointerdown', (event) => {
        pointerStartX = event.clientX;
    });
    carousel.addEventListener('pointerup', (event) => {
        const movement = event.clientX - pointerStartX;

        if (Math.abs(movement) > 45) {
            activate(activeIndex + (movement < 0 ? 1 : -1));
        }
    });

    prefersReducedMotion.addEventListener('change', (event) => {
        isManuallyPaused = event.matches;
        setPauseButton();
        schedule();
    });

    setPauseButton();
    activate(0);
};

const loadAndPlayVideo = (video) => {
    if (!video.src && video.dataset.videoSrc) {
        video.src = video.dataset.videoSrc;
        video.load();
    }

    video.play().catch(() => {});
};

const initializeCarousel = (carousel) => {
    const track = carousel.querySelector('[data-carousel-track]');
    const previousButton = carousel.querySelector('[data-carousel-prev]');
    const nextButton = carousel.querySelector('[data-carousel-next]');
    const autoplayDelay = Number(carousel.dataset.autoplay);

    if (!track) {
        return;
    }

    let timer;
    let isPaused = prefersReducedMotion.matches;

    const itemStep = () => {
        const firstItem = track.querySelector(':scope > div > *');

        if (!firstItem) {
            return track.clientWidth;
        }

        const gap = Number.parseFloat(getComputedStyle(firstItem.parentElement).gap) || 0;

        return firstItem.getBoundingClientRect().width + gap;
    };

    const move = (direction) => {
        const isAtStart = track.scrollLeft <= 1;
        const isAtEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;

        if (direction > 0 && isAtEnd) {
            track.scrollTo({ left: 0, behavior: 'smooth' });

            return;
        }

        if (direction < 0 && isAtStart) {
            track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });

            return;
        }

        track.scrollBy({ left: itemStep() * direction, behavior: 'smooth' });
    };

    const schedule = () => {
        window.clearTimeout(timer);

        if (!autoplayDelay || isPaused || prefersReducedMotion.matches) {
            return;
        }

        timer = window.setTimeout(() => {
            move(1);
            schedule();
        }, autoplayDelay);
    };

    previousButton?.addEventListener('click', () => {
        move(-1);
        schedule();
    });
    nextButton?.addEventListener('click', () => {
        move(1);
        schedule();
    });

    ['pointerenter', 'focusin', 'touchstart'].forEach((eventName) => {
        carousel.addEventListener(eventName, () => {
            isPaused = true;
            window.clearTimeout(timer);
        }, { passive: true });
    });
    ['pointerleave', 'focusout', 'touchend'].forEach((eventName) => {
        carousel.addEventListener(eventName, () => {
            window.setTimeout(() => {
                if (eventName !== 'focusout' || !carousel.contains(document.activeElement)) {
                    isPaused = false;
                    schedule();
                }
            });
        }, { passive: true });
    });

    prefersReducedMotion.addEventListener('change', (event) => {
        isPaused = event.matches;
        schedule();
    });

    schedule();
};

const initializeGalleryLightbox = () => {
    const dialog = document.querySelector('[data-gallery-lightbox]');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const image = dialog.querySelector('[data-gallery-lightbox-image]');
    const caption = dialog.querySelector('[data-gallery-lightbox-caption]');
    const images = [...document.querySelectorAll('[data-gallery-image]:not([aria-hidden="true"])')];
    let activeIndex = 0;
    let activeRow;

    const showImage = (index) => {
        activeIndex = (index + images.length) % images.length;
        const trigger = images[activeIndex];

        if (!(image instanceof HTMLImageElement) || !trigger) {
            return;
        }

        image.src = trigger.dataset.galleryFull ?? '';
        image.alt = trigger.dataset.galleryAlt ?? '';

        if (caption) {
            caption.textContent = trigger.dataset.galleryAlt ?? '';
        }
    };

    images.forEach((trigger, index) => {
        trigger.addEventListener('click', () => {
            activeRow = trigger.closest('[data-gallery-row]');
            activeRow?.classList.add('is-paused');
            showImage(index);
            dialog.showModal();
        });
    });

    dialog.querySelector('[data-gallery-close]')?.addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-gallery-previous]')?.addEventListener('click', () => showImage(activeIndex - 1));
    dialog.querySelector('[data-gallery-next]')?.addEventListener('click', () => showImage(activeIndex + 1));
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        activeRow?.classList.remove('is-paused');
        activeRow = undefined;

        if (image instanceof HTMLImageElement) {
            image.removeAttribute('src');
        }
    });
};

const initializeVideoDialog = () => {
    const dialog = document.querySelector('[data-video-dialog]');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const frame = dialog.querySelector('[data-video-frame]');

    if (!frame) {
        return;
    }

    document.querySelectorAll('[data-video-launch]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const url = trigger.dataset.videoUrl;

            if (!url) {
                return;
            }

            const isDirectVideo = /\.(mp4|webm|ogg)(\?.*)?$/i.test(url);
            const player = document.createElement(isDirectVideo ? 'video' : 'iframe');

            if (player instanceof HTMLVideoElement) {
                player.src = url;
                player.controls = true;
                player.autoplay = true;
                player.playsInline = true;
            } else {
                player.src = url;
                player.title = 'Video persediaan Umrah';
                player.allow = 'autoplay; fullscreen; picture-in-picture';
                player.allowFullscreen = true;
            }

            frame.replaceChildren(player);
            dialog.showModal();
        });
    });

    dialog.querySelector('[data-video-close]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => frame.replaceChildren());
};

document.querySelectorAll('[data-hero-carousel]').forEach(initializeHeroCarousel);
document.querySelectorAll('[data-carousel]').forEach(initializeCarousel);
initializeGalleryLightbox();
initializeVideoDialog();
