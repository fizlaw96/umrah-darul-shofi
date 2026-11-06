<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransportSettingsRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransportSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = WebsiteSetting::siteSettings();

        return view('admin.transport-settings.edit', [
            'settings' => $settings,
            'transportBannerUrl' => WebsiteSetting::publicUrl($settings['transport_banner_path'] ?? null),
            'transportVehicleOptions' => WebsiteSetting::transportVehicleOptions($settings['transport_vehicle_options'] ?? null),
        ]);
    }

    public function update(TransportSettingsRequest $request): RedirectResponse
    {
        $values = $request->safe()->except(['transport_banner', 'remove_transport_banner', 'transport_vehicle_options']);
        $values['transport_enabled'] = $request->boolean('transport_enabled') ? '1' : '0';
        $values['transport_whatsapp'] = WebsiteSetting::normalizedWhatsAppNumber($request->input('transport_whatsapp'));
        $values['transport_vehicle_options'] = WebsiteSetting::encodeTransportVehicleOptions(
            $request->validated('transport_vehicle_options'),
        );
        $oldBanner = WebsiteSetting::getValue('transport_banner_path');

        if ($request->hasFile('transport_banner')) {
            $values['transport_banner_path'] = $request->file('transport_banner')->store('transport', 'public');
        } elseif ($request->boolean('remove_transport_banner')) {
            $values['transport_banner_path'] = null;
        }

        foreach ($values as $key => $value) {
            WebsiteSetting::setValue($key, $value, 'transport');
        }

        if (array_key_exists('transport_banner_path', $values) && $oldBanner !== $values['transport_banner_path']) {
            $this->deleteBanner($oldBanner);
        }

        return redirect()->route('admin.transport-settings.edit')->with('success', 'Tetapan Transport berjaya dikemas kini.');
    }

    private function deleteBanner(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
