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

const initializeAqiqahKorbanForms = () => {
    const adminCategory = document.querySelector('[data-aqiqah-korban-category]');
    const korbanFields = document.querySelector('[data-aqiqah-korban-korban-fields]');

    if (adminCategory instanceof HTMLSelectElement && korbanFields instanceof HTMLElement) {
        const syncKorbanFields = () => {
            const isKorban = adminCategory.value === 'Korban';

            korbanFields.hidden = !isKorban;
            korbanFields.querySelectorAll('input').forEach((input) => {
                input.disabled = !isKorban;
            });
        };

        adminCategory.addEventListener('change', syncKorbanFields);
        syncKorbanFields();
    }

    const root = document.querySelector('[data-aqiqah-korban-enquiry]');
    const enquiryForm = root?.querySelector('form[data-aqiqah-korban-enquiry]');

    if (!(root instanceof HTMLElement) || !(enquiryForm instanceof HTMLFormElement)) {
        return;
    }

    const categoryInputs = [...enquiryForm.querySelectorAll('[data-aqiqah-korban-category]')]
        .filter((input) => input instanceof HTMLInputElement);
    const serviceInput = enquiryForm.querySelector('[data-aqiqah-korban-service-input]');
    const serviceButtons = [...enquiryForm.querySelectorAll('[data-aqiqah-korban-service-option]')]
        .filter((button) => button instanceof HTMLButtonElement);
    const details = enquiryForm.querySelector('[data-aqiqah-korban-details]');
    const categoryOnlyFields = [...enquiryForm.querySelectorAll('[data-aqiqah-korban-only]')]
        .filter((element) => element instanceof HTMLElement);
    const participantNames = enquiryForm.querySelector('[data-aqiqah-korban-participant-names]');
    const participantHint = enquiryForm.querySelector('[data-aqiqah-korban-participant-hint]');
    const childGender = enquiryForm.querySelector('[data-aqiqah-korban-child-gender]');
    const genderHint = enquiryForm.querySelector('[data-aqiqah-korban-gender-hint]');
    const quantityInput = enquiryForm.querySelector('[name="quantity"]');
    const steps = [...enquiryForm.querySelectorAll('[data-aqiqah-korban-step]')]
        .filter((step) => step instanceof HTMLElement);
    const progressButtons = [...enquiryForm.querySelectorAll('[data-aqiqah-korban-progress-step]')]
        .filter((button) => button instanceof HTMLButtonElement);
    const acknowledgement = enquiryForm.querySelector('[data-aqiqah-korban-acknowledgement]');
    const submitButton = enquiryForm.querySelector('[data-aqiqah-korban-submit]');
    const userLocation = enquiryForm.querySelector('[data-aqiqah-korban-user-location]');
    const userLatitude = enquiryForm.querySelector('[data-aqiqah-korban-user-latitude]');
    const userLongitude = enquiryForm.querySelector('[data-aqiqah-korban-user-longitude]');
    const userTimezone = enquiryForm.querySelector('[data-aqiqah-korban-user-timezone]');
    const locationStatus = enquiryForm.querySelector('[data-aqiqah-korban-location-status]');

    if (!(serviceInput instanceof HTMLInputElement) || categoryInputs.length === 0 || steps.length === 0) {
        return;
    }

    let currentStep = 1;
    let completedStep = 0;

    const valueFor = (name) => {
        const input = enquiryForm.querySelector(`[name="${name}"]`);

        return input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement
            ? input.value.trim()
            : '';
    };
    const selectedCategory = () => categoryInputs.find((input) => input.checked)?.value ?? '';
    const selectedService = () => serviceButtons.find((button) => button.dataset.serviceId === serviceInput.value) ?? null;
    const feedbackFor = (stepNumber) => enquiryForm.querySelector(`[data-aqiqah-korban-feedback="${stepNumber}"]`);
    const setFeedback = (stepNumber, message = '') => {
        const feedback = feedbackFor(stepNumber);

        if (feedback instanceof HTMLElement) {
            feedback.textContent = message;
        }
    };
    const setReviewValue = (selector, value) => {
        const output = enquiryForm.querySelector(selector);

        if (output instanceof HTMLElement) {
            output.textContent = value || '—';
        }
    };
    const deviceTimezone = () => {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch {
            return '';
        }
    };
    const syncTimezone = () => {
        if (userTimezone instanceof HTMLInputElement) {
            userTimezone.value = deviceTimezone();
        }
    };
    const participantNameList = () => (participantNames instanceof HTMLTextAreaElement ? participantNames.value : '')
        .split(/\r?\n/)
        .map((name) => name.trim())
        .filter(Boolean);
    const maximumParticipants = () => {
        const slots = Number(selectedService()?.dataset.serviceSlots) || 1;

        return slots * Math.max(1, Number(valueFor('quantity')) || 1);
    };
    const estimatedTotal = () => {
        const price = Number(selectedService()?.dataset.servicePriceValue);

        if (!selectedService()?.dataset.servicePriceValue || Number.isNaN(price)) {
            return '';
        }

        const total = price * Math.max(1, Number(valueFor('quantity')) || 1);

        return `RM ${total.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };
    const syncCategoryFields = () => {
        const category = selectedCategory();

        categoryOnlyFields.forEach((element) => {
            const isVisible = element.dataset.aqiqahKorbanOnly === category;

            element.hidden = !isVisible;
            element.querySelectorAll('input, select, textarea').forEach((input) => {
                input.disabled = !isVisible;
            });
        });
    };
    const syncParticipantHint = () => {
        if (!(participantHint instanceof HTMLElement) || !(participantNames instanceof HTMLTextAreaElement)) {
            return;
        }

        const count = participantNameList().length;
        const maximum = maximumParticipants();

        participantHint.textContent = `Satu nama bagi setiap baris. ${count} daripada maksimum ${maximum} nama untuk kuantiti dipilih.`;
        participantNames.setCustomValidity(count > maximum ? `Maksimum ${maximum} nama peserta untuk kuantiti dipilih.` : '');
    };
    const suggestAqiqahQuantity = () => {
        const service = selectedService();

        if (!(childGender instanceof HTMLSelectElement) || !(quantityInput instanceof HTMLInputElement) || !service) {
            return;
        }

        const isSingleSmallAnimal = ['Kambing', 'Biri-biri'].includes(service.dataset.serviceAnimal ?? '')
            && service.dataset.serviceQuantity === '1 Ekor';

        if (!isSingleSmallAnimal || !childGender.value) {
            return;
        }

        const suggestedQuantity = childGender.value === 'Lelaki' ? 2 : 1;
        quantityInput.value = String(suggestedQuantity);

        if (genderHint instanceof HTMLElement) {
            genderHint.textContent = `Kuantiti dicadangkan: ${suggestedQuantity} ekor untuk anak ${childGender.value.toLowerCase()}. Anda boleh mengubahnya jika perlu.`;
        }
    };
    const syncServiceOptions = () => {
        const category = selectedCategory();
        const service = selectedService();

        serviceButtons.forEach((button) => {
            const isVisible = button.dataset.category === category;

            button.hidden = !isVisible;
            button.setAttribute('aria-pressed', String(button === service));
        });

        if (service !== null && service.dataset.category !== category) {
            serviceInput.value = '';
        }

        if (details instanceof HTMLElement) {
            details.hidden = !serviceInput.value;
        }

        syncCategoryFields();
        syncParticipantHint();
    };
    const syncSummary = () => {
        const service = selectedService();

        setReviewValue('[data-aqiqah-korban-review-category]', selectedCategory());
        setReviewValue('[data-aqiqah-korban-review-service]', service?.dataset.serviceName ?? '');
        setReviewValue('[data-aqiqah-korban-review-service-details]', service ? `${service.dataset.serviceAnimal ?? ''} · ${service.dataset.serviceQuantity ?? ''}` : '');
        setReviewValue('[data-aqiqah-korban-review-price]', service?.dataset.servicePrice ?? '');
        setReviewValue('[data-aqiqah-korban-review-total]', estimatedTotal() || 'Atas pengesahan');
        setReviewValue('[data-aqiqah-korban-review-name]', valueFor('full_name'));
        setReviewValue('[data-aqiqah-korban-review-contact]', valueFor('contact_number'));
        setReviewValue(
            '[data-aqiqah-korban-review-beneficiary]',
            selectedCategory() === 'Aqiqah'
                ? [valueFor('child_name'), valueFor('child_gender'), valueFor('father_name') ? `Bapa: ${valueFor('father_name')}` : ''].filter(Boolean).join(' · ')
                : participantNameList().map((name, index) => `${index + 1}. ${name}`).join('\n'),
        );
        setReviewValue('[data-aqiqah-korban-review-quantity]', valueFor('quantity'));
        setReviewValue('[data-aqiqah-korban-review-location]', valueFor('user_location'));
        setReviewValue('[data-aqiqah-korban-review-timezone]', valueFor('user_timezone'));
        setReviewValue('[data-aqiqah-korban-review-date]', valueFor('requested_execution_date') || 'Tidak diminta');
    };
    const syncSubmitButton = () => {
        if (!(submitButton instanceof HTMLButtonElement)) {
            return;
        }

        const isAcknowledged = acknowledgement instanceof HTMLInputElement && acknowledgement.checked;
        submitButton.disabled = !isAcknowledged;
        submitButton.setAttribute('aria-disabled', String(!isAcknowledged));
    };
    const syncWizard = () => {
        steps.forEach((step) => {
            const stepNumber = Number(step.dataset.aqiqahKorbanStep);

            step.hidden = stepNumber !== currentStep;
            step.classList.toggle('is-current', stepNumber === currentStep);
            step.classList.toggle('is-complete', stepNumber <= completedStep);
        });

        progressButtons.forEach((button) => {
            const stepNumber = Number(button.dataset.aqiqahKorbanProgressStep);
            const isCurrent = stepNumber === currentStep;

            button.classList.toggle('is-current', isCurrent);
            button.classList.toggle('is-complete', stepNumber <= completedStep);
            button.disabled = stepNumber > completedStep + 1;

            if (isCurrent) {
                button.setAttribute('aria-current', 'step');
            } else {
                button.removeAttribute('aria-current');
            }
        });
    };
    const showStep = (stepNumber, shouldScroll = true) => {
        currentStep = stepNumber;
        syncSummary();
        syncWizard();

        const step = steps.find((item) => Number(item.dataset.aqiqahKorbanStep) === stepNumber);

        if (shouldScroll && step instanceof HTMLElement) {
            step.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };
    const validateStep = (stepNumber) => {
        if (stepNumber === 1 && !selectedCategory()) {
            setFeedback(stepNumber, 'Sila pilih Aqiqah atau Korban untuk meneruskan.');

            return false;
        }

        if (stepNumber === 2) {
            if (!serviceInput.value) {
                setFeedback(stepNumber, 'Sila pilih satu tawaran perkhidmatan.');

                return false;
            }

            const invalidField = [...(details?.querySelectorAll('input, textarea, select') ?? [])].find((input) => {
                if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement || input instanceof HTMLSelectElement)) {
                    return false;
                }

                return !input.disabled && !input.checkValidity();
            });

            if (invalidField instanceof HTMLInputElement || invalidField instanceof HTMLTextAreaElement || invalidField instanceof HTMLSelectElement) {
                setFeedback(stepNumber, 'Sila lengkapkan butiran wajib sebelum meneruskan.');
                invalidField.reportValidity();

                return false;
            }
        }

        if (stepNumber === 3 && !(acknowledgement instanceof HTMLInputElement && acknowledgement.checked)) {
            setFeedback(stepNumber, 'Sila tandakan pengesahan maklumat penting sebelum ke WhatsApp.');

            return false;
        }

        setFeedback(stepNumber);

        return true;
    };
    const selectService = (button) => {
        if (!button.dataset.serviceId || button.dataset.category !== selectedCategory()) {
            return;
        }

        serviceInput.value = button.dataset.serviceId;
        syncServiceOptions();
        syncSummary();
    };

    categoryInputs.forEach((input) => {
        input.addEventListener('change', () => {
            serviceInput.value = '';
            syncServiceOptions();
            syncSummary();
        });
    });
    serviceButtons.forEach((button) => button.addEventListener('click', () => selectService(button)));
    document.querySelectorAll('[data-aqiqah-korban-select-service]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const category = button.dataset.category;
            const serviceId = button.dataset.serviceId;
            const categoryInput = categoryInputs.find((input) => input.value === category);

            if (!serviceId || !(categoryInput instanceof HTMLInputElement)) {
                return;
            }

            categoryInput.checked = true;
            serviceInput.value = serviceId;
            completedStep = Math.max(completedStep, 1);
            syncServiceOptions();
            showStep(2);
        });
    });
    enquiryForm.querySelectorAll('input, textarea, select').forEach((input) => {
        input.addEventListener('input', syncSummary);
        input.addEventListener('change', syncSummary);
    });
    if (childGender instanceof HTMLSelectElement) {
        childGender.addEventListener('change', () => {
            suggestAqiqahQuantity();
            syncSummary();
        });
    }
    [participantNames, quantityInput].forEach((input) => {
        input?.addEventListener('input', syncParticipantHint);
    });
    enquiryForm.querySelectorAll('[data-aqiqah-korban-next-step]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const nextStep = Number(button.dataset.aqiqahKorbanNextStep);

            if (!validateStep(currentStep)) {
                return;
            }

            completedStep = Math.max(completedStep, currentStep);
            showStep(nextStep);
        });
    });
    progressButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const stepNumber = Number(button.dataset.aqiqahKorbanProgressStep);

            if (stepNumber <= completedStep + 1) {
                showStep(stepNumber);
            }
        });
    });
    if (acknowledgement instanceof HTMLInputElement) {
        acknowledgement.addEventListener('change', syncSubmitButton);
    }

    enquiryForm.querySelector('[data-aqiqah-korban-use-gps]')?.addEventListener('click', () => {
        if (!(userLocation instanceof HTMLInputElement) || !(userLatitude instanceof HTMLInputElement) || !(userLongitude instanceof HTMLInputElement)) {
            return;
        }

        if (!navigator.geolocation) {
            if (locationStatus instanceof HTMLElement) {
                locationStatus.textContent = 'GPS tidak tersedia pada peranti ini. Sila masukkan bandar atau negara anda secara manual.';
            }

            return;
        }

        if (locationStatus instanceof HTMLElement) {
            locationStatus.textContent = 'Meminta kebenaran lokasi…';
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = position.coords.latitude.toFixed(6);
                const longitude = position.coords.longitude.toFixed(6);

                userLatitude.value = latitude;
                userLongitude.value = longitude;
                syncTimezone();

                if (!userLocation.value.trim()) {
                    userLocation.value = `GPS: ${latitude}, ${longitude}`;
                }

                if (locationStatus instanceof HTMLElement) {
                    locationStatus.textContent = `Lokasi GPS dikesan. Zon waktu peranti: ${valueFor('user_timezone') || 'tidak dapat dikesan'}. Anda boleh memperincikan bandar atau negara di bawah.`;
                }

                syncSummary();
            },
            () => {
                if (locationStatus instanceof HTMLElement) {
                    locationStatus.textContent = 'GPS tidak dapat digunakan. Sila masukkan bandar atau negara anda secara manual.';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000,
            },
        );
    });
    enquiryForm.addEventListener('submit', (event) => {
        const invalidStep = [1, 2, 3].find((stepNumber) => !validateStep(stepNumber));

        if (invalidStep === undefined) {
            return;
        }

        event.preventDefault();
        showStep(invalidStep);
    });

    const initialService = selectedService();

    if (initialService && !selectedCategory()) {
        const categoryInput = categoryInputs.find((input) => input.value === initialService.dataset.category);

        if (categoryInput instanceof HTMLInputElement) {
            categoryInput.checked = true;
        }
    }

    syncTimezone();
    syncServiceOptions();
    syncSummary();
    syncSubmitButton();
    showStep(1, false);
};

const initializeTransportEnquiry = () => {
    const root = document.querySelector('[data-transport-enquiry]');

    if (!(root instanceof HTMLElement)) {
        return;
    }

    const form = root.querySelector('[data-transport-form]');
    const scheduledFields = root.querySelector('[data-transport-scheduled-fields]');
    const immediateNotice = root.querySelector('[data-transport-immediate-notice]');
    const mapWrap = root.querySelector('[data-transport-map-wrap]');
    const userTimezone = root.querySelector('[data-transport-user-timezone]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (userTimezone instanceof HTMLInputElement) {
        try {
            userTimezone.value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch {
            userTimezone.value = '';
        }
    }

    const field = (selector) => form.querySelector(selector);
    const valueFor = (selector) => {
        const input = field(selector);

        return input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement
            ? input.value.trim()
            : '';
    };
    const numberFor = (selector) => Math.max(0, Number(valueFor(selector)) || 0);
    const formatDate = (value) => {
        if (!value) {
            return '';
        }

        const date = new Date(`${value}T00:00:00`);

        return Number.isNaN(date.valueOf())
            ? value
            : new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
    };
    const syncRequestType = () => {
        const requestType = form.querySelector('input[name="request_type"]:checked');
        const isImmediate = requestType instanceof HTMLInputElement && requestType.value === 'immediate';

        if (scheduledFields instanceof HTMLElement) {
            scheduledFields.hidden = isImmediate;
            scheduledFields.querySelectorAll('input').forEach((input) => {
                input.disabled = isImmediate;
            });
        }

        if (immediateNotice instanceof HTMLElement) {
            immediateNotice.hidden = !isImmediate;
        }
    };
    const review = root.querySelector('[data-transport-review]');
    const steps = [...root.querySelectorAll('[data-transport-step]')].filter((step) => step instanceof HTMLElement);
    const progressButtons = [...root.querySelectorAll('[data-transport-progress-step]')].filter((button) => button instanceof HTMLButtonElement);
    let currentStep = 1;
    let completedStep = 0;

    const reviewValue = (selector, value) => {
        const destination = root.querySelector(selector);

        if (destination instanceof HTMLElement) {
            destination.textContent = value || '—';
        }
    };
    const syncSummary = () => {
        const requestType = field('input[name="request_type"]:checked');
        const isImmediate = requestType instanceof HTMLInputElement && requestType.value === 'immediate';
        const pickupDate = valueFor('[data-transport-summary-date]');
        const pickupTime = valueFor('[data-transport-summary-time]');
        const adults = numberFor('[data-transport-summary-adults]');
        const children = numberFor('[data-transport-summary-children]');
        const luggage = numberFor('[data-transport-summary-luggage]');
        const locationValue = (prefix) => [
            valueFor(`[name="${prefix}_place"]`),
            valueFor(`[name="${prefix}_address"]`),
            valueFor(`[name="${prefix}_google_maps_url"]`),
        ].filter(Boolean).join('\n');

        reviewValue('[data-transport-review-request]', isImmediate ? 'Perlu segera' : 'Jadualkan perjalanan');
        reviewValue(
            '[data-transport-review-schedule]',
            isImmediate ? 'Segera' : [formatDate(pickupDate), pickupTime].filter(Boolean).join(', '),
        );
        reviewValue('[data-transport-review-pickup]', locationValue('pickup'));
        reviewValue('[data-transport-review-destination]', locationValue('destination'));
        reviewValue('[data-transport-review-passengers]', `${adults} dewasa, ${children} kanak-kanak, ${luggage} item bagasi`);
        reviewValue('[data-transport-review-vehicle]', valueFor('[data-transport-summary-vehicle]'));
        reviewValue('[data-transport-review-instructions]', valueFor('[name="pickup_instructions"]'));
        reviewValue('[data-transport-review-name]', valueFor('[name="full_name"]'));
        reviewValue('[data-transport-review-contact]', valueFor('[name="contact_number"]'));
    };
    const stepFor = (stepNumber) => steps.find((step) => Number(step.dataset.transportStep) === stepNumber);
    const feedbackFor = (stepNumber) => root.querySelector(`[data-transport-step-feedback="${stepNumber}"]`);
    const setFeedback = (stepNumber, message = '') => {
        const feedback = feedbackFor(stepNumber);

        if (feedback instanceof HTMLElement) {
            feedback.textContent = message;
        }
    };
    const syncWizard = () => {
        steps.forEach((step) => {
            const stepNumber = Number(step.dataset.transportStep);

            step.hidden = stepNumber !== currentStep;
            step.classList.toggle('is-complete', stepNumber <= completedStep);
            step.classList.toggle('is-current', stepNumber === currentStep);
        });

        progressButtons.forEach((button) => {
            const stepNumber = Number(button.dataset.transportProgressStep);
            const isCurrent = stepNumber === currentStep;

            button.classList.toggle('is-current', isCurrent);
            button.classList.toggle('is-complete', stepNumber <= completedStep);
            button.disabled = stepNumber > completedStep + 1;

            if (isCurrent) {
                button.setAttribute('aria-current', 'step');
            } else {
                button.removeAttribute('aria-current');
            }
        });

        if (review instanceof HTMLElement) {
            review.hidden = currentStep !== 5;
        }

        if (mapWrap instanceof HTMLElement) {
            mapWrap.hidden = ![1, 2].includes(currentStep);
        }
    };
    const showStep = (stepNumber, shouldScroll = true) => {
        currentStep = stepNumber;
        syncSummary();
        syncWizard();
        root.dispatchEvent(new CustomEvent('transport:step-change', { detail: { step: stepNumber } }));

        const destination = stepNumber === 5 ? review : stepFor(stepNumber);

        if (shouldScroll && destination instanceof HTMLElement) {
            destination.scrollIntoView({ behavior: 'smooth', block: 'start' });

            if (stepNumber === 5) {
                destination.focus({ preventScroll: true });
            }
        }
    };
    const validateStep = (stepNumber) => {
        const step = stepFor(stepNumber);

        if (!(step instanceof HTMLElement)) {
            return false;
        }

        const invalidField = [...step.querySelectorAll('input, select, textarea')].find((input) => {
            if (!(input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement)) {
                return false;
            }

            return !input.disabled && input.type !== 'hidden' && !input.checkValidity();
        });

        if (invalidField instanceof HTMLInputElement || invalidField instanceof HTMLSelectElement || invalidField instanceof HTMLTextAreaElement) {
            setFeedback(stepNumber, 'Sila lengkapkan medan wajib sebelum meneruskan.');
            invalidField.reportValidity();

            return false;
        }

        if (stepNumber === 3) {
            const vehicleValue = valueFor('[data-transport-vehicle-input]');
            const vehicleOption = [...root.querySelectorAll('[data-transport-vehicle-option]')]
                .find((option) => option instanceof HTMLElement && option.dataset.value === vehicleValue);
            const vehicleCapacity = vehicleOption instanceof HTMLElement ? Number(vehicleOption.dataset.capacity) || 0 : 0;
            const passengerCount = numberFor('[data-transport-summary-adults]') + numberFor('[data-transport-summary-children]');

            if (vehicleCapacity > 0 && passengerCount > vehicleCapacity) {
                setFeedback(stepNumber, `Kenderaan ini memuatkan maksimum ${vehicleCapacity} penumpang. Pilih kenderaan yang lebih besar atau biarkan operator mencadangkan.`);

                return false;
            }
        }

        const locationKey = stepNumber === 1 ? 'destination' : stepNumber === 2 ? 'pickup' : null;

        if (locationKey !== null && valueFor(`[name="${locationKey}_confirmed"]`) !== '1') {
            setFeedback(stepNumber, `Sila sahkan ${locationKey === 'pickup' ? 'Lokasi Pickup' : 'Destinasi'} sebelum meneruskan.`);

            return false;
        }

        setFeedback(stepNumber);

        return true;
    };

    form.querySelectorAll('input[name="request_type"]').forEach((input) => {
        input.addEventListener('change', () => {
            syncRequestType();
            syncSummary();
        });
    });
    form.querySelectorAll('input, textarea, select').forEach((input) => {
        input.addEventListener('input', syncSummary);
        input.addEventListener('change', syncSummary);
    });
    form.querySelectorAll('[data-transport-counter]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const input = button.parentElement?.querySelector('input[type="number"]');

            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const currentValue = Number(input.value) || 0;
            const lowerLimit = Number(input.min) || 0;
            const upperLimit = Number(input.max) || Number.POSITIVE_INFINITY;
            const nextValue = button.dataset.transportCounter === 'increment'
                ? Math.min(upperLimit, currentValue + 1)
                : Math.max(lowerLimit, currentValue - 1);

            input.value = String(nextValue);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
    const vehiclePicker = root.querySelector('[data-transport-vehicle-picker]');

    if (vehiclePicker instanceof HTMLElement) {
        const vehicleInput = vehiclePicker.querySelector('[data-transport-vehicle-input]');
        const vehicleTrigger = vehiclePicker.querySelector('[data-transport-vehicle-trigger]');
        const vehicleOptions = vehiclePicker.querySelector('[data-transport-vehicle-options]');
        const vehiclePreview = vehiclePicker.querySelector('[data-transport-vehicle-preview]');
        const vehicleLabel = vehiclePicker.querySelector('[data-transport-vehicle-label]');
        const vehicleDescription = vehiclePicker.querySelector('[data-transport-vehicle-description]');
        const vehicleButtons = [...vehiclePicker.querySelectorAll('[data-transport-vehicle-option]')]
            .filter((button) => button instanceof HTMLButtonElement);

        const closeVehiclePicker = () => {
            if (vehicleOptions instanceof HTMLElement) {
                vehicleOptions.hidden = true;
            }

            if (vehicleTrigger instanceof HTMLButtonElement) {
                vehicleTrigger.setAttribute('aria-expanded', 'false');
            }
        };
        const selectVehicle = (button) => {
            if (!(vehicleInput instanceof HTMLInputElement)) {
                return;
            }

            const value = button.dataset.value ?? '';
            const label = button.dataset.label ?? value;
            const description = button.dataset.description ?? '';
            const image = button.dataset.image ?? '';

            vehicleInput.value = value;
            vehicleButtons.forEach((option) => option.setAttribute('aria-selected', String(option === button)));

            if (vehicleLabel instanceof HTMLElement) {
                vehicleLabel.textContent = label;
            }

            if (vehicleDescription instanceof HTMLElement) {
                vehicleDescription.textContent = description;
            }

            if (vehiclePreview instanceof HTMLElement && image) {
                const previewImage = document.createElement('img');
                previewImage.src = image;
                previewImage.alt = '';
                vehiclePreview.replaceChildren(previewImage);
            }

            vehicleInput.dispatchEvent(new Event('input', { bubbles: true }));
            closeVehiclePicker();
        };

        if (vehicleTrigger instanceof HTMLButtonElement && vehicleOptions instanceof HTMLElement) {
            vehicleTrigger.addEventListener('click', () => {
                const willOpen = vehicleOptions.hidden;
                vehicleOptions.hidden = !willOpen;
                vehicleTrigger.setAttribute('aria-expanded', String(willOpen));
            });
        }

        vehicleButtons.forEach((button) => button.addEventListener('click', () => selectVehicle(button)));

        if (vehicleInput instanceof HTMLInputElement && vehicleInput.value) {
            const selectedButton = vehicleButtons.find((button) => button.dataset.value === vehicleInput.value);

            if (selectedButton) {
                selectVehicle(selectedButton);
            }
        }
    }
    root.querySelectorAll('[data-transport-next-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const step = button.closest('[data-transport-step]');
            const currentStepNumber = Number(step?.dataset.transportStep);
            const nextStep = Number(button.dataset.transportNextStep);

            if (!validateStep(currentStepNumber)) {
                return;
            }

            completedStep = Math.max(completedStep, currentStepNumber);
            showStep(nextStep);
        });
    });
    progressButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = Number(button.dataset.transportProgressStep);

            if (target <= completedStep + 1) {
                showStep(target);
            }
        });
    });
    form.addEventListener('submit', (event) => {
        if (currentStep !== 5) {
            event.preventDefault();

            if (validateStep(currentStep)) {
                completedStep = Math.max(completedStep, currentStep);
                showStep(currentStep + 1);
            }

            return;
        }

        const invalidStep = [1, 2, 3, 4].find((stepNumber) => !validateStep(stepNumber));

        if (invalidStep === undefined) {
            return;
        }

        event.preventDefault();
        showStep(invalidStep);
    });

    const acknowledgement = root.querySelector('[data-transport-info-acknowledgement]');
    const submitButton = root.querySelector('[data-transport-submit]');
    const syncSubmitButton = () => {
        if (!(submitButton instanceof HTMLButtonElement)) {
            return;
        }

        const isAcknowledged = acknowledgement instanceof HTMLInputElement && acknowledgement.checked;
        submitButton.disabled = !isAcknowledged;
        submitButton.setAttribute('aria-disabled', String(!isAcknowledged));
    };

    if (acknowledgement instanceof HTMLInputElement) {
        acknowledgement.addEventListener('change', syncSubmitButton);
    }

    syncRequestType();
    syncSummary();
    syncSubmitButton();
    showStep(1, false);

    const mapElement = root.querySelector('[data-transport-map]');
    const mapStatus = root.querySelector('[data-transport-map-status]');
    const leaflet = mapElement instanceof HTMLElement && 'L' in window ? window.L : null;
    const map = leaflet && mapElement instanceof HTMLElement
        ? leaflet.map(mapElement, {
            scrollWheelZoom: false,
        }).setView([20, 0], 2)
        : null;

    if (map && leaflet) {
        leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);
    } else if (mapStatus instanceof HTMLElement) {
        mapStatus.textContent = 'Peta tidak tersedia. Anda masih boleh mencari lokasi manual atau memasukkan pautan Google Maps, kemudian sahkan lokasi.';
    }

    if (!map && !(mapStatus instanceof HTMLElement)) {
        return;
    }

    if (!map) {
        if (mapStatus instanceof HTMLElement) {
            mapStatus.textContent = 'Peta tidak tersedia. Anda masih boleh mencari lokasi manual atau memasukkan pautan Google Maps, kemudian sahkan lokasi.';
        }
    }

    const locations = new Map();
    let activeKey = 'destination';

    const hasCoordinates = (location) => {
        if (!location.latitude.value.trim() || !location.longitude.value.trim()) {
            return false;
        }

        const latitude = Number(location.latitude.value);
        const longitude = Number(location.longitude.value);

        return Number.isFinite(latitude) && Number.isFinite(longitude)
            && latitude >= -90 && latitude <= 90
            && longitude >= -180 && longitude <= 180;
    };

    const labelFor = (key) => key === 'pickup' ? 'Pickup' : 'Destinasi';

    const fitMarkers = () => {
        if (!map || !leaflet) {
            return;
        }

        const markers = [...locations.values()]
            .map((location) => location.marker)
            .filter(Boolean);

        if (markers.length === 1) {
            map.setView(markers[0].getLatLng(), 14);
        }

        if (markers.length > 1) {
            map.fitBounds(leaflet.featureGroup(markers).getBounds().pad(0.25));
        }
    };

    const setCoordinates = (location, latitude, longitude, status) => {
        location.latitude.value = Number(latitude).toFixed(6);
        location.longitude.value = Number(longitude).toFixed(6);
        location.confirmed.value = '';
        location.status.textContent = status;

        if (!map || !leaflet) {
            return;
        }

        const latLng = leaflet.latLng(latitude, longitude);

        if (!location.marker) {
            location.marker = leaflet.marker(latLng, {
                draggable: true,
                title: labelFor(location.key),
            }).addTo(map);
            location.marker.bindTooltip(labelFor(location.key), { permanent: true, direction: 'top' });
            location.marker.on('dragend', () => {
                const position = location.marker.getLatLng();
                setCoordinates(location, position.lat, position.lng, `${labelFor(location.key)} diubah. Sahkan lokasi untuk meneruskan.`);
            });
        } else {
            location.marker.setLatLng(latLng);
        }

        fitMarkers();
    };

    if (map) {
        root.addEventListener('transport:step-change', (event) => {
            if (event instanceof CustomEvent && [1, 2].includes(event.detail?.step)) {
                map.invalidateSize({ animate: false });
            }
        });
    }

    root.querySelectorAll('[data-transport-location]').forEach((element) => {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        const key = element.dataset.locationKey;
        const place = element.querySelector('[data-location-place]');
        const latitude = element.querySelector('[data-location-latitude]');
        const longitude = element.querySelector('[data-location-longitude]');
        const link = element.querySelector('[data-location-link]');
        const confirmed = element.querySelector('[data-location-confirmed]');
        const status = element.querySelector('[data-location-status]');

        if (key === undefined || !(place instanceof HTMLInputElement) || !(latitude instanceof HTMLInputElement) || !(longitude instanceof HTMLInputElement) || !(link instanceof HTMLInputElement) || !(confirmed instanceof HTMLInputElement) || !(status instanceof HTMLElement)) {
            return;
        }

        const location = { key, place, latitude, longitude, link, confirmed, status, marker: null };
        locations.set(key, location);

        if (hasCoordinates(location)) {
            setCoordinates(location, Number(latitude.value), Number(longitude.value), `${labelFor(key)} sedia untuk disahkan.`);
        }

        element.querySelector('[data-location-select]')?.addEventListener('click', () => {
            if (!map) {
                status.textContent = 'Peta tidak tersedia. Masukkan lokasi manual atau pautan Google Maps, kemudian sahkan lokasi.';

                return;
            }

            activeKey = key;
            status.textContent = `Tekan atau seret pin ${labelFor(key)} pada peta, kemudian sahkan lokasi.`;
        });

        element.querySelector('[data-location-open-search]')?.addEventListener('click', () => {
            const query = place.value.trim();

            if (!query) {
                status.textContent = 'Masukkan nama atau alamat lokasi dahulu.';

                return;
            }

            window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`, '_blank', 'noopener,noreferrer');
        });

        element.querySelector('[data-location-apply-link]')?.addEventListener('click', () => {
            const url = link.value.trim();
            let parsed;

            try {
                parsed = new URL(url);
            } catch {
                status.textContent = 'Pautan tidak dapat dibaca. Ia akan diperiksa semasa borang dihantar.';

                return;
            }

            const isGoogleMaps = parsed.protocol === 'https:'
                && ['google.com', 'www.google.com', 'maps.google.com', 'maps.app.goo.gl'].includes(parsed.hostname);
            const query = parsed.searchParams.get('query') ?? parsed.searchParams.get('q') ?? '';
            const coordinates = parsed.pathname.match(/@(-?\d{1,2}(?:\.\d+)?),(-?\d{1,3}(?:\.\d+)?)/)
                ?? parsed.pathname.match(/!3d(-?\d{1,2}(?:\.\d+)?)!4d(-?\d{1,3}(?:\.\d+)?)/)
                ?? parsed.pathname.match(/(?:place|search)\/(-?\d{1,2}(?:\.\d+)?),(-?\d{1,3}(?:\.\d+)?)/)
                ?? query.match(/^\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$/);

            if (!isGoogleMaps) {
                status.textContent = 'Gunakan pautan Google Maps HTTPS sahaja.';

                return;
            }

            if (!coordinates) {
                status.textContent = 'Pautan disimpan sebagai rujukan. Sila pilih atau laraskan pin secara manual sebelum mengesahkan.';

                return;
            }

            setCoordinates(location, Number(coordinates[1]), Number(coordinates[2]), 'Koordinat pautan dibaca. Semak dan sahkan pin sebelum meneruskan.');
        });

        element.querySelector('[data-location-confirm]')?.addEventListener('click', () => {
            if (!place.value.trim()) {
                status.textContent = `Masukkan nama atau alamat ${labelFor(key)} sebelum mengesahkan.`;

                return;
            }

            confirmed.value = '1';
            status.textContent = `${labelFor(key)} telah disahkan oleh anda.`;
        });

        element.querySelector('[data-location-use-gps]')?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                status.textContent = 'GPS tidak tersedia pada peranti ini. Gunakan carian manual, pin atau pautan Google Maps.';

                return;
            }

            status.textContent = 'Meminta kebenaran lokasi…';
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    if (!place.value.trim()) {
                        place.value = 'Lokasi semasa';
                    }

                    setCoordinates(location, position.coords.latitude, position.coords.longitude, 'Lokasi GPS dikesan. Semak atau seret pin sebelum mengesahkan.');
                },
                () => {
                    status.textContent = 'GPS tidak dapat digunakan. Anda masih boleh mencari lokasi secara manual, mengalihkan pin atau memasukkan pautan Google Maps.';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000,
                },
            );
        });
    });

    if (map) {
        map.on('click', (event) => {
            const location = locations.get(activeKey);

            if (!location) {
                return;
            }

            setCoordinates(location, event.latlng.lat, event.latlng.lng, `Pin ${labelFor(activeKey)} diletakkan. Sahkan lokasi untuk meneruskan.`);
        });
    }
};

initializePasswordToggles();
initializePackagePosterLightbox();
initializeHotelLightbox();
initializeAqiqahKorbanForms();
window.addEventListener('load', initializeTransportEnquiry);
