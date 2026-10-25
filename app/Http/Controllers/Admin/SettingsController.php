<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PrepareCompanyLogo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = array_merge(
            WebsiteSetting::siteDefaults(),
            WebsiteSetting::query()->pluck('value', 'key')->all(),
        );

        return view('admin.settings.edit', [
            'settings' => $settings,
            'companyLogoUrl' => WebsiteSetting::publicUrl($settings['company_logo_path'] ?? null)
                ?? ($settings['company_logo_url'] ?? null),
            'footerLogoUrl' => WebsiteSetting::publicUrl($settings['footer_logo_path'] ?? null)
                ?? asset('darul-shofi/images/branding/darul-soffi-logo.jpg'),
        ]);
    }

    public function update(SettingsRequest $request, PrepareCompanyLogo $prepareCompanyLogo): RedirectResponse
    {
        $validated = $request->safe()->except([
            'company_logo',
            'remove_company_logo',
            'footer_logo',
            'remove_footer_logo',
            'tab',
        ]);
        $previousLogoPaths = $this->logoPaths();
        $previousFooterLogoPath = WebsiteSetting::getValue('footer_logo_path');
        $storedLogoPaths = null;
        $storedFooterLogoPath = null;

        if ($request->hasFile('company_logo')) {
            try {
                $storedLogoPaths = $prepareCompanyLogo->handle($request->file('company_logo'));
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages([
                    'company_logo' => $exception->getMessage(),
                ]);
            }
        }

        if ($request->hasFile('footer_logo')) {
            $storedFooterLogoPath = $request->file('footer_logo')->store('branding', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $storedLogoPaths, $storedFooterLogoPath, $request): void {
                foreach ($validated as $key => $value) {
                    WebsiteSetting::setValue($key, $value, $this->settingGroup($key));
                }

                if ($storedLogoPaths !== null) {
                    foreach ($storedLogoPaths as $key => $path) {
                        WebsiteSetting::setValue($key, $path, 'identity');
                    }

                    WebsiteSetting::setValue('company_logo_url', null, 'identity');
                } elseif ($request->boolean('remove_company_logo')) {
                    foreach (array_keys($this->logoPaths()) as $key) {
                        WebsiteSetting::setValue($key, null, 'identity');
                    }

                    WebsiteSetting::setValue('company_logo_url', null, 'identity');
                }

                if ($storedFooterLogoPath !== null) {
                    WebsiteSetting::setValue('footer_logo_path', $storedFooterLogoPath, 'identity');
                } elseif ($request->boolean('remove_footer_logo')) {
                    WebsiteSetting::setValue('footer_logo_path', null, 'identity');
                }

            });
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete(array_filter([
                ...($storedLogoPaths === null ? [] : array_values($storedLogoPaths)),
                $storedFooterLogoPath,
            ]));

            throw $throwable;
        }

        if ($storedLogoPaths !== null || $request->boolean('remove_company_logo')) {
            $this->deleteStoredLogos($previousLogoPaths);
        }

        if ($storedFooterLogoPath !== null || $request->boolean('remove_footer_logo')) {
            $this->deleteStoredFile($previousFooterLogoPath);
        }

        return redirect()
            ->route('admin.settings.edit', ['tab' => $request->string('tab')->toString() ?: 'identity'])
            ->with('success', 'Tetapan website berjaya dikemas kini.');
    }

    private function settingGroup(string $key): string
    {
        if (str_starts_with($key, 'homepage_') || str_starts_with($key, 'about_')) {
            return 'homepage';
        }

        if (in_array($key, ['terms_content', 'registered_address', 'motac_license_number', 'motac_license_valid_until', 'license_document_url'], true)) {
            return 'legal';
        }

        if (str_starts_with($key, 'contact_') || in_array($key, ['facebook_url', 'instagram_url', 'tiktok_url', 'threads_url', 'privacy_url', 'terms_url', 'public_contact_address', 'map_url', 'office_hours'], true)) {
            return 'contact';
        }

        return 'identity';
    }

    /**
     * @return array{company_logo_path: ?string, company_favicon_path: ?string, company_apple_touch_icon_path: ?string}
     */
    private function logoPaths(): array
    {
        return [
            'company_logo_path' => WebsiteSetting::getValue('company_logo_path'),
            'company_favicon_path' => WebsiteSetting::getValue('company_favicon_path'),
            'company_apple_touch_icon_path' => WebsiteSetting::getValue('company_apple_touch_icon_path'),
        ];
    }

    /**
     * @param  array{company_logo_path: ?string, company_favicon_path: ?string, company_apple_touch_icon_path: ?string}  $paths
     */
    private function deleteStoredLogos(array $paths): void
    {
        foreach ($paths as $path) {
            if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
