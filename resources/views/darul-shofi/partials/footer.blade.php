{{-- Based on supplied Stitch HTML: partials/footer.blade.php --}}
<footer class="bg-brand-emerald-dark text-white border-t border-brand-emerald-light/30 pt-16 pb-12" id="hubungi">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 pb-12 border-b border-white/10">
<!-- Brand & Mission Column -->
<div class="lg:col-span-4 space-y-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-brand-emerald border border-brand-gold/40 flex items-center justify-center text-brand-gold-light shadow-md">
<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" viewbox="0 0 24 24">
<path d="M12 2C8 2 4 6 4 11v9h16v-9c0-5-4-9-8-9z"></path>
<path d="M9 20v-7a3 3 0 0 1 6 0v7"></path>
</svg>
</div>
<div>
<span class="text-xl font-serif-luxury font-bold tracking-wider text-white uppercase">DARUL SHOFI</span>
<p class="text-[9px] tracking-widest uppercase font-semibold text-brand-gold-light">Travel &amp; Tours Sdn Bhd</p>
</div>
</div>
<p class="text-xs text-gray-300 font-light leading-relaxed max-w-sm">
            Perjalanan ibadah yang lebih mudah, teratur, dan penuh keberkatan bersama bimbingan mutawwif berpengalaman Darul Shofi. Dilesenkan di bawah Kementerian Pelancongan, Seni dan Budaya (MOTAC).
          </p>
<div class="flex items-center gap-3 pt-2">
<a aria-label="Facebook" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 flex items-center justify-center text-xs text-brand-gold transition" href="#hubungi"><i class="fa-brands fa-facebook-f"></i></a>
<a aria-label="Instagram" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 flex items-center justify-center text-xs text-brand-gold transition" href="#hubungi"><i class="fa-brands fa-instagram"></i></a>
<a aria-label="TikTok" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 flex items-center justify-center text-xs text-brand-gold transition" href="#hubungi"><i class="fa-brands fa-tiktok"></i></a>
<a aria-label="YouTube" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 flex items-center justify-center text-xs text-brand-gold transition" href="#hubungi"><i class="fa-brands fa-youtube"></i></a>
</div>
</div>
<!-- Quick Links: Pakej Utama -->
<div class="lg:col-span-2 space-y-3">
<h4 class="text-xs uppercase font-bold tracking-wider text-brand-gold-light">Pakej Pilihan</h4>
<ul class="space-y-2 text-xs text-gray-300 font-light">
<li><a class="hover:text-brand-gold transition" href="#pakej">Pakej Ekonomi</a></li>
<li><a class="hover:text-brand-gold transition" href="#pakej">Pakej Premium Safwah</a></li>
<li><a class="hover:text-brand-gold transition" href="#pakej">Pakej Ramadan 1447H</a></li>
<li><a class="hover:text-brand-gold transition" href="#pakej">Pakej Keluarga Selesa</a></li>
<li><a class="hover:text-brand-gold transition" href="#haji">Pakej Haji Bersasar</a></li>
<li><a class="hover:text-brand-gold transition" href="#ziarah">Pakej Umrah Ziarah</a></li>
</ul>
</div>
<!-- Quick Links: Maklumat & Panduan -->
<div class="lg:col-span-3 space-y-3">
<h4 class="text-xs uppercase font-bold tracking-wider text-brand-gold-light">Maklumat &amp; Panduan</h4>
<ul class="space-y-2 text-xs text-gray-300 font-light">
<li><a class="hover:text-brand-gold transition" href="#tentang">Tentang Darul Shofi</a></li>
<li><a class="hover:text-brand-gold transition" href="#panduan">Panduan Manasik Umrah</a></li>
<li><a class="hover:text-brand-gold transition" href="#galeri">Galeri Jemaah</a></li>
<li><a class="hover:text-brand-gold transition" href="#hubungi">Soalan Lazim (FAQ)</a></li>
<li><a class="hover:text-brand-gold transition" href="#hubungi">Syarat &amp; Pembatalan</a></li>
<li><a class="hover:text-brand-gold transition" href="#hubungi">Perlindungan Takaful</a></li>
</ul>
</div>
<!-- Contact & Operations Column -->
<div class="lg:col-span-3 space-y-3">
<h4 class="text-xs uppercase font-bold tracking-wider text-brand-gold-light">Hubungi Kami</h4>
<div class="space-y-2.5 text-xs text-gray-300 font-light">
<div class="flex items-start gap-2.5">
<i class="fa-brands fa-whatsapp text-brand-gold text-sm mt-0.5"></i>
<span>Hotline WhatsApp: {{ config('darul_shofi.whatsapp_display') ?: 'Sila hubungi kami' }}</span>
</div>
<div class="flex items-start gap-2.5">
<i class="fa-regular fa-envelope text-brand-gold text-xs mt-0.5"></i>
<span>info@darulshofi.com</span>
</div>
<div class="flex items-start gap-2.5">
<i class="fa-solid fa-location-dot text-brand-gold text-xs mt-0.5"></i>
<span>Tingkat 5, Menara Darul Shofi, Jalan Ampang, 50450 Kuala Lumpur, Malaysia</span>
</div>
<div class="flex items-start gap-2.5">
<i class="fa-regular fa-clock text-brand-gold text-xs mt-0.5"></i>
<span>Isnin – Jumaat: 9:00 pagi – 6:00 petang<br/>Sabtu: 9:00 pagi – 1:00 tengah hari</span>
</div>
</div>
</div>
</div>
<!-- Copyright Sub-footer -->
<div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 font-light gap-3">
<p>© 2026 Darul Shofi Travel &amp; Tours Sdn Bhd (KPK/LN: 9876). Hak cipta terpelihara.</p>
<div class="flex items-center gap-6">
<a class="hover:text-brand-gold transition" href="#hubungi">Dasar Privasi</a>
<span>·</span>
<a class="hover:text-brand-gold transition" href="#hubungi">Terma &amp; Syarat</a>
</div>
</div>
</div>
</footer>
