<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\AqiqahKorbanEnquiryRequest;
use App\Models\AqiqahKorbanService;
use App\Models\ServiceEnquiry;
use App\Models\WebsiteSetting;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function aqiqahKorban(): View
    {
        $services = AqiqahKorbanService::query()
            ->published()
            ->openForRegistration()
            ->ordered()
            ->get();

        return view('services.aqiqah-korban', [
            'aqiqahServices' => $services->where('category', AqiqahKorbanService::Aqiqah)->values(),
            'korbanServices' => $services->where('category', AqiqahKorbanService::Korban)->values(),
            'availableCategories' => $services->pluck('category')->unique()->values(),
            'whatsAppConfigured' => $this->whatsAppNumber() !== null,
        ]);
    }

    public function prepareAqiqahKorban(AqiqahKorbanEnquiryRequest $request): View
    {
        $enquiry = $request->validated();
        $service = $request->service();
        $message = $this->message($service, $enquiry);

        ServiceEnquiry::query()->create([
            'service_type' => ServiceEnquiry::AqiqahKorbanType,
            'aqiqah_korban_service_id' => $service->id,
            'full_name' => $enquiry['full_name'],
            'contact_number' => $enquiry['contact_number'],
            'summary' => Str::limit($service->category.': '.$service->name.' × '.$enquiry['quantity'], 250),
            'details' => [
                ...collect($enquiry)->except(['important_information_acknowledged'])->all(),
                'service_name' => $service->name,
                'unit_price' => $service->price,
                'estimated_total' => $service->estimatedTotalLabel((int) $enquiry['quantity']),
            ],
            'message' => $message,
        ]);

        return view('services.aqiqah-korban-prepared', [
            'whatsAppUrl' => WebsiteSetting::whatsAppUrl($this->whatsAppNumber(), $message),
            'message' => $message,
        ]);
    }

    private function whatsAppNumber(): ?string
    {
        $number = WebsiteSetting::getValue('aqiqah_korban_use_general_whatsapp', '1') === '1'
            ? WebsiteSetting::getValue('contact_whatsapp')
            : WebsiteSetting::getValue('aqiqah_korban_whatsapp');

        return WebsiteSetting::normalizedWhatsAppNumber($number) === null ? null : $number;
    }

    /**
     * @param  array<string, mixed>  $enquiry
     */
    private function message(AqiqahKorbanService $service, array $enquiry): string
    {
        $isAqiqah = $service->category === AqiqahKorbanService::Aqiqah;
        $quantity = (int) $enquiry['quantity'];
        $estimatedTotal = $service->estimatedTotalLabel($quantity);

        return implode("\n", array_filter([
            'SALAMHAJI — AQIQAH & KORBAN',
            '',
            'Assalamualaikum.',
            'Saya ingin membuat pertanyaan berkenaan perkhidmatan Aqiqah/Korban.',
            '',
            'MAKLUMAT PELANGGAN',
            'Nama: '.$enquiry['full_name'],
            'Telefon: '.$enquiry['contact_number'],
            '',
            'LOKASI PELANGGAN',
            $enquiry['user_location'],
            isset($enquiry['user_latitude'], $enquiry['user_longitude'])
                ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($enquiry['user_latitude'].','.$enquiry['user_longitude'])
                : null,
            'Zon waktu pengguna: '.($enquiry['user_timezone'] ?? 'Tidak dapat dikesan'),
            '',
            'JENIS PERKHIDMATAN',
            $service->category,
            '',
            'PAKEJ DIPILIH',
            $service->name,
            '',
            'JENIS HAIWAN',
            $service->animal_type,
            '',
            'KUANTITI',
            $quantity.' × '.$service->quantity_description,
            '',
            ...($isAqiqah ? $this->aqiqahLines($enquiry) : $this->korbanLines($enquiry)),
            '',
            'HARGA DIPAPARKAN',
            $service->priceLabel().($service->price === null ? '' : ' seunit'),
            ...($estimatedTotal === null ? [] : ['Anggaran jumlah: '.$estimatedTotal.' ('.$quantity.' × '.$service->priceLabel().')']),
            '',
            'Mohon pihak SalamHaji mengesahkan harga, ketersediaan dan proses seterusnya.',
            'Terima kasih.',
        ], fn (?string $line): bool => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $enquiry
     * @return array<int, string>
     */
    private function aqiqahLines(array $enquiry): array
    {
        return array_values(array_filter([
            'BUTIRAN ANAK',
            'Nama anak: '.$enquiry['child_name'],
            'Jantina: '.$enquiry['child_gender'],
            filled($enquiry['father_name'] ?? null) ? 'Nama bapa: '.$enquiry['father_name'] : null,
            '',
            'TARIKH PELAKSANAAN DIMINTA',
            ($enquiry['requested_execution_date'] ?? null) ?: 'Tidak diminta',
        ], fn (?string $line): bool => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $enquiry
     * @return array<int, string>
     */
    private function korbanLines(array $enquiry): array
    {
        return [
            'NAMA PESERTA KORBAN ('.count($enquiry['participant_names']).')',
            ...collect($enquiry['participant_names'])->values()->map(fn (string $name, int $index): string => ($index + 1).'. '.$name)->all(),
        ];
    }
}
