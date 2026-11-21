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

const initializePackageImageFrames = () => {
    document.querySelectorAll('[data-package-image-frame]').forEach((frame) => {
        const image = frame.querySelector('[data-package-image]');

        if (!(image instanceof HTMLImageElement)) {
            return;
        }

        const setImageOrientation = () => {
            if (image.naturalWidth === 0) {
                return;
            }

            if (image.naturalHeight > image.naturalWidth) {
                frame.setAttribute('data-portrait', 'true');

                return;
            }

            frame.removeAttribute('data-portrait');
        };

        if (image.complete) {
            setImageOrientation();

            return;
        }

        image.addEventListener('load', setImageOrientation, { once: true });
    });
};

const initializePackagePosterLightbox = () => {
    const dialog = document.querySelector('[data-package-poster-lightbox]');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const image = dialog.querySelector('[data-package-poster-lightbox-image]');
    const caption = dialog.querySelector('[data-package-poster-lightbox-caption]');

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    document.querySelectorAll('[data-package-poster]').forEach((trigger) => {
        if (!(trigger instanceof HTMLButtonElement)) {
            return;
        }

        trigger.addEventListener('click', () => {
            image.src = trigger.dataset.packagePosterSrc ?? '';
            image.alt = trigger.dataset.packagePosterAlt ?? '';

            if (caption instanceof HTMLElement) {
                caption.textContent = trigger.dataset.packagePosterAlt ?? '';
            }

            dialog.showModal();
        });
    });

    dialog.querySelector('[data-package-poster-lightbox-close]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        image.removeAttribute('src');
        image.alt = '';

        if (caption instanceof HTMLElement) {
            caption.textContent = '';
        }
    });
};

const initializeHotelLightbox = () => {
    const dialog = document.querySelector('[data-hotel-lightbox]');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const image = dialog.querySelector('[data-hotel-lightbox-image]');
    const caption = dialog.querySelector('[data-hotel-lightbox-caption]');

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    document.querySelectorAll('[data-hotel-lightbox-trigger]').forEach((trigger) => {
        if (!(trigger instanceof HTMLButtonElement)) {
            return;
        }

        trigger.addEventListener('click', () => {
            image.src = trigger.dataset.hotelLightboxSrc ?? '';
            image.alt = trigger.dataset.hotelLightboxAlt ?? '';

            if (caption instanceof HTMLElement) {
                caption.textContent = trigger.dataset.hotelLightboxAlt ?? '';
            }

            dialog.showModal();
        });
    });

    dialog.querySelector('[data-hotel-lightbox-close]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        image.removeAttribute('src');
        image.alt = '';

        if (caption instanceof HTMLElement) {
            caption.textContent = '';
        }
    });
};

const initializePackageCoverLayouts = () => {
    document.querySelectorAll('[data-package-cover]').forEach((cover) => {
        const image = cover.querySelector('[data-package-cover-image]');

        if (!(image instanceof HTMLImageElement)) {
            return;
        }

        const setCoverOrientation = () => {
            if (image.naturalWidth === 0) {
                return;
            }

            cover.toggleAttribute('data-landscape', image.naturalWidth > image.naturalHeight);
        };

        if (image.complete) {
            setCoverOrientation();

            return;
        }

        image.addEventListener('load', setCoverOrientation, { once: true });
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

const initializePackageFilters = () => {
    const scrollPositionKey = 'package-filters-scroll-position';
    const scrollPositionParameter = 'filter_scroll_position';

    document.querySelectorAll('[data-package-filters]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const minimumInput = form.querySelector('[data-budget-minimum]');
        const maximumInput = form.querySelector('[data-budget-maximum]');
        const selection = form.querySelector('[data-budget-selection]');
        const output = form.querySelector('[data-budget-output]');

        if (!(minimumInput instanceof HTMLInputElement) || !(maximumInput instanceof HTMLInputElement) || !selection || !output) {
            return;
        }

        const rangeMinimum = Number(minimumInput.min);
        const rangeMaximum = Number(minimumInput.max);
        const budgetFormatter = new Intl.NumberFormat('ms-MY', {
            style: 'currency',
            currency: 'MYR',
            maximumFractionDigits: 0,
        });

        const updateBudgetRange = (changedInput) => {
            if (Number(minimumInput.value) > Number(maximumInput.value)) {
                if (changedInput === minimumInput) {
                    maximumInput.value = minimumInput.value;
                } else {
                    minimumInput.value = maximumInput.value;
                }
            }

            const minimum = Number(minimumInput.value);
            const maximum = Number(maximumInput.value);
            const percentage = rangeMaximum - rangeMinimum;
            const start = ((minimum - rangeMinimum) / percentage) * 100;
            const width = ((maximum - minimum) / percentage) * 100;

            selection.style.left = `${start}%`;
            selection.style.width = `${width}%`;
            output.textContent = `${budgetFormatter.format(minimum)} – ${budgetFormatter.format(maximum)}`;
        };

        const saveScrollPosition = () => {
            const scrollPosition = String(window.scrollY);

            try {
                window.sessionStorage.setItem(scrollPositionKey, scrollPosition);
            } catch {
                // The URL parameter below is used when session storage is unavailable.
            }

            return scrollPosition;
        };

        const saveFallbackScrollPosition = (scrollPosition) => {
            let input = form.querySelector(`[data-filter-scroll-position="${scrollPositionParameter}"]`);

            if (!(input instanceof HTMLInputElement)) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = scrollPositionParameter;
                input.dataset.filterScrollPosition = scrollPositionParameter;
                form.append(input);
            }

            input.value = scrollPosition;
        };

        const restoreScrollPosition = () => {
            let savedScrollPosition = null;

            try {
                savedScrollPosition = window.sessionStorage.getItem(scrollPositionKey);
                window.sessionStorage.removeItem(scrollPositionKey);
            } catch {
                // The URL parameter below is used when session storage is unavailable.
            }

            const url = new URL(window.location.href);
            const fallbackScrollPosition = url.searchParams.get(scrollPositionParameter);

            if (fallbackScrollPosition !== null) {
                url.searchParams.delete(scrollPositionParameter);
                window.history.replaceState(window.history.state, '', `${url.pathname}${url.search}${url.hash}`);
                savedScrollPosition ??= fallbackScrollPosition;
            }

            if (savedScrollPosition === null || !Number.isFinite(Number(savedScrollPosition))) {
                return;
            }

            window.requestAnimationFrame(() => {
                const scrollBehavior = document.documentElement.style.scrollBehavior;

                document.documentElement.style.scrollBehavior = 'auto';
                window.scrollTo({ left: window.scrollX, top: Number(savedScrollPosition), behavior: 'auto' });
                document.documentElement.style.scrollBehavior = scrollBehavior;
            });
        };

        const submitFilters = () => form.requestSubmit();

        minimumInput.addEventListener('input', () => updateBudgetRange(minimumInput));
        maximumInput.addEventListener('input', () => updateBudgetRange(maximumInput));
        minimumInput.addEventListener('change', submitFilters);
        maximumInput.addEventListener('change', submitFilters);
        form.querySelectorAll('[data-auto-submit]').forEach((input) => input.addEventListener('change', submitFilters));
        form.addEventListener('submit', () => {
            const fallbackScrollPosition = saveScrollPosition();

            saveFallbackScrollPosition(fallbackScrollPosition);
        });
        form.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', (event) => {
            event.preventDefault();

            const fallbackScrollPosition = saveScrollPosition();
            const url = new URL(link.href);

            url.searchParams.set(scrollPositionParameter, fallbackScrollPosition);
            window.location.assign(url);
        }));

        updateBudgetRange();
        restoreScrollPosition();
    });
};

document.querySelectorAll('[data-hero-carousel]').forEach(initializeHeroCarousel);
document.querySelectorAll('[data-carousel]').forEach(initializeCarousel);
initializeGalleryLightbox();
initializePackageImageFrames();
initializePackageCoverLayouts();
initializeVideoDialog();
initializePackageFilters();

const initializePasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const input = document.getElementById(toggle.dataset.passwordTarget ?? '');

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            toggle.setAttribute('aria-label', isPassword ? 'Sembunyikan kata laluan' : 'Papar kata laluan');
            toggle.querySelector('.material-symbols-outlined').textContent = isPassword ? 'visibility_off' : 'visibility';
        });
    });
};

initializePasswordToggles();
initializePackagePosterLightbox();
initializeHotelLightbox();
