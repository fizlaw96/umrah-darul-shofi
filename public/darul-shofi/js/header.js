/* Shared public mobile navigation. */
(() => {
  'use strict';

  const menuButton = document.querySelector('[data-mobile-menu-toggle]');
  const mobileMenu = document.querySelector('[data-mobile-menu]');

  if (!menuButton || !mobileMenu) {
    return;
  }

  const setOpen = (open) => {
    mobileMenu.classList.toggle('hidden', !open);
    document.body.classList.toggle('ds-mobile-menu-open', open);
    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');

    const icon = menuButton.querySelector('i');

    if (icon) {
      icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
    }

    if (!open) {
      menuButton.focus();
    }
  };

  menuButton.addEventListener('click', () => setOpen(menuButton.getAttribute('aria-expanded') !== 'true'));
  mobileMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
      setOpen(false);
    }
  });
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1280 && menuButton.getAttribute('aria-expanded') === 'true') {
      setOpen(false);
    }
  });
})();
