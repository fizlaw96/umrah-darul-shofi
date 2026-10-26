@php($isEditing = $faq->exists)
@php($selectedCategory = old('category', $faq->category ?: 'Umum'))

<form class="panel max-w-2xl space-y-5 p-6" method="POST" action="{{ $isEditing ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <x-errors />

    <label>
        <span class="field-label">Kategori</span>
        <select class="field-control" name="category" required>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <span class="mt-1 block text-xs text-slate-500">Pilih kategori yang paling sesuai untuk soalan ini.</span>
    </label>
    <label><span class="field-label">Soalan</span><input class="field-control" name="question" value="{{ old('question', $faq->question) }}" required></label>
    <label><span class="field-label">Jawapan</span><textarea class="field-control min-h-32" name="answer" required>{{ old('answer', $faq->answer) }}</textarea></label>
    <label class="flex items-end gap-3 pb-3"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->is_published))><span class="text-sm font-bold text-slate-700">Terbitkan soalan</span></label>

    <div class="flex flex-wrap gap-3"><button class="button-primary">{{ $isEditing ? 'Simpan soalan' : 'Tambah soalan' }}</button><a class="button-secondary" href="{{ route('admin.faqs.index') }}"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>Kembali</a>@if ($isEditing)<button form="delete-faq" class="button-secondary !border-red-200 !text-red-700">Padam</button>@endif</div>
</form>

@if ($isEditing)
    <form id="delete-faq" method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" data-confirm-delete data-confirm-message="Padam soalan lazim ini?">
        @csrf
        @method('DELETE')
    </form>
@endif
