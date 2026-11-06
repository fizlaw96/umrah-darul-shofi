<?php

namespace App\Http\Requests\Admin;

use App\Models\WebsiteSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransportSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transport_enabled' => ['nullable', 'boolean'],
            'transport_whatsapp' => [
                'nullable',
                'string',
                'max:40',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && WebsiteSetting::normalizedWhatsAppNumber($value) === null) {
                        $fail('Masukkan nombor WhatsApp antarabangsa yang sah.');
                    }
                },
            ],
            'transport_intro' => ['nullable', 'string', 'max:2000'],
            'transport_coverage_info' => ['nullable', 'string', 'max:2000'],
            'transport_operating_notes' => ['nullable', 'string', 'max:1000'],
            'transport_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_transport_banner' => ['nullable', 'boolean'],
            'transport_vehicle_options' => ['required', 'array', 'max:'.(WebsiteSetting::MaximumTransportVehicleOptions + 1)],
            'transport_vehicle_options.*.key' => ['nullable', 'string', 'max:80'],
            'transport_vehicle_options.*.name' => ['nullable', 'string', 'max:80'],
            'transport_vehicle_options.*.description' => ['nullable', 'string', 'max:120'],
            'transport_vehicle_options.*.image' => ['required', 'string', Rule::in(array_keys(WebsiteSetting::transportVehicleImages()))],
            'transport_vehicle_options.*.capacity' => ['nullable', 'integer', 'min:1', 'max:60'],
            'transport_vehicle_options.*.enabled' => ['nullable', 'boolean'],
            'transport_vehicle_options.*.remove' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('transport_vehicle_options*')) {
                    return;
                }

                $keptOptions = WebsiteSetting::normalizeTransportVehicleOptions((array) $this->input('transport_vehicle_options', []));
                $submittedCount = collect((array) $this->input('transport_vehicle_options', []))
                    ->filter(fn (mixed $option): bool => is_array($option) && filled($option['name'] ?? null) && ! filter_var($option['remove'] ?? false, FILTER_VALIDATE_BOOLEAN))
                    ->count();

                if ($keptOptions === []) {
                    $validator->errors()->add('transport_vehicle_options', 'Simpan sekurang-kurangnya satu pilihan kenderaan.');
                } elseif ($submittedCount > WebsiteSetting::MaximumTransportVehicleOptions) {
                    $validator->errors()->add('transport_vehicle_options', 'Maksimum '.WebsiteSetting::MaximumTransportVehicleOptions.' pilihan kenderaan boleh disimpan.');
                }
            },
        ];
    }
}
