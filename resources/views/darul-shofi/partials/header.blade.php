{{-- Based on supplied Stitch HTML: partials/header.blade.php --}}
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#E9E4DC] transition-all duration-300">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex items-center justify-between h-20">
<!-- Brand Logo -->
<a class="flex items-center gap-3.5 group" href="{{ url('/') }}">
<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-emerald to-brand-emerald-dark border border-brand-gold/40 flex items-center justify-center text-brand-gold-light shadow-md transition-transform group-hover:scale-105">
<!-- Kaaba & Arch Icon Emblem -->
<svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" viewbox="0 0 24 24">
<path d="M12 2C8 2 4 6 4 11v9h16v-9c0-5-4-9-8-9z"></path>
<path d="M9 20v-7a3 3 0 0 1 6 0v7"></path>
<path d="M12 2v2"></path>
</svg>
</div>
<div>
<div class="flex items-center gap-1.5">
<span class="text-xl font-serif-luxury font-bold tracking-wider text-brand-emerald uppercase">DARUL SHOFI</span>
</div>
<p class="text-[10px] tracking-widest uppercase font-semibold text-brand-gold-dark">Travel &amp; Tours · 1447H</p>
</div>
</a>
<!-- Desktop Navigation Links -->
<nav class="hidden lg:flex items-center space-x-8 text-sm font-medium text-brand-charcoal">
<a class="text-brand-emerald font-semibold border-b-2 border-brand-gold pb-1" href="{{ url('/') }}">Utama</a>
<a class="hover:text-brand-emerald transition-colors" href="#pakej">Pakej Umrah</a>
<a class="hover:text-brand-emerald transition-colors" href="#tentang">Tentang Kami</a>
<a class="hover:text-brand-emerald transition-colors" href="#galeri">Galeri</a>
<a class="hover:text-brand-emerald transition-colors" href="#panduan">Panduan</a>
<a class="hover:text-brand-emerald transition-colors" href="#hubungi">Hubungi Kami</a>
</nav>
<!-- Right Side WhatsApp Direct Button -->
<div class="flex items-center gap-3"><button aria-controls="darul-mobile-nav" aria-expanded="false" aria-label="Buka menu navigasi" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-brand-gold/30 text-brand-emerald hover:bg-brand-cream" data-mobile-menu-toggle="" type="button"><i aria-hidden="true" class="fa-solid fa-bars"></i></button>
<a class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-brand-emerald text-white rounded-full text-xs sm:text-sm font-medium tracking-wide shadow-md hover:bg-brand-emerald-dark transition-all duration-200 border border-brand-emerald-light" href="{{ $whatsappLink('Salam Darul Shofi, saya ingin bertanya tentang Pakej Umrah 1447H.') }}" rel="noopener noreferrer" target="_blank">
<i class="fa-brands fa-whatsapp text-emerald-400 text-base"></i>
<span>WhatsApp Kami</span>
</a>
</div>
</div>
<nav aria-label="Navigasi mudah alih" class="hidden lg:hidden pb-5 pt-2 border-t border-brand-gold/20" data-mobile-menu="" id="darul-mobile-nav"><div class="grid grid-cols-2 gap-2 text-sm font-medium text-brand-emerald"><a class="px-3 py-3 rounded-xl hover:bg-brand-cream" href="{{ url('/') }}">Utama</a><a class="px-3 py-3 rounded-xl hover:bg-brand-cream" href="#pakej">Pakej Umrah</a><a class="px-3 py-3 rounded-xl hover:bg-brand-cream" href="#tentang">Tentang Kami</a><a class="px-3 py-3 rounded-xl hover:bg-brand-cream" href="#galeri">Galeri</a><a class="px-3 py-3 rounded-xl hover:bg-brand-cream" href="#panduan">Panduan</a><a class="px-3 py-3 rounded-xl hover:bg-brand-cream" href="#hubungi">Hubungi Kami</a></div></nav></div>
</header>
