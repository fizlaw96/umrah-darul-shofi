<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => WebsiteSetting::query()->pluck('value', 'key'),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            WebsiteSetting::setValue(
                $key,
                $value,
                str_starts_with($key, 'contact_') || str_ends_with($key, '_url') ? 'contact' : (str_starts_with($key, 'homepage_') ? 'homepage' : 'general'),
            );
        }

        return back()->with('success', 'Tetapan laman berjaya dikemas kini.');
    }
}
