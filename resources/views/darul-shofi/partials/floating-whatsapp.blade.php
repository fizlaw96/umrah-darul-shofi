{{-- Based on supplied Stitch HTML: partials/floating-whatsapp.blade.php --}}
@php
    $number = preg_replace('/\D+/', '', (string) ($number ?? ($siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number', '')))) ?? '';

    if (str_starts_with($number, '0')) {
        $number = '60'.ltrim($number, '0');
    }

    $whatsappDisplay ??= $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_display', '');
    $whatsappLink ??= static fn (string $message = ''): string => $number !== ''
        ? 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '')
        : '#hubungi';
    $whatsAppMessage ??= 'Salam SalamHaji, saya ingin bertanya tentang pakej perjalanan.';
@endphp
<div class="fixed bottom-6 right-6 z-50 flex items-center group" data-floating-whatsapp>
<!-- Tooltip -->
<div class="hidden sm:block mr-3 px-3.5 py-1.5 rounded-full bg-white text-brand-emerald text-xs font-semibold shadow-lg border border-brand-gold/30 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
      {{ $number === '' ? 'Sila hubungi kami' : 'Perlukan Bantuan? WhatsApp Kami' }}
      @if ($number !== '' && filled($whatsappDisplay))
          <span class="sr-only">Hotline WhatsApp: {{ $whatsappDisplay }}</span>
      @endif
    </div>
<!-- Floating Circular Button with Pulse -->
<a aria-label="Hubungi kami melalui WhatsApp" class="relative w-14 h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center text-2xl shadow-xl hover:scale-110 transition-transform duration-200" href="{{ $whatsappLink($whatsAppMessage) }}" rel="noopener noreferrer" target="_blank">
<span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-brand-gold border-2 border-white animate-ping"></span>
<span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-brand-gold border-2 border-white"></span>
<i class="fa-brands fa-whatsapp"></i>
</a>
</div>
