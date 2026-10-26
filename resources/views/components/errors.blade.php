@if (request()->boolean('upload_error') || $errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <p class="font-bold">Sila semak maklumat yang diperlukan.</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @if (request()->boolean('upload_error'))
                <li>Fail terlalu besar untuk dimuat naik oleh pelayan. Gunakan video yang tidak melebihi 100 MB.</li>
            @endif
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
