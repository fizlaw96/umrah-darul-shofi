<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact', [
            'packages' => Package::query()->published()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        Enquiry::query()->create([
            ...$request->safe()->except('privacy_consent'),
            'privacy_consented_at' => now(),
        ]);

        return back()->with('success', 'Terima kasih. Pertanyaan anda telah diterima dan pasukan kami akan menghubungi anda.');
    }
}
