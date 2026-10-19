@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <p class="font-bold">Sila semak maklumat yang diperlukan.</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
