@props([
    'path' => null,
    'url' => null,
    'label' => 'Laraskan fokus',
    'iconOnly' => false,
])

@if (filled($path) && filled($url))
    <button
        {{ $attributes->class([
            'rounded-lg border border-[#9dcdb0] bg-white font-bold text-[#006b57] transition hover:bg-[#e9f7ed]',
            'inline-flex items-center gap-1.5 px-3 py-2 text-[11px]' => ! $iconOnly,
            'grid size-10 place-items-center' => $iconOnly,
        ]) }}
        type="button"
        data-image-focus-open
        data-image-focus-path="{{ $path }}"
        data-image-focus-url="{{ $url }}"
        data-image-focus-save-url="{{ route('admin.image-focus.update') }}"
        data-image-focus-position="{{ $imageFocusPositions[$path] ?? '50% 50%' }}"
        @if ($iconOnly)
            aria-label="{{ $label }}"
            title="{{ $label }}"
        @endif
    >
        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">center_focus_strong</span>
        @unless ($iconOnly)
            {{ $label }}
        @endunless
    </button>
@endif
