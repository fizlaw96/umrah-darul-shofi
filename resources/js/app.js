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

const initializeAdminNavigation = () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const backdrop = document.querySelector('[data-admin-backdrop]');
    const toggle = document.querySelector('[data-admin-menu-toggle]');
    const closeButton = document.querySelector('[data-admin-menu-close]');

    if (!sidebar || !backdrop || !toggle) {
        return;
    }

    const close = () => {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
    };

    toggle.addEventListener('click', open);
    closeButton?.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
};

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

const initializeBulkSelection = () => {
    const selectAll = document.querySelector('[data-select-all]');
    const selections = [...document.querySelectorAll('[data-package-selection]')];
    const toolbar = document.querySelector('[data-bulk-toolbar]');
    const count = document.querySelector('[data-bulk-count]');
    const clearButton = document.querySelector('[data-clear-selection]');

    if (!(selectAll instanceof HTMLInputElement) || !toolbar || !count) {
        return;
    }

    const update = () => {
        const selected = selections.filter((selection) => selection.checked);

        selectAll.checked = selected.length === selections.length && selections.length > 0;
        selectAll.indeterminate = selected.length > 0 && selected.length < selections.length;
        count.textContent = String(selected.length);
        toolbar.hidden = selected.length === 0;
    };

    selectAll.addEventListener('change', () => {
        selections.forEach((selection) => {
            selection.checked = selectAll.checked;
        });
        update();
    });

    selections.forEach((selection) => selection.addEventListener('change', update));
    clearButton?.addEventListener('click', () => {
        selections.forEach((selection) => {
            selection.checked = false;
        });
        update();
    });
};

const initializeAdminTabs = () => {
    document.querySelectorAll('[data-admin-tabs]').forEach((container) => {
        const triggers = [...container.querySelectorAll('[data-admin-tab-trigger]')];
        const panels = [...container.querySelectorAll('[data-admin-tab-panel]')];
        const tabInput = container.querySelector('[data-admin-tab-input]');

        if (triggers.length === 0 || panels.length === 0) {
            return;
        }

        const availableTabs = new Set(triggers.map((trigger) => trigger.dataset.adminTabTrigger));
        const tabFromUrl = new URLSearchParams(window.location.search).get('tab');
        let activeTab = tabFromUrl ?? container.dataset.defaultTab ?? triggers[0].dataset.adminTabTrigger;

        if (!availableTabs.has(activeTab)) {
            activeTab = triggers[0].dataset.adminTabTrigger;
        }

        const showTab = (tab) => {
            triggers.forEach((trigger) => {
                const isActive = trigger.dataset.adminTabTrigger === tab;

                trigger.setAttribute('aria-selected', String(isActive));
                trigger.classList.toggle('border-[#007c68]', isActive);
                trigger.classList.toggle('text-[#006b57]', isActive);
                trigger.classList.toggle('border-transparent', !isActive);
                trigger.classList.toggle('text-[#64716a]', !isActive);
            });
            panels.forEach((panel) => {
                const isActive = panel.dataset.adminTabPanel === tab;

                panel.hidden = !isActive;
                panel.classList.toggle('hidden', !isActive);
            });

            if (tabInput instanceof HTMLInputElement) {
                tabInput.value = tab;
            }
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => showTab(trigger.dataset.adminTabTrigger));
        });

        showTab(activeTab);
    });
};

const createPackageCollectionRow = (collection, index, values = {}) => {
    const template = collection.querySelector('[data-collection-template]');

    if (!(template instanceof HTMLTemplateElement)) {
        return null;
    }

    const content = template.content.cloneNode(true);
    const row = content.querySelector('[data-collection-row]');

    if (!(row instanceof HTMLElement)) {
        return null;
    }

    row.querySelectorAll('[name]').forEach((field) => {
        if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
            field.name = field.name.replace('__INDEX__', String(index));
        }
    });

    row.querySelectorAll('[data-collection-field]').forEach((field) => {
        if (!(field instanceof HTMLInputElement) && !(field instanceof HTMLSelectElement) && !(field instanceof HTMLTextAreaElement)) {
            return;
        }

        const value = values[field.dataset.collectionField ?? ''];

        if (typeof value === 'string' || typeof value === 'number') {
            field.value = String(value);
        }
    });

    if (collection.dataset.packageCollection === 'hotel_options') {
        const key = row.querySelector('[data-hotel-key]');

        if (key instanceof HTMLInputElement && key.value === '') {
            key.value = typeof crypto.randomUUID === 'function'
                ? crypto.randomUUID()
                : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
                    const random = Math.floor(Math.random() * 16);

                    return (character === 'x' ? random : (random & 0x3) | 0x8).toString(16);
                });
        }
    }

    return row;
};

const initializePackageForm = () => {
    const form = document.querySelector('[data-package-form]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const editor = form.querySelector('[data-rich-editor]');
    const editorInput = form.querySelector('[data-rich-editor-input]');
    let isDirty = false;
    let isSubmitting = false;

    form.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('click', () => {
            editor?.focus();
            document.execCommand(button.dataset.editorCommand ?? '', false);
            isDirty = true;
        });
    });

    form.addEventListener('change', (event) => {
        const selection = event.target;

        if (!(selection instanceof HTMLInputElement) || !selection.matches('[data-package-service-status]')) {
            return;
        }

        if (selection.checked) {
            selection.closest('[data-package-service-row]')
                ?.querySelectorAll('[data-package-service-status]')
                .forEach((otherSelection) => {
                    if (otherSelection instanceof HTMLInputElement && otherSelection !== selection) {
                        otherSelection.checked = false;
                    }
                });
        }

        isDirty = true;
        form.dispatchEvent(new Event('input', { bubbles: true }));
    });

    form.querySelectorAll('[data-package-collection]').forEach((collection) => {
        if (!(collection instanceof HTMLElement)) {
            return;
        }

        const list = collection.querySelector('[data-collection-items]');
        const addButton = collection.querySelector('[data-collection-add]');
        let nextIndex = Number(collection.dataset.collectionNextIndex ?? list?.children.length ?? 0);

        addButton?.addEventListener('click', () => {
            if (!list) {
                return;
            }

            const row = createPackageCollectionRow(collection, nextIndex++);

            if (!row) {
                return;
            }

            list.append(row);
            row.querySelector('[data-package-service-name], input, textarea, select')?.focus();
            isDirty = true;
            form.dispatchEvent(new Event('input', { bubbles: true }));
        });

        collection.addEventListener('click', (event) => {
            const removeExistingImageButton = event.target.closest('[data-collection-existing-image-remove]');

            if (removeExistingImageButton) {
                const currentImage = removeExistingImageButton.closest('[data-collection-existing-image]');
                const existingImageInput = currentImage?.querySelector('input[type="hidden"]');

                if (existingImageInput instanceof HTMLInputElement && existingImageInput.name) {
                    const isMultipleImages = existingImageInput.name.includes('[existing_images][]');
                    const removal = document.createElement('input');
                    removal.type = 'hidden';
                    removal.name = existingImageInput.name
                        .replace('[existing_images][]', '[removed_existing_images][]')
                        .replace('[existing_image]', '[remove_existing_image]');
                    removal.value = isMultipleImages ? existingImageInput.value : '1';
                    form.append(removal);
                }

                currentImage?.remove();
                isDirty = true;
                form.dispatchEvent(new Event('input', { bubbles: true }));

                return;
            }

            const removeButton = event.target.closest('[data-collection-remove]');

            if (removeButton) {
                removeButton.closest('[data-collection-row]')?.remove();
                isDirty = true;
                form.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        collection.addEventListener('change', (event) => {
            const input = event.target;

            if (!(input instanceof HTMLInputElement) || !input.matches('[data-collection-image-input]')) {
                return;
            }

            const preview = input.closest('[data-collection-row]')?.querySelector('[data-collection-image-preview]');

            if (!preview) {
                return;
            }

            preview.replaceChildren(...[...(input.files ?? [])].map((file) => {
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = '';
                image.className = 'aspect-square w-full rounded-lg object-cover';

                return image;
            }));
            isDirty = true;
        });
    });

    form.querySelectorAll('[data-image-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const [file] = input.files ?? [];
            const preview = form.querySelector(input.dataset.previewTarget ?? '');

            if (!file || !preview) {
                return;
            }

            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = '';
            image.className = 'size-full object-cover';
            preview.replaceChildren(image);
            isDirty = true;
        });
    });

    form.querySelectorAll('[data-gallery-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const previewList = form.querySelector('[data-gallery-preview-list]');

            if (!previewList) {
                return;
            }

            previewList.replaceChildren(...[...(input.files ?? [])].map((file) => {
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = '';
                image.className = 'aspect-square w-full rounded-lg object-cover';

                return image;
            }));
            isDirty = true;
        });
    });

    form.addEventListener('input', () => {
        isDirty = true;
    });

    form.addEventListener('submit', () => {
        if (editor && editorInput instanceof HTMLInputElement) {
            editorInput.value = editor.innerHTML;
        }
        isSubmitting = true;
    });

    window.addEventListener('beforeunload', (event) => {
        if (isDirty && !isSubmitting) {
            event.preventDefault();
        }
    });
};

const initializeMediaUpload = () => {
    document.querySelectorAll('[data-media-upload-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const input = form.querySelector('[data-media-file-input]');
        const dropzone = form.querySelector('[data-media-dropzone]');
        const preview = form.querySelector('[data-media-upload-preview]');

        if (!(input instanceof HTMLInputElement) || !(dropzone instanceof HTMLElement) || !(preview instanceof HTMLElement)) {
            return;
        }

        const showPreviews = (files) => {
            const selectedFiles = [...files];

            preview.replaceChildren(...selectedFiles.map((file) => {
                const item = document.createElement('figure');
                const image = document.createElement('img');
                const caption = document.createElement('figcaption');

                item.className = 'overflow-hidden rounded-xl border border-[#dce7df] bg-white';
                image.src = URL.createObjectURL(file);
                image.alt = file.name;
                image.className = 'aspect-[4/3] w-full object-cover';
                caption.className = 'truncate px-3 py-2 text-xs font-medium text-[#52645b]';
                caption.textContent = file.name;
                item.append(image, caption);

                return item;
            }));
            preview.hidden = selectedFiles.length === 0;
            preview.classList.toggle('hidden', selectedFiles.length === 0);
        };

        const assignFiles = (files) => {
            if (typeof DataTransfer === 'undefined') {
                return;
            }

            const transfer = new DataTransfer();

            [...files].forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            showPreviews(transfer.files);
        };

        input.addEventListener('change', () => showPreviews(input.files ?? []));
        dropzone.addEventListener('click', () => input.click());
        dropzone.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                input.click();
            }
        });
        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('border-[#006b57]', 'bg-[#eff9f1]');
            });
        });
        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('border-[#006b57]', 'bg-[#eff9f1]');
            });
        });
        dropzone.addEventListener('drop', (event) => assignFiles(event.dataTransfer?.files ?? []));
    });
};


const initializePackageAiImport = () => {
    const form = document.querySelector('[data-package-form]');
    const assistant = form?.querySelector('[data-ai-import-assistant]');
    const dialog = assistant?.querySelector('[data-ai-import-dialog]');
    const openButton = assistant?.querySelector('[data-ai-import-open]');
    const prompt = dialog?.querySelector('[data-ai-import-prompt]');
    const copyPromptButton = dialog?.querySelector('[data-ai-import-copy-prompt]');
    const response = dialog?.querySelector('[data-ai-import-response]');
    const syncButton = dialog?.querySelector('[data-ai-import-sync]');
    const status = dialog?.querySelector('[data-ai-import-status]');
    const errors = dialog?.querySelector('[data-ai-import-errors]');
    const errorList = dialog?.querySelector('[data-ai-import-error-list]');

    if (!(form instanceof HTMLFormElement)
        || !(dialog instanceof HTMLDialogElement)
        || !(openButton instanceof HTMLButtonElement)
        || !(prompt instanceof HTMLTextAreaElement)
        || !(copyPromptButton instanceof HTMLButtonElement)
        || !(response instanceof HTMLTextAreaElement)
        || !(syncButton instanceof HTMLButtonElement)
        || !(status instanceof HTMLElement)
        || !(errors instanceof HTMLElement)
        || !(errorList instanceof HTMLElement)) {
        return;
    }

    const text = (value) => String(value ?? '').replace(/\s+/g, ' ').trim();
    const normalise = (value) => text(value).toLocaleLowerCase();
    const asObject = (value) => value !== null && typeof value === 'object' && !Array.isArray(value);
    const valueOrNull = (value) => {
        if (value === null || value === undefined) {
            return null;
        }

        if (typeof value === 'string' || typeof value === 'number') {
            return String(value).trim();
        }

        return undefined;
    };

    const setStatus = (message) => {
        status.textContent = message;
    };

    const showErrors = (messages) => {
        errorList.replaceChildren(...messages.map((message) => {
            const item = document.createElement('li');
            item.textContent = message;

            return item;
        }));
        errors.classList.toggle('hidden', messages.length === 0);
    };

    const notifyFormChanged = () => {
        form.dispatchEvent(new Event('input', { bubbles: true }));
        form.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const fieldForName = (name) => {
        const field = form.elements.namedItem(name);

        return field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement
            ? field
            : null;
    };

    const setField = (field, value) => {
        if (value === null) {
            return true;
        }

        if (value === undefined) {
            return false;
        }

        if (field instanceof HTMLSelectElement) {
            const option = [...field.options].find((item) => item.value === value);

            if (!option) {
                return false;
            }
        }

        field.value = value;

        return true;
    };

    const setFormField = (name, value, label, validationErrors) => {
        if (value === null) {
            return;
        }

        if (value === undefined) {
            validationErrors.push(label + ' mesti berupa teks atau nombor.');

            return;
        }

        const field = fieldForName(name);

        if (!field || !setField(field, value)) {
            validationErrors.push(label + ' tidak sah untuk borang ini.');
        }
    };

    const collectionFor = (name) => [...form.querySelectorAll('[data-package-collection]')]
        .find((collection) => collection.dataset.packageCollection === name);

    const appendCollectionRow = (collection) => {
        const items = collection.querySelector('[data-collection-items]');
        const addButton = collection.querySelector('[data-collection-add]');

        if (!(items instanceof HTMLElement) || !(addButton instanceof HTMLButtonElement)) {
            return null;
        }

        items.querySelectorAll(':scope > p').forEach((item) => item.remove());
        addButton.click();

        const rows = items.querySelectorAll('[data-collection-row]');

        return rows.length > 0 ? rows[rows.length - 1] : null;
    };

    const rowField = (row, name) => row.querySelector('[name$="[' + name + ']"]');

    const fillCollection = (name, value, fields, label, validationErrors) => {
        if (value === null || value === undefined) {
            return;
        }

        if (!Array.isArray(value)) {
            validationErrors.push(label + ' mesti berupa senarai.');

            return;
        }

        const collection = collectionFor(name);

        if (!(collection instanceof HTMLElement)) {
            validationErrors.push(label + ' tidak ditemui dalam borang.');

            return;
        }

        const rows = [...collection.querySelectorAll('[data-collection-row]')];

        value.forEach((rowValues, index) => {
            if (!asObject(rowValues)) {
                validationErrors.push(label + ' item ' + (index + 1) + ' mesti berupa objek.');

                return;
            }

            const row = rows[index] ?? appendCollectionRow(collection);

            if (!(row instanceof HTMLElement)) {
                validationErrors.push('Tidak dapat menambah ' + label.toLocaleLowerCase() + '.');

                return;
            }

            rows[index] = row;
            fields.forEach((fieldName) => {
                const valueToSet = valueOrNull(rowValues[fieldName]);

                if (valueToSet === null || rowValues[fieldName] === undefined) {
                    return;
                }

                if (valueToSet === undefined) {
                    validationErrors.push(label + ' item ' + (index + 1) + ': ' + fieldName + ' mesti berupa teks atau nombor.');

                    return;
                }

                const field = rowField(row, fieldName);

                if (!(field instanceof HTMLInputElement) && !(field instanceof HTMLSelectElement) && !(field instanceof HTMLTextAreaElement)) {
                    validationErrors.push(label + ' item ' + (index + 1) + ': medan ' + fieldName + ' tidak ditemui.');

                    return;
                }

                if (!setField(field, valueToSet)) {
                    validationErrors.push(label + ' item ' + (index + 1) + ': nilai ' + fieldName + ' tidak sah.');
                }
            });
        });
    };

    const fillRoomPrices = (value, validationErrors) => {
        if (value === null || value === undefined) {
            return;
        }

        if (!Array.isArray(value)) {
            validationErrors.push('Harga bilik mesti berupa senarai.');

            return;
        }

        const invalidRoom = value.find((room) => !asObject(room)
            || valueOrNull(room.hotel_name) === null
            || valueOrNull(room.hotel_name) === undefined
            || valueOrNull(room.room_type) === null
            || valueOrNull(room.room_type) === undefined
            || valueOrNull(room.price) === null
            || valueOrNull(room.price) === undefined);

        if (invalidRoom) {
            validationErrors.push('Setiap harga bilik mesti mempunyai hotel_name, room_type dan price.');

            return;
        }

        fillCollection('room_prices', value, ['room_type', 'price'], 'Harga bilik', validationErrors);
        form.dispatchEvent(new Event('input', { bubbles: true }));

        const hotelCollection = collectionFor('hotel_options');
        const roomCollection = collectionFor('room_prices');

        if (!(hotelCollection instanceof HTMLElement) || !(roomCollection instanceof HTMLElement)) {
            validationErrors.push('Hotel atau harga bilik tidak ditemui dalam borang.');

            return;
        }

        const hotelRows = [...hotelCollection.querySelectorAll('[data-collection-row]')];
        const roomRows = [...roomCollection.querySelectorAll('[data-collection-row]')];

        value.forEach((room, index) => {
            const hotel = hotelRows.find((row) => normalise(rowField(row, 'name')?.value) === normalise(room.hotel_name));
            const hotelKey = hotel?.querySelector('[data-hotel-key]');
            const roomHotel = roomRows[index] && rowField(roomRows[index], 'hotel_key');

            if (!(hotelKey instanceof HTMLInputElement) || !(roomHotel instanceof HTMLSelectElement) || !setField(roomHotel, hotelKey.value)) {
                validationErrors.push('Harga bilik item ' + (index + 1) + ': hotel_name tidak sepadan dengan senarai hotel.');
            }
        });
    };

    const fillItinerary = (value, validationErrors) => {
        if (value === null || value === undefined) {
            return;
        }

        if (!Array.isArray(value)) {
            validationErrors.push('Itinerari mesti berupa senarai.');

            return;
        }

        const itineraryList = form.querySelector('[data-itinerary-days]');

        if (!(itineraryList instanceof HTMLElement)) {
            validationErrors.push('Itinerari tidak ditemui dalam borang.');

            return;
        }

        const rows = [...itineraryList.querySelectorAll('article')];

        value.forEach((rowValues, index) => {
            if (!asObject(rowValues)) {
                validationErrors.push('Itinerari item ' + (index + 1) + ' mesti berupa objek.');

                return;
            }

            const row = rows[index];

            if (!(row instanceof HTMLElement)) {
                validationErrors.push('Masukkan duration_days yang mencukupi untuk itinerari item ' + (index + 1) + '.');

                return;
            }

            ['title', 'description'].forEach((fieldName) => {
                const valueToSet = valueOrNull(rowValues[fieldName]);

                if (valueToSet === null || rowValues[fieldName] === undefined) {
                    return;
                }

                if (valueToSet === undefined) {
                    validationErrors.push('Itinerari item ' + (index + 1) + ': ' + fieldName + ' mesti berupa teks atau nombor.');

                    return;
                }

                const field = rowField(row, fieldName);

                if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                    field.value = valueToSet;
                }
            });
        });
    };

    const applyServices = (value, serviceStatus, label, validationErrors) => {
        if (value === null || value === undefined) {
            return;
        }

        if (!Array.isArray(value)) {
            validationErrors.push(label + ' mesti berupa senarai.');

            return;
        }

        const collection = collectionFor('package_services');

        if (!(collection instanceof HTMLElement)) {
            validationErrors.push('Perkhidmatan tidak ditemui dalam borang.');

            return;
        }

        const serviceNames = value
            .map((name) => valueOrNull(name))
            .filter((name) => name !== null && name !== undefined && name !== '');

        if (serviceNames.length !== value.length) {
            validationErrors.push(label + ' hanya boleh mengandungi nama perkhidmatan.');

            return;
        }

        serviceNames.forEach((serviceName) => {
            let row = [...collection.querySelectorAll('[data-package-service-row]')]
                .find((item) => normalise(item.querySelector('[data-package-service-name]')?.value) === normalise(serviceName));

            if (!(row instanceof HTMLElement)) {
                row = appendCollectionRow(collection);

                const nameField = row?.querySelector('[data-package-service-name]');

                if (!(row instanceof HTMLElement) || !(nameField instanceof HTMLInputElement)) {
                    validationErrors.push('Tidak dapat menambah perkhidmatan ' + serviceName + '.');

                    return;
                }

                nameField.value = serviceName;
            }

            const selection = row.querySelector('[data-package-service-status="' + serviceStatus + '"]');

            if (!(selection instanceof HTMLInputElement)) {
                validationErrors.push('Tidak dapat menetapkan perkhidmatan ' + serviceName + '.');

                return;
            }

            selection.checked = true;
            row.querySelectorAll('[data-package-service-status]').forEach((otherSelection) => {
                if (otherSelection instanceof HTMLInputElement && otherSelection !== selection) {
                    otherSelection.checked = false;
                }
            });
        });
    };

    const parseResponse = () => {
        const raw = response.value.trim();

        if (raw === '') {
            throw new Error('Tampal jawapan JSON daripada AI terlebih dahulu.');
        }

        const fence = String.fromCharCode(96).repeat(3);
        const firstLineBreak = raw.indexOf('\n');
        const parsedText = raw.startsWith(fence) && firstLineBreak !== -1 && raw.lastIndexOf(fence) > firstLineBreak
            ? raw.slice(firstLineBreak + 1, raw.lastIndexOf(fence)).trim()
            : raw;
        const parsed = JSON.parse(parsedText);

        if (!asObject(parsed)) {
            throw new Error('Jawapan AI mesti mengandungi satu objek JSON.');
        }

        return parsed;
    };

    const syncResponse = () => {
        let data;

        try {
            data = parseResponse();
        } catch (error) {
            showErrors([error instanceof Error ? error.message : 'Jawapan AI tidak dapat dibaca.']);
            setStatus('');

            return;
        }

        const validationErrors = [];
        const includedServices = Array.isArray(data.included_services)
            ? data.included_services.map(normalise).filter(Boolean)
            : [];
        const excludedServices = Array.isArray(data.excluded_services)
            ? data.excluded_services.map(normalise).filter(Boolean)
            : [];
        const duplicateService = includedServices.find((service) => excludedServices.includes(service));

        if (duplicateService) {
            validationErrors.push('Perkhidmatan "' + duplicateService + '" tidak boleh termasuk dan tidak termasuk pada masa yang sama.');
        }

        if (validationErrors.length > 0) {
            showErrors(validationErrors);
            setStatus('');

            return;
        }

        setFormField('title', valueOrNull(data.title), 'Nama pakej', validationErrors);
        setFormField('season_label', valueOrNull(data.season_label), 'Musim', validationErrors);
        setFormField('starting_price', valueOrNull(data.starting_price), 'Harga bermula', validationErrors);
        setFormField('duration_days', valueOrNull(data.duration_days), 'Tempoh hari', validationErrors);
        setFormField('duration_nights', valueOrNull(data.duration_nights), 'Tempoh malam', validationErrors);

        const categoryName = valueOrNull(data.package_category);

        if (categoryName !== null) {
            if (categoryName === undefined) {
                validationErrors.push('Kategori pakej mesti berupa teks.');
            } else {
                const category = fieldForName('package_category_id');

                if (!(category instanceof HTMLSelectElement)) {
                    validationErrors.push('Kategori pakej tidak ditemui dalam borang.');
                } else {
                    const option = [...category.options].find((item) => normalise(item.text) === normalise(categoryName));

                    if (!option) {
                        validationErrors.push('Kategori pakej "' + categoryName + '" tidak sah.');
                    } else {
                        category.value = option.value;
                    }
                }
            }
        }

        const description = valueOrNull(data.description);

        if (description === undefined) {
            validationErrors.push('Penerangan mesti berupa teks.');
        } else if (description !== null) {
            const editor = form.querySelector('[data-rich-editor]');
            const editorInput = form.querySelector('[data-rich-editor-input]');

            if (editor instanceof HTMLElement && editorInput instanceof HTMLInputElement) {
                editor.textContent = description;
                editorInput.value = editor.innerHTML;
            }
        }

        const duration = fieldForName('duration_days');

        if (duration instanceof HTMLInputElement && valueOrNull(data.duration_days) !== null) {
            duration.dispatchEvent(new Event('change', { bubbles: true }));
        }

        fillCollection('departure_rows', data.departure_rows, ['departure_date', 'return_date', 'departure_airport', 'price', 'capacity', 'status'], 'Tarikh berlepas', validationErrors);
        fillCollection('flight_options', data.flight_options, ['airline', 'departure_airport', 'arrival_airport', 'proposed_departure_date', 'notes'], 'Penerbangan', validationErrors);
        fillCollection('hotel_options', data.hotel_options, ['location', 'name', 'rating', 'nights', 'walking_distance', 'notes'], 'Hotel', validationErrors);
        fillItinerary(data.itinerary_days, validationErrors);
        fillRoomPrices(data.room_prices, validationErrors);
        fillCollection('ziarah_places', data.ziarah_places, ['name', 'location', 'description'], 'Tempat ziarah', validationErrors);
        fillCollection('gifts', data.gifts, ['title', 'description'], 'Hadiah jemaah', validationErrors);
        applyServices(data.included_services, 'included', 'Perkhidmatan termasuk', validationErrors);
        applyServices(data.excluded_services, 'excluded', 'Perkhidmatan tidak termasuk', validationErrors);

        ['is_featured'].forEach((name) => {
            if (data[name] === null || data[name] === undefined) {
                return;
            }

            const field = fieldForName(name);

            if (!(field instanceof HTMLInputElement) || typeof data[name] !== 'boolean') {
                validationErrors.push(name + ' mesti berupa true atau false.');

                return;
            }

            field.checked = data[name];
        });

        if (validationErrors.length > 0) {
            showErrors(validationErrors);
            setStatus('');

            return;
        }

        showErrors([]);
        notifyFormChanged();
        setStatus('Data telah diselaraskan ke borang. Sila semak semua maklumat sebelum menyimpan.');
    };

    openButton.addEventListener('click', () => {
        showErrors([]);
        setStatus('');
        dialog.showModal();
    });

    dialog.querySelectorAll('[data-ai-import-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    copyPromptButton.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(prompt.value);
            setStatus('Prompt telah disalin.');
        } catch {
            prompt.focus();
            prompt.select();
            document.execCommand('copy');
            setStatus('Prompt telah dipilih. Gunakan Command/Ctrl + C jika perlu.');
        }
    });
    response.addEventListener('input', () => {
        syncButton.disabled = response.value.trim() === '';
        showErrors([]);
        setStatus('');
    });
    syncButton.addEventListener('click', syncResponse);
};

const initializePackagePosterPrompt = () => {
    const form = document.querySelector('[data-package-form]');
    const assistant = form?.querySelector('[data-poster-prompt-assistant]');
    const dialog = assistant?.querySelector('[data-poster-prompt-dialog]');
    const openButton = assistant?.querySelector('[data-poster-prompt-open]');
    const completionStatus = assistant?.querySelector('[data-poster-prompt-completion]');
    const prompt = dialog?.querySelector('[data-poster-prompt]');
    const copyButton = dialog?.querySelector('[data-poster-prompt-copy]');
    const status = dialog?.querySelector('[data-poster-prompt-status]');

    if (!(form instanceof HTMLFormElement)
        || !(dialog instanceof HTMLDialogElement)
        || !(openButton instanceof HTMLButtonElement)
        || !(completionStatus instanceof HTMLElement)
        || !(prompt instanceof HTMLTextAreaElement)
        || !(copyButton instanceof HTMLButtonElement)
        || !(status instanceof HTMLElement)) {
        return;
    }

    const text = (value) => String(value ?? '').replace(/\s+/g, ' ').trim();

    const inputValue = (input) => {
        if (input instanceof HTMLInputElement || input instanceof HTMLSelectElement || input instanceof HTMLTextAreaElement) {
            return text(input.value);
        }

        return '';
    };

    const fieldValue = (name) => inputValue(form.elements.namedItem(name));

    const selectedOptionText = (name) => {
        const field = form.elements.namedItem(name);

        if (!(field instanceof HTMLSelectElement) || field.value === '') {
            return '';
        }

        return text(field.options[field.selectedIndex]?.text);
    };

    const collectionRows = (name) => {
        if (name === 'itinerary_days') {
            return [...form.querySelectorAll('[data-itinerary-days] [data-collection-row], [data-itinerary-days] article')];
        }

        const collection = [...form.querySelectorAll('[data-package-collection]')]
            .find((item) => item.dataset.packageCollection === name);

        return [...(collection?.querySelectorAll('[data-collection-row]') ?? [])];
    };

    const rowValue = (row, name) => inputValue(row.querySelector(`[name$="[${name}]"]`));

    const listValues = (name, fields, formatter) => collectionRows(name)
        .map((row) => Object.fromEntries(fields.map((field) => [field, rowValue(row, field)])))
        .map(formatter)
        .filter((value) => value !== '');

    const selectedServices = (statusName) => [...form.querySelectorAll('[data-package-service-row]')]
        .flatMap((row) => {
            const selection = row.querySelector(`[data-package-service-status="${statusName}"]`);

            if (!(selection instanceof HTMLInputElement) || !selection.checked) {
                return [];
            }

            const serviceName = inputValue(row.querySelector('[data-package-service-name]'));

            return serviceName === '' ? [] : [serviceName];
        });

    const description = () => text(form.querySelector('[data-rich-editor]')?.textContent);
    const departures = () => listValues(
        'departure_rows',
        ['departure_date', 'return_date', 'departure_airport', 'price'],
        ({ departure_date: departureDate, return_date: returnDate, departure_airport: departureAirport, price }) => {
            const details = [
                departureDate && `Pergi ${departureDate}`,
                returnDate && `Balik ${returnDate}`,
                departureAirport && `Dari ${departureAirport}`,
                price && `RM ${price}`,
            ].filter(Boolean);

            return details.length > 0 ? details.join(' · ') : '';
        },
    );
    const flights = () => listValues(
        'flight_options',
        ['airline', 'proposed_departure_date', 'departure_airport', 'arrival_airport', 'notes'],
        ({ airline, proposed_departure_date: proposedDepartureDate, departure_airport: departureAirport, arrival_airport: arrivalAirport, notes }) => {
            const details = [
                airline,
                proposedDepartureDate,
                departureAirport && `Dari ${departureAirport}`,
                arrivalAirport && `Ke ${arrivalAirport}`,
                notes,
            ].filter(Boolean);

            return details.length > 0 ? details.join(' · ') : '';
        },
    );
    const hotels = () => listValues(
        'hotel_options',
        ['location', 'name', 'rating', 'nights', 'walking_distance', 'notes'],
        ({ location, name, rating, nights, walking_distance: walkingDistance, notes }) => {
            const details = [
                location,
                name,
                rating && `${rating} bintang`,
                nights && `${nights} malam`,
                walkingDistance,
                notes,
            ].filter(Boolean);

            return details.length > 0 ? details.join(' · ') : '';
        },
    );
    const roomPrices = () => listValues(
        'room_prices',
        ['room_type', 'price'],
        ({ room_type: roomType, price }) => [roomType, price && `RM ${price}`].filter(Boolean).join(' · '),
    );
    const itineraries = () => listValues(
        'itinerary_days',
        ['title', 'description'],
        ({ title, description: itineraryDescription }) => [title, itineraryDescription].filter(Boolean).join(' — '),
    );
    const ziarahPlaces = () => listValues(
        'ziarah_places',
        ['name', 'location', 'description'],
        ({ name, location, description: placeDescription }) => [name, location, placeDescription].filter(Boolean).join(' · '),
    );
    const gifts = () => listValues(
        'gifts',
        ['title', 'description'],
        ({ title, description: giftDescription }) => [title, giftDescription].filter(Boolean).join(' — '),
    );

    const completionChecks = () => [
        fieldValue('title') !== '',
        selectedOptionText('package_category_id') !== '',
        fieldValue('starting_price') !== '',
        fieldValue('duration_days') !== '' && fieldValue('duration_nights') !== '',
        description() !== '',
        collectionRows('departure_rows').some((row) => rowValue(row, 'departure_date') !== '' && rowValue(row, 'price') !== ''),
        collectionRows('flight_options').some((row) => rowValue(row, 'airline') !== ''),
        collectionRows('hotel_options').some((row) => rowValue(row, 'name') !== ''),
        selectedServices('included').length > 0 || selectedServices('excluded').length > 0,
        collectionRows('itinerary_days').some((row) => rowValue(row, 'title') !== ''),
    ];

    const updateReadiness = () => {
        const checks = completionChecks();
        const completed = checks.filter(Boolean).length;
        const percentage = Math.round((completed / checks.length) * 100);
        const isReady = percentage >= 50;

        openButton.disabled = !isReady;
        completionStatus.textContent = isReady
            ? `${percentage}% lengkap — prompt poster boleh dijana.`
            : `${percentage}% lengkap — isi sekurang-kurangnya 50% maklumat pakej untuk jana prompt poster.`;
    };

    const buildPrompt = () => {
        const details = [
            fieldValue('title') && `Nama pakej: ${fieldValue('title')}`,
            selectedOptionText('package_category_id') && `Kategori: ${selectedOptionText('package_category_id')}`,
            selectedOptionText('season_label') && `Musim: ${selectedOptionText('season_label')}`,
            fieldValue('starting_price') && `Harga bermula: RM ${fieldValue('starting_price')} seorang`,
            fieldValue('duration_days') && fieldValue('duration_nights') && `Tempoh: ${fieldValue('duration_days')} Hari ${fieldValue('duration_nights')} Malam`,
            description() && `Penerangan: ${description()}`,
        ].filter(Boolean);
        const sections = [
            ['TARIKH BERLEPAS', departures()],
            ['PENERBANGAN', flights()],
            ['PENGINAPAN', hotels()],
            ['HARGA BILIK', roomPrices()],
            ['PERKHIDMATAN TERMASUK', selectedServices('included')],
            ['PERKHIDMATAN TIDAK TERMASUK', selectedServices('excluded')],
            ['ITINERARI', itineraries()],
            ['TEMPAT ZIARAH', ziarahPlaces()],
            ['HADIAH JEMAAH', gifts()],
        ];
        const lines = [
            'Cipta poster promosi digital untuk pakej Umrah ini dalam Bahasa Melayu.',
            'Gunakan hanya fakta dalam data di bawah. Jangan mereka atau mengubah nama pakej, harga, tarikh, tempoh, hotel, penerbangan, atau manfaat.',
            'Jika suatu maklumat tidak dinyatakan, jangan masukkannya pada poster.',
            '',
            'KEPERLUAN REKA BENTUK',
            '- Format menegak 4:5, sesuai untuk Instagram (1080 × 1350 px).',
            '- Gaya premium, profesional, tenang dan meyakinkan; gunakan nuansa hijau gelap, putih serta aksen emas lembut.',
            '- Wujudkan hierarki jelas: nama pakej, tempoh, harga bermula, tarikh berlepas dan manfaat utama.',
            '- Gunakan visual suasana Umrah yang sopan dan elegan. Pastikan teks mudah dibaca dan tidak terlalu padat.',
            '',
            'BAHAN VISUAL LAMPIRAN (JIKA ADA)',
            '- Saya mungkin lampirkan foto hotel, mutawif, hadiah jemaah, penerbangan atau lokasi ziarah. Gunakan foto lampiran apabila kandungannya sepadan dengan maklumat pakej.',
            '- Letakkan foto hotel berhampiran penginapan, foto hadiah berhampiran manfaat atau hadiah, dan foto mutawif sebagai elemen sokongan yang sopan. Pilih satu hingga tiga visual paling relevan; jangan jadikan setiap foto sebagai kolaj.',
            '- Jangan menamakan atau mendakwa identiti, peranan, lokasi atau kualiti sesuatu foto jika maklumat itu tidak dinyatakan. Abaikan foto yang kaitannya dengan pakej tidak pasti.',
            '- Kekalkan wajah dan produk sebenar pada foto lampiran; jangan mengubahnya dengan ketara atau menambah orang atau barang yang tidak wujud.',
            '',
            'MAKLUMAT PAKEJ',
            ...details.map((detail) => `- ${detail}`),
        ];

        sections.forEach(([heading, values]) => {
            if (values.length === 0) {
                return;
            }

            lines.push('', `${heading}:`, ...values.map((value) => `- ${value}`));
        });

        lines.push(
            '',
            'Pastikan poster akhir hanya menonjolkan maklumat yang penting kepada pelanggan. Kekalkan semua harga dan tarikh seperti yang diberikan.',
        );

        return lines.join('\n');
    };

    const setStatus = (message) => {
        status.textContent = message;
    };

    openButton.addEventListener('click', () => {
        prompt.value = buildPrompt();
        setStatus('');
        dialog.showModal();
    });

    dialog.querySelectorAll('[data-poster-prompt-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
    copyButton.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(prompt.value);
            setStatus('Prompt poster telah disalin.');
        } catch {
            prompt.focus();
            prompt.select();
            document.execCommand('copy');
            setStatus('Prompt telah dipilih. Gunakan Command/Ctrl + C jika perlu.');
        }
    });

    form.addEventListener('input', updateReadiness);
    form.addEventListener('change', updateReadiness);
    updateReadiness();
};

const initializePackageFormEnhancements = () => {
    const form = document.querySelector('[data-package-form]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const richEditor = form.querySelector('[data-rich-editor]');
    const richEditorInput = form.querySelector('[data-rich-editor-input]');
    const durationInput = form.querySelector('[data-duration-days]');
    const itineraryList = form.querySelector('[data-itinerary-days]');
    const autosaveUrl = form.dataset.autosaveUrl;
    const autosaveStatus = form.querySelector('[data-autosave-status]');
    let autosaveTimer;

    const dispatchFormInput = () => form.dispatchEvent(new Event('input', { bubbles: true }));

    const refreshRoomHotelSelects = () => {
        const hotelCollection = [...form.querySelectorAll('[data-package-collection]')]
            .find((collection) => collection.dataset.packageCollection === 'hotel_options');

        const hotels = [...(hotelCollection?.querySelectorAll('[data-collection-row]') ?? [])]
            .map((row) => {
                const key = row.querySelector('[data-hotel-key]');
                const name = row.querySelector('[name$="[name]"]');
                const location = row.querySelector('[name$="[location]"]');

                if (!(key instanceof HTMLInputElement) || !(name instanceof HTMLInputElement) || key.value === '' || name.value.trim() === '') {
                    return null;
                }

                return {
                    key: key.value,
                    label: `${location instanceof HTMLSelectElement && location.value !== '' ? `${location.value} · ` : ''}${name.value.trim()}`,
                };
            })
            .filter((hotel) => hotel !== null);

        form.querySelectorAll('[data-room-hotel-select]').forEach((select) => {
            if (!(select instanceof HTMLSelectElement)) {
                return;
            }

            const selectedHotelKey = select.value;

            select.replaceChildren(
                new Option('Pilih hotel penginapan', ''),
                ...hotels.map((hotel) => new Option(hotel.label, hotel.key)),
            );
            select.value = hotels.some((hotel) => hotel.key === selectedHotelKey) ? selectedHotelKey : '';
        });
    };

    refreshRoomHotelSelects();
    form.addEventListener('input', refreshRoomHotelSelects);
    form.querySelector('[data-package-collection="hotel_options"]')?.addEventListener('click', () => {
        window.setTimeout(refreshRoomHotelSelects);
    });

    const makeItineraryDay = (dayNumber, values = {}) => {
        const article = document.createElement('article');
        const day = document.createElement('span');
        const fields = document.createElement('div');
        const titleLabel = document.createElement('label');
        const titleCaption = document.createElement('span');
        const title = document.createElement('input');
        const detailLabel = document.createElement('label');
        const detailCaption = document.createElement('span');
        const detail = document.createElement('textarea');

        article.className = 'grid gap-3 rounded-xl border border-[#e2eae4] bg-[#fbfefc] p-4 md:grid-cols-[5.5rem_minmax(0,1fr)]';
        day.className = 'grid h-10 place-items-center rounded-lg bg-[#fff4d7] text-xs font-extrabold text-[#65511b]';
        day.textContent = `Hari ${dayNumber}`;
        fields.className = 'grid gap-3';
        titleCaption.className = 'admin-label';
        titleCaption.textContent = 'Tajuk';
        title.className = 'admin-field !py-2';
        title.name = `itinerary_days[${dayNumber - 1}][title]`;
        title.value = values.title ?? '';
        detailCaption.className = 'admin-label';
        detailCaption.textContent = 'Butiran aktiviti hari ini';
        detail.className = 'admin-field min-h-20 !py-2';
        detail.name = `itinerary_days[${dayNumber - 1}][description]`;
        detail.value = values.description ?? '';
        titleLabel.append(titleCaption, title);
        detailLabel.append(detailCaption, detail);
        fields.append(titleLabel, detailLabel);
        article.append(day, fields);

        return article;
    };

    const syncItineraryDays = () => {
        if (!(durationInput instanceof HTMLInputElement) || !(itineraryList instanceof HTMLElement)) {
            return;
        }

        const duration = Math.min(60, Math.max(1, Number(durationInput.value) || 1));
        const currentValues = [...itineraryList.querySelectorAll('article')].map((article) => ({
            title: article.querySelector('[name$="[title]"]')?.value ?? '',
            description: article.querySelector('[name$="[description]"]')?.value ?? '',
        }));

        itineraryList.replaceChildren(...Array.from({ length: duration }, (_, index) => makeItineraryDay(index + 1, currentValues[index])));
    };

    if (richEditor instanceof HTMLElement && richEditorInput instanceof HTMLInputElement) {
        richEditor.addEventListener('input', () => {
            richEditorInput.value = richEditor.innerHTML;
        });
    }

    durationInput?.addEventListener('change', syncItineraryDays);

    form.querySelector('[data-remove-pdf]')?.addEventListener('click', () => {
        const removeInput = form.querySelector('[data-remove-pdf-input]');
        const currentPdf = form.querySelector('[data-current-pdf]');

        if (removeInput instanceof HTMLInputElement) {
            removeInput.value = '1';
        }

        currentPdf?.remove();
        dispatchFormInput();
    });

    const galleryInput = form.querySelector('[data-gallery-preview]');
    const galleryPreview = form.querySelector('[data-gallery-preview-list]');
    const galleryPrimaryIndex = form.querySelector('[data-gallery-primary-index]');

    const renderGallery = () => {
        if (!(galleryInput instanceof HTMLInputElement) || !(galleryPreview instanceof HTMLElement)) {
            return;
        }

        const existingImageCount = form.querySelectorAll('input[name="primary_image_path"]').length;
        const files = [...(galleryInput.files ?? [])].slice(0, Math.max(0, 3 - existingImageCount));

        if (typeof DataTransfer !== 'undefined' && files.length !== (galleryInput.files?.length ?? 0)) {
            const transfer = new DataTransfer();
            files.forEach((file) => transfer.items.add(file));
            galleryInput.files = transfer.files;
        }

        galleryPreview.replaceChildren(...files.map((file, index) => {
            const card = document.createElement('article');
            const image = document.createElement('img');
            const controls = document.createElement('div');
            const label = document.createElement('label');
            const radio = document.createElement('input');
            const remove = document.createElement('button');

            card.className = 'relative overflow-hidden rounded-xl border border-[#dbe8df] bg-white';
            image.className = 'aspect-[4/3] w-full object-cover';
            image.src = URL.createObjectURL(file);
            image.alt = '';
            controls.className = 'flex items-center justify-between gap-2 px-3 py-2';
            label.className = 'flex items-center gap-2 text-xs font-bold text-[#163a30]';
            radio.type = 'radio';
            radio.name = 'new_primary_image';
            radio.value = String(index);
            radio.checked = galleryPrimaryIndex instanceof HTMLInputElement
                && galleryPrimaryIndex.value === String(index);
            radio.addEventListener('change', () => {
                if (galleryPrimaryIndex instanceof HTMLInputElement) {
                    galleryPrimaryIndex.value = String(index);
                }

                form.querySelectorAll('input[name="primary_image_path"]').forEach((input) => {
                    if (input instanceof HTMLInputElement) {
                        input.checked = false;
                    }
                });

                dispatchFormInput();
            });
            remove.type = 'button';
            remove.className = 'grid size-8 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]';
            remove.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
            remove.addEventListener('click', () => {
                if (typeof DataTransfer === 'undefined') {
                    return;
                }

                if (galleryPrimaryIndex instanceof HTMLInputElement) {
                    const selectedPrimaryIndex = Number(galleryPrimaryIndex.value);

                    if (Number.isInteger(selectedPrimaryIndex)) {
                        if (selectedPrimaryIndex === index) {
                            galleryPrimaryIndex.value = files.length > 1
                                ? String(Math.min(index, files.length - 2))
                                : '';
                        } else if (selectedPrimaryIndex > index) {
                            galleryPrimaryIndex.value = String(selectedPrimaryIndex - 1);
                        }
                    }
                }

                const transfer = new DataTransfer();
                [...(galleryInput.files ?? [])].filter((_, fileIndex) => fileIndex !== index).forEach((selectedFile) => transfer.items.add(selectedFile));
                galleryInput.files = transfer.files;
                renderGallery();
                dispatchFormInput();
            });
            label.append(radio, document.createTextNode('Gambar utama'));
            controls.append(label, remove);
            card.append(image, controls);

            return card;
        }));

    };

    galleryInput?.addEventListener('change', () => {
        if (galleryPrimaryIndex instanceof HTMLInputElement) {
            galleryPrimaryIndex.value = '';
        }

        renderGallery();
        dispatchFormInput();
    });
    form.querySelectorAll('input[name="primary_image_path"]').forEach((input) => input.addEventListener('change', () => {
        if (galleryPrimaryIndex instanceof HTMLInputElement) {
            galleryPrimaryIndex.value = '';
        }

        form.querySelectorAll('input[name="new_primary_image"]').forEach((newPrimaryInput) => {
            if (newPrimaryInput instanceof HTMLInputElement) {
                newPrimaryInput.checked = false;
            }
        });

        dispatchFormInput();
    }));

    const autosave = async () => {
        if (!autosaveUrl) {
            return;
        }

        const selectedFiles = [...form.querySelectorAll('input[type="file"]')]
            .some((input) => input instanceof HTMLInputElement && (input.files?.length ?? 0) > 0);

        if (selectedFiles) {
            if (autosaveStatus instanceof HTMLElement) {
                autosaveStatus.textContent = 'Gambar atau fail baharu akan disimpan apabila anda menekan Simpan Draf.';
            }

            return;
        }

        const data = new FormData(form);
        data.delete('_method');
        data.delete('intent');

        if (autosaveStatus instanceof HTMLElement) {
            autosaveStatus.textContent = 'Menyimpan perubahan…';
        }

        try {
            const request = await fetch(autosaveUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: data,
            });

            if (!request.ok) {
                throw new Error('Autosimpan gagal.');
            }

            const result = await request.json();

            if (autosaveStatus instanceof HTMLElement) {
                autosaveStatus.textContent = `${result.message} ${result.saved_at ?? ''}`;
            }
        } catch {
            if (autosaveStatus instanceof HTMLElement) {
                autosaveStatus.textContent = 'Autosimpan belum berjaya. Gunakan Simpan Draf untuk memastikan perubahan disimpan.';
            }
        }
    };

    form.addEventListener('input', () => {
        if (!autosaveUrl) {
            return;
        }

        window.clearTimeout(autosaveTimer);
        autosaveTimer = window.setTimeout(autosave, 1500);
    });
};

const initializeAutomaticFilters = () => {
    document.querySelectorAll('[data-auto-submit-filters]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        let filterTimer;
        const submitFilters = () => form.requestSubmit();

        form.querySelectorAll('select').forEach((select) => select.addEventListener('change', submitFilters));
        form.querySelectorAll('input').forEach((input) => input.addEventListener('input', () => {
            window.clearTimeout(filterTimer);
            filterTimer = window.setTimeout(submitFilters, 300);
        }));
    });
};

const initializeConfirmations = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const isDelete = form.matches('[data-confirm-delete], [data-confirm-image-delete], [data-confirm-media-delete]')
            || form.querySelector('input[name="_method"][value="DELETE"]') !== null;
        const isArchive = form.matches('[data-confirm-archive]');

        if (!isDelete && !isArchive) {
            return;
        }

        const message = form.dataset.confirmMessage
            ?? (isArchive ? 'Arkibkan pakej ini? Pakej boleh dipulihkan semula sebagai draf.' : 'Anda pasti mahu memadamkan item ini? Tindakan ini tidak boleh dibatalkan.');

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
};

const initializeRemovableImagePreviews = () => {
    document.querySelectorAll('[data-current-image-remove]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.removeInput ?? '');

            if (input instanceof HTMLInputElement) {
                input.value = '1';
            }

            button.closest('[data-current-image]')?.remove();
        });
    });
};

const initializePackageMediaPicker = () => {
    const picker = document.querySelector('[data-package-media-picker]');
    const preview = document.querySelector('[data-package-media-preview]');

    if (!(picker instanceof HTMLDialogElement) || !(preview instanceof HTMLDialogElement)) {
        return;
    }

    const cards = [...picker.querySelectorAll('[data-package-media-asset]')];
    const breadcrumb = picker.querySelector('[data-package-media-breadcrumb]');
    const previewImage = preview.querySelector('[data-package-media-preview-image]');
    const previewName = preview.querySelector('[data-package-media-preview-name]');
    let activeTarget;
    let activeFolder = '';
    let previewAsset;

    const selectedIds = () => activeTarget
        ? [...activeTarget.querySelectorAll('[data-package-media-selected-input]')].map((input) => input.value)
        : [];

    const updateCards = () => {
        const selected = selectedIds();

        cards.forEach((card) => {
            const isVisible = activeFolder === '' || card.dataset.assetFolder === activeFolder;
            const isSelected = selected.includes(card.dataset.assetId ?? '');

            card.hidden = !isVisible;
            card.classList.toggle('bg-[#dfe9e1]', isSelected);
            card.classList.toggle('opacity-50', isSelected);
        });

        if (breadcrumb) {
            const button = picker.querySelector(`[data-package-media-folder-button="${activeFolder}"]`);

            breadcrumb.textContent = activeFolder === '' ? 'Semua folder' : (button?.textContent ?? activeFolder);
        }
    };

    const renderSelection = () => {
        if (!activeTarget) {
            return;
        }

        const container = activeTarget.querySelector('[data-package-media-selected]');

        if (!(container instanceof HTMLElement)) {
            return;
        }

        const currentCards = cards.filter((card) => selectedIds().includes(card.dataset.assetId ?? ''));

        container.replaceChildren(...currentCards.map((card) => {
            const selection = document.createElement('div');
            selection.className = 'relative overflow-hidden rounded-lg border border-[#b9d6c1] bg-white';
            const image = document.createElement('img');
            image.src = card.dataset.assetUrl ?? '';
            image.alt = '';
            image.className = 'aspect-square w-full object-cover';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = activeTarget.dataset.mediaName ?? '';
            input.value = card.dataset.assetId ?? '';
            input.dataset.packageMediaSelectedInput = 'true';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'absolute right-1 top-1 grid size-6 place-items-center rounded-full bg-[#b33129] text-white';
            remove.setAttribute('aria-label', 'Buang pilihan gambar');
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                selection.remove();
                updateCards();
            });
            selection.append(image, input, remove);

            return selection;
        }));

        updateCards();
    };

    const selectAsset = (asset) => {
        if (!activeTarget || !asset) {
            return;
        }

        const multiple = activeTarget.dataset.mediaMultiple === 'true';
        const existing = selectedIds();
        const id = asset.dataset.assetId ?? '';

        if (existing.includes(id)) {
            return;
        }

        if (!multiple) {
            activeTarget.querySelectorAll('[data-package-media-selected-input]').forEach((input) => input.closest('div')?.remove());
        }

        const selection = document.createElement('div');
        selection.className = 'relative overflow-hidden rounded-lg border border-[#b9d6c1] bg-white';
        const image = document.createElement('img');
        image.src = asset.dataset.assetUrl ?? '';
        image.alt = '';
        image.className = 'aspect-square w-full object-cover';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = activeTarget.dataset.mediaName ?? '';
        input.value = id;
        input.dataset.packageMediaSelectedInput = 'true';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'absolute right-1 top-1 grid size-6 place-items-center rounded-full bg-[#b33129] text-white';
        remove.setAttribute('aria-label', 'Buang pilihan gambar');
        remove.textContent = '×';
        remove.addEventListener('click', () => {
            selection.remove();
            updateCards();
        });
        selection.append(image, input, remove);
        activeTarget.querySelector('[data-package-media-selected]')?.append(selection);
        updateCards();

        if (!multiple) {
            picker.close();
        }
    };

    const mediaMapping = (input) => {
        if (input.name === 'poster_image') {
            return ['poster', 'poster_media_asset_id', false];
        }

        if (input.name === 'gallery[]') {
            return ['landscape', 'gallery_media_asset_ids[]', true];
        }

        if (input.name.startsWith('flight_options')) {
            return ['flight', input.name.replace('[images][]', '[media_asset_ids][]'), true];
        }

        if (input.name.startsWith('hotel_options')) {
            return ['hotel', input.name.replace('[images][]', '[media_asset_ids][]'), true];
        }

        if (input.name.startsWith('room_prices')) {
            return ['room', input.name.replace('[image]', '[media_asset_id]'), false];
        }

        if (input.name.startsWith('ziarah_places')) {
            return ['ziarah', input.name.replace('[image]', '[media_asset_id]'), false];
        }

        if (input.name.startsWith('gifts')) {
            return ['gift', input.name.replace('[image]', '[media_asset_id]'), false];
        }

        return null;
    };

    const addMediaPicker = (input) => {
        if (!(input instanceof HTMLInputElement) || input.closest('[data-package-media-selection]')) {
            return;
        }

        const mapping = mediaMapping(input);

        if (!mapping) {
            return;
        }

        const [folder, mediaName, multiple] = mapping;
        const target = document.createElement('div');
        target.dataset.packageMediaSelection = 'true';
        target.dataset.mediaName = mediaName;
        target.dataset.mediaMultiple = String(multiple);
        target.className = 'mt-2';
        const controls = document.createElement('div');
        controls.className = 'flex flex-col gap-2 sm:flex-row sm:items-center';
        input.classList.remove('mt-2');
        input.classList.add('min-w-0', 'flex-1');
        const divider = document.createElement('span');
        divider.className = 'hidden shrink-0 text-[11px] font-bold uppercase tracking-[.1em] text-[#718078] sm:block';
        divider.textContent = 'atau';
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.dataset.packageMediaPickerTrigger = 'true';
        trigger.dataset.mediaFolder = folder;
        trigger.className = 'admin-button-secondary shrink-0 !px-3 !py-2 text-xs';
        trigger.setAttribute('aria-label', 'Pilih dari pustaka media');
        const icon = document.createElement('span');
        icon.className = 'material-symbols-outlined text-[18px]';
        icon.textContent = 'folder';
        const label = document.createElement('span');
        label.textContent = 'Pilih dari pustaka media';
        trigger.append(icon, label);
        const selected = document.createElement('div');
        selected.dataset.packageMediaSelected = 'true';
        selected.className = 'mt-2 grid grid-cols-4 gap-2';
        input.before(target);
        controls.append(input, divider, trigger);
        target.append(controls, selected);
    };

    const addMediaPickers = () => document.querySelectorAll('[data-collection-image-input], [data-package-media-upload-input]').forEach(addMediaPicker);

    addMediaPickers();
    document.addEventListener('click', (event) => {
        const trigger = event.target instanceof Element ? event.target.closest('[data-package-media-picker-trigger]') : null;

        if (!(trigger instanceof HTMLElement)) {
            return;
        }

        activeTarget = trigger.closest('[data-package-media-selection]');
        activeFolder = trigger.dataset.mediaFolder ?? '';
        updateCards();
        picker.showModal();
    });
    document.querySelectorAll('[data-collection-add]').forEach((button) => button.addEventListener('click', () => {
        window.setTimeout(addMediaPickers);
    }));

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            previewAsset = card;

            if (previewImage instanceof HTMLImageElement) {
                previewImage.src = card.dataset.assetUrl ?? '';
                previewImage.alt = card.dataset.assetName ?? '';
            }

            if (previewName) {
                previewName.textContent = card.dataset.assetName ?? '';
            }

            preview.showModal();
        });
    });

    preview.querySelector('[data-package-media-preview-select]')?.addEventListener('click', () => {
        selectAsset(previewAsset);
        preview.close();
    });
    preview.querySelector('[data-package-media-preview-close]')?.addEventListener('click', () => preview.close());
    picker.querySelector('[data-package-media-picker-close]')?.addEventListener('click', () => picker.close());
    picker.querySelector('[data-package-media-picker-done]')?.addEventListener('click', () => picker.close());
    picker.querySelector('[data-package-media-folder-all]')?.addEventListener('click', () => {
        activeFolder = '';
        updateCards();
    });
    picker.querySelectorAll('[data-package-media-folder-button]').forEach((button) => {
        button.addEventListener('click', () => {
            activeFolder = button.dataset.packageMediaFolderButton ?? '';
            updateCards();
        });
    });
    [picker, preview].forEach((dialog) => dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    }));
};

const initializePackageMediaUsageDialogs = () => {
    document.querySelectorAll('[data-package-media-usage-open]').forEach((trigger) => {
        if (!(trigger instanceof HTMLButtonElement)) {
            return;
        }

        const dialog = document.getElementById(trigger.dataset.packageMediaUsageOpen ?? '');

        if (!(dialog instanceof HTMLDialogElement)) {
            return;
        }

        const rows = [...dialog.querySelectorAll('[data-package-media-usage-row]')];
        const pagination = dialog.querySelector('[data-package-media-usage-pagination]');
        const pageStatus = dialog.querySelector('[data-package-media-usage-page-status]');
        const previousButton = dialog.querySelector('[data-package-media-usage-previous]');
        const nextButton = dialog.querySelector('[data-package-media-usage-next]');
        const pageSize = 10;
        const totalPages = Math.ceil(rows.length / pageSize);
        let currentPage = 0;

        const renderPage = (page) => {
            if (totalPages <= 1
                || !(pagination instanceof HTMLElement)
                || !(pageStatus instanceof HTMLElement)
                || !(previousButton instanceof HTMLButtonElement)
                || !(nextButton instanceof HTMLButtonElement)) {
                return;
            }

            currentPage = Math.min(Math.max(page, 0), totalPages - 1);
            const start = currentPage * pageSize;
            const end = Math.min(start + pageSize, rows.length);

            rows.forEach((row, index) => {
                row.hidden = index < start || index >= end;
            });
            pageStatus.textContent = `Menunjukkan ${start + 1}–${end} daripada ${rows.length} penggunaan`;
            pagination.hidden = false;
            previousButton.disabled = currentPage === 0;
            nextButton.disabled = currentPage === totalPages - 1;
            previousButton.classList.toggle('opacity-40', previousButton.disabled);
            nextButton.classList.toggle('opacity-40', nextButton.disabled);
        };

        previousButton instanceof HTMLButtonElement && previousButton.addEventListener('click', () => renderPage(currentPage - 1));
        nextButton instanceof HTMLButtonElement && nextButton.addEventListener('click', () => renderPage(currentPage + 1));

        trigger.addEventListener('click', () => {
            renderPage(0);
            dialog.showModal();
        });
        dialog.querySelector('[data-package-media-usage-close]')?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
};

const initializeFeaturedVideoSource = () => {
    document.querySelectorAll('[data-featured-video-form]').forEach((form) => {
        if (!(form instanceof HTMLElement)) {
            return;
        }

        const currentVideo = form.querySelector('[data-featured-video-current]');
        const sourceFields = form.querySelector('[data-featured-video-sources]');
        const videoUrl = form.querySelector('[data-featured-video-url]');
        const videoFile = form.querySelector('[data-featured-video-file]');
        const removeInput = form.querySelector('[data-featured-video-remove-input]');
        const removeButton = form.querySelector('[data-featured-video-remove]');
        const status = form.querySelector('[data-featured-video-status]');

        if (!(currentVideo instanceof HTMLElement)
            || !(sourceFields instanceof HTMLElement)
            || !(videoUrl instanceof HTMLInputElement)
            || !(videoFile instanceof HTMLInputElement)
            || !(removeInput instanceof HTMLInputElement)
            || !(removeButton instanceof HTMLButtonElement)
            || !(status instanceof HTMLElement)) {
            return;
        }

        const hasCurrentVideo = () => currentVideo.dataset.featuredVideoPresent === 'true'
            && removeInput.value !== '1';

        const updateSourceAvailability = () => {
            if (hasCurrentVideo()) {
                videoUrl.disabled = true;
                videoFile.disabled = true;
                sourceFields.classList.add('opacity-60');
                status.textContent = 'Tekan × pada video semasa untuk membuangnya sebelum memilih video baharu.';

                return;
            }

            const hasVideoUrl = videoUrl.value.trim() !== '';
            const hasVideoFile = (videoFile.files?.length ?? 0) > 0;

            videoUrl.disabled = hasVideoFile;
            videoFile.disabled = hasVideoUrl;
            sourceFields.classList.remove('opacity-60');

            if (hasVideoFile) {
                status.textContent = `Fail ${videoFile.files?.[0]?.name ?? 'video'} dipilih. Kosongkan pilihan ini untuk menggunakan pautan YouTube.`;
            } else if (hasVideoUrl) {
                status.textContent = 'Pautan YouTube dipilih. Kosongkan pautan ini untuk memuat naik fail video.';
            } else {
                status.textContent = 'Masukkan pautan YouTube atau pilih fail MP4/WebM (maksimum 100MB). Pilih satu sahaja.';
            }
        };

        removeButton.addEventListener('click', () => {
            removeInput.value = '1';
            currentVideo.dataset.featuredVideoPresent = 'false';
            currentVideo.classList.add('hidden');
            videoUrl.value = '';
            videoFile.value = '';
            updateSourceAvailability();
            status.textContent = 'Video semasa akan dibuang apabila tetapan disimpan. Anda kini boleh memilih sumber baharu.';
        });

        videoUrl.addEventListener('input', updateSourceAvailability);
        videoFile.addEventListener('change', updateSourceAvailability);

        updateSourceAvailability();
    });
};

const initializeCategoryHeroMedia = () => {
    document.querySelectorAll('[data-category-hero-form]').forEach((form) => {
        const mediaTypes = ['image', 'video'];
        const fields = new Map(mediaTypes.map((mediaType) => [mediaType, {
            input: form.querySelector(`[data-hero-media-input="${mediaType}"]`),
            panel: form.querySelector(`[data-hero-media-panel="${mediaType}"]`),
            preview: form.querySelector(`[data-hero-media-preview="${mediaType}"]`),
            removeInput: form.querySelector(`[data-hero-media-remove-input="${mediaType}"]`),
            removeButton: form.querySelector(`[data-hero-media-remove="${mediaType}"]`),
            status: form.querySelector(`[data-hero-media-status="${mediaType}"]`),
        }]));

        const isValidField = (field) => field.input instanceof HTMLInputElement
            && field.panel instanceof HTMLElement
            && field.preview instanceof HTMLElement
            && field.removeInput instanceof HTMLInputElement
            && field.removeButton instanceof HTMLButtonElement
            && field.status instanceof HTMLElement;

        if (![...fields.values()].every(isValidField)) {
            return;
        }

        const hasMedia = (mediaType) => {
            const field = fields.get(mediaType);

            return field.preview.dataset.heroMediaPresent === 'true'
                && field.removeInput.value !== '1';
        };

        const emptyPreview = (mediaType) => {
            const field = fields.get(mediaType);
            const icon = document.createElement('span');

            icon.className = 'material-symbols-outlined text-4xl';
            icon.textContent = mediaType === 'image' ? 'image' : 'movie';
            field.preview.replaceChildren(icon, field.removeButton);
        };

        const updateAvailability = () => {
            mediaTypes.forEach((mediaType) => {
                const field = fields.get(mediaType);
                const otherType = mediaTypes.find((type) => type !== mediaType);
                const hasOtherMedia = otherType !== undefined && hasMedia(otherType);

                field.input.disabled = hasOtherMedia;
                field.panel.classList.toggle('opacity-60', hasOtherMedia && !hasMedia(mediaType));
                field.status.textContent = hasOtherMedia
                    ? `Keluarkan ${otherType === 'image' ? 'imej' : 'video'} terlebih dahulu untuk menggunakan ${mediaType === 'image' ? 'imej' : 'video'}.`
                    : field.status.dataset.heroMediaDefaultStatus ?? '';
            });
        };

        mediaTypes.forEach((mediaType) => {
            const field = fields.get(mediaType);

            field.input.addEventListener('change', () => {
                const [file] = field.input.files ?? [];

                if (!file) {
                    return;
                }

                const media = document.createElement(mediaType === 'image' ? 'img' : 'video');

                media.src = URL.createObjectURL(file);
                media.className = 'size-full object-cover';

                if (media instanceof HTMLImageElement) {
                    media.alt = `Pratonton ${file.name}`;
                } else {
                    media.controls = true;
                    media.preload = 'metadata';
                }

                field.preview.dataset.heroMediaPresent = 'true';
                field.preview.replaceChildren(media, field.removeButton);
                field.removeInput.value = '';
                field.removeButton.classList.remove('hidden');
                field.status.textContent = `Pratonton ${file.name}. Simpan kategori apabila visual ini telah disemak.`;
                updateAvailability();
            });

            field.removeButton.addEventListener('click', () => {
                field.input.value = '';
                field.preview.dataset.heroMediaPresent = 'false';
                field.removeInput.value = '1';
                field.removeButton.classList.add('hidden');
                emptyPreview(mediaType);
                field.status.textContent = 'Media ini akan dikeluarkan apabila kategori disimpan.';
                updateAvailability();
            });
        });

        updateAvailability();
    });
};

const initializeCategorySlugPreviews = () => {
    document.querySelectorAll('[data-category-name]').forEach((nameInput) => {
        if (!(nameInput instanceof HTMLInputElement)) {
            return;
        }

        const slugPreview = nameInput.closest('form')?.querySelector('[data-category-slug-preview]');

        if (!(slugPreview instanceof HTMLInputElement)) {
            return;
        }

        const updateSlugPreview = () => {
            slugPreview.value = nameInput.value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLocaleLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        };

        nameInput.addEventListener('input', updateSlugPreview);
        updateSlugPreview();
    });
};

const initializeNotificationMenus = () => {
    document.querySelectorAll('[data-notification-menu]').forEach((menu) => {
        const toggle = menu.querySelector('[data-notification-toggle]');
        const panel = menu.querySelector('[data-notification-panel]');

        if (!(toggle instanceof HTMLButtonElement) || !(panel instanceof HTMLElement)) {
            return;
        }

        const close = () => {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            const isOpen = !panel.hidden;

            panel.hidden = isOpen;
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
                toggle.focus();
            }
        });
    });
};

const initializeLogoPreviews = () => {
    document.querySelectorAll('[data-logo-field]').forEach((field) => {
        const input = field.querySelector('[data-logo-input]');
        const removeInput = field.querySelector('[data-logo-remove-input]');
        const preview = field.querySelector('[data-logo-preview]');
        const previewImage = field.querySelector('[data-logo-preview-image]');
        const removeButton = field.querySelector('[data-logo-remove]');
        const status = field.querySelector('[data-logo-status]');
        const logoLabel = field.dataset.logoLabel ?? 'Logo';

        if (
            !(input instanceof HTMLInputElement)
            || !(removeInput instanceof HTMLInputElement)
            || !(preview instanceof HTMLElement)
            || !(previewImage instanceof HTMLImageElement)
            || !(removeButton instanceof HTMLButtonElement)
            || !(status instanceof HTMLElement)
        ) {
            return;
        }

        input.addEventListener('change', () => {
            const [file] = input.files ?? [];

            if (!file) {
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewImage.alt = `Pratonton ${logoLabel.toLocaleLowerCase()}`;
            preview.classList.remove('hidden');
            removeInput.value = '';
            status.textContent = `Pratonton ${file.name}. Simpan tetapan untuk menggunakan ${logoLabel.toLocaleLowerCase()} baharu.`;
        });

        removeButton.addEventListener('click', () => {
            input.value = '';
            preview.classList.add('hidden');
            removeInput.value = '1';
            status.textContent = `${logoLabel} akan dikeluarkan apabila tetapan disimpan.`;
        });
    });
};

initializeAdminNavigation();
initializePasswordToggles();
initializeBulkSelection();
initializeAdminTabs();
initializePackageForm();
initializeMediaUpload();
initializePackageAiImport();
initializePackagePosterPrompt();
initializePackageFormEnhancements();
initializeAutomaticFilters();
initializePackagePosterLightbox();
initializeHotelLightbox();
initializeConfirmations();
initializeRemovableImagePreviews();
initializePackageMediaPicker();
initializePackageMediaUsageDialogs();
initializeFeaturedVideoSource();
initializeCategoryHeroMedia();
initializeCategorySlugPreviews();
initializeNotificationMenus();
initializeLogoPreviews();
