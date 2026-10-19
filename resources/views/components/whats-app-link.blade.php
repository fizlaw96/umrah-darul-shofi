<div>
    <!-- No surplus words or unnecessary actions. - Marcus Aurelius -->
</div>
@props([
    'number' => null,
    'message' => '',
    'fallback' => null,
])

@php
    $phoneNumber = preg_replace('/\D+/', '', (string) $number) ?? '';

    if (str_starts_with($phoneNumber, '0')) {
        $phoneNumber = '60'.ltrim($phoneNumber, '0');
    }

    $whatsAppUrl = filled($phoneNumber)
        ? 'https://wa.me/'.$phoneNumber.'?text='.rawurlencode($message)
        : ($fallback ?: route('contact.create'));
@endphp

<a
    {{ $attributes }}
    href="{{ $whatsAppUrl }}"
    @if (filled($phoneNumber)) target="_blank" rel="noopener noreferrer" @endif
>
    {{ $slot }}
</a>
