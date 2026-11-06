<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AqiqahKorbanContactSettingsRequest;
use App\Http\Requests\Admin\AqiqahKorbanServiceRequest;
use App\Models\AqiqahKorbanService;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AqiqahKorbanServiceController extends Controller
{
    public function index(): View
    {
        $servicesQuery = AqiqahKorbanService::query();

        return view('admin.aqiqah-korban-services.index', [
            'services' => (clone $servicesQuery)
                ->ordered()
                ->paginate(20),
            'serviceCounts' => [
                'all' => (clone $servicesQuery)->count(),
                'aqiqah' => (clone $servicesQuery)->where('category', AqiqahKorbanService::Aqiqah)->count(),
                'korban' => (clone $servicesQuery)->where('category', AqiqahKorbanService::Korban)->count(),
                'published' => (clone $servicesQuery)->published()->count(),
            ],
            'contactSettings' => $this->contactSettings(),
        ]);
    }

    public function create(): View
    {
        return view('admin.aqiqah-korban-services.create', [
            'service' => new AqiqahKorbanService,
        ]);
    }

    public function store(AqiqahKorbanServiceRequest $request): RedirectResponse
    {
        $service = new AqiqahKorbanService;
        $this->save($request, $service);

        return redirect()->route('admin.aqiqah-korban-services.index')->with('success', 'Perkhidmatan Aqiqah & Korban berjaya ditambah.');
    }

    public function show(AqiqahKorbanService $aqiqahKorbanService): RedirectResponse
    {
        return redirect()->route('admin.aqiqah-korban-services.edit', $aqiqahKorbanService);
    }

    public function edit(AqiqahKorbanService $aqiqahKorbanService): View
    {
        return view('admin.aqiqah-korban-services.edit', [
            'service' => $aqiqahKorbanService,
        ]);
    }

    public function update(AqiqahKorbanServiceRequest $request, AqiqahKorbanService $aqiqahKorbanService): RedirectResponse
    {
        $this->save($request, $aqiqahKorbanService);

        return redirect()->route('admin.aqiqah-korban-services.index')->with('success', 'Perkhidmatan Aqiqah & Korban berjaya dikemas kini.');
    }

    public function destroy(AqiqahKorbanService $aqiqahKorbanService): RedirectResponse
    {
        $this->deleteImage($aqiqahKorbanService->featured_image);
        $aqiqahKorbanService->delete();

        return back()->with('success', 'Perkhidmatan Aqiqah & Korban berjaya dipadamkan.');
    }

    public function updateContact(AqiqahKorbanContactSettingsRequest $request): RedirectResponse
    {
        WebsiteSetting::setValue(
            'aqiqah_korban_use_general_whatsapp',
            $request->boolean('aqiqah_korban_use_general_whatsapp') ? '1' : '0',
            'aqiqah-korban',
        );
        WebsiteSetting::setValue(
            'aqiqah_korban_whatsapp',
            WebsiteSetting::normalizedWhatsAppNumber($request->input('aqiqah_korban_whatsapp')),
            'aqiqah-korban',
        );

        return redirect()->route('admin.aqiqah-korban-services.index')->with('success', 'Tetapan WhatsApp Aqiqah & Korban berjaya dikemas kini.');
    }

    private function save(AqiqahKorbanServiceRequest $request, AqiqahKorbanService $service): void
    {
        $values = $request->safe()->except(['featured_image', 'remove_featured_image', 'is_published']);

        $values['is_published'] = $request->boolean('is_published');
        $values['sort_order'] = $request->integer('sort_order');
        $values['promotion_badge'] = filled($values['promotion_badge'] ?? null)
            ? $values['promotion_badge']
            : null;

        if ($request->input('category') !== AqiqahKorbanService::Korban) {
            $values['korban_season'] = null;
            $values['registration_opens_on'] = null;
            $values['registration_closes_on'] = null;
            $values['registration_period'] = null;
        }

        $oldImage = $service->featured_image;

        if ($request->hasFile('featured_image')) {
            $values['featured_image'] = $request->file('featured_image')->store('aqiqah-korban', 'public');
        } elseif ($request->boolean('remove_featured_image')) {
            $values['featured_image'] = null;
        }

        $service->fill($values);
        $service->save();

        if ($oldImage !== $service->featured_image) {
            $this->deleteImage($oldImage);
        }
    }

    /**
     * @return array{aqiqah_korban_use_general_whatsapp: string|null, aqiqah_korban_whatsapp: string|null, contact_whatsapp: string|null}
     */
    private function contactSettings(): array
    {
        return [
            'aqiqah_korban_use_general_whatsapp' => WebsiteSetting::getValue('aqiqah_korban_use_general_whatsapp', '1'),
            'aqiqah_korban_whatsapp' => WebsiteSetting::getValue('aqiqah_korban_whatsapp'),
            'contact_whatsapp' => WebsiteSetting::getValue('contact_whatsapp'),
        ];
    }

    private function deleteImage(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
