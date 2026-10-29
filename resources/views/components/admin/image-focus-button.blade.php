@props([
    'path' => null,
    'url' => null,
    'label' => 'Laraskan fokus',
])

@if (filled($path) && filled($url))
    <button
        type="button"
        data-image-focus-open
        data-image-focus-path="{{ $path }}"
        data-image-focus-url="{{ $url }}"
        data-image-focus-save-url="{{ route('admin.image-focus.update') }}"
        data-image-focus-position="{{ $imageFocusPositions[$path] ?? '50% 50%' }}"
        class="inline-flex items-center gap-1.5 rounded-lg border border-[#9dcdb0] bg-white px-3 py-2 text-[11px] font-bold text-[#006b57] transition hover:bg-[#e9f7ed]"
    >
        <span class="material-symbols-outlined text-[16px]">center_focus_strong</span>
        {{ $label }}
    </button>
@endif
