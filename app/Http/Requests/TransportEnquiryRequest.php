<?php

namespace App\Http\Requests;

use App\GoogleMapsLocation;
use App\Models\WebsiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransportEnquiryRequest extends FormRequest
{
    /**
     * @var array<int, array{key: string, name: string, description: string, image: string, capacity: int|null, enabled: bool}>|null
     */
    private ?array $enabledVehicleOptions = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:40'],
            'user_timezone' => ['nullable', 'timezone'],
            'request_type' => ['required', Rule::in(['scheduled', 'immediate'])],
            'pickup_date' => [Rule::requiredIf($this->input('request_type') === 'scheduled'), 'nullable', 'date_format:Y-m-d'],
            'pickup_time' => [Rule::requiredIf($this->input('request_type') === 'scheduled'), 'nullable', 'date_format:H:i'],
            'pickup_place' => ['required', 'string', 'max:255'],
            'pickup_address' => ['nullable', 'string', 'max:1000'],
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'pickup_google_maps_url' => $this->googleMapsUrlRules(),
            'pickup_confirmed' => ['accepted'],
            'destination_place' => ['required', 'string', 'max:255'],
            'destination_address' => ['nullable', 'string', 'max:1000'],
            'destination_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_google_maps_url' => $this->googleMapsUrlRules(),
            'destination_confirmed' => ['accepted'],
            'adults' => ['required', 'integer', 'min:1', 'max:100'],
            'children' => ['nullable', 'integer', 'min:0', 'max:100'],
            'luggage_items' => ['nullable', 'integer', 'min:0', 'max:100'],
            'vehicle_type' => ['nullable', 'string', 'max:120', Rule::in(array_column($this->enabledVehicleOptions(), 'name'))],
            'pickup_instructions' => ['nullable', 'string', 'max:1000'],
            'important_information_acknowledged' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'adults.min' => 'Sekurang-kurangnya seorang penumpang dewasa diperlukan.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('request_type') !== 'scheduled' || $validator->errors()->hasAny(['pickup_date', 'pickup_time', 'user_timezone'])) {
                    return;
                }

                $pickupAt = CarbonImmutable::createFromFormat('Y-m-d H:i', $this->input('pickup_date').' '.$this->input('pickup_time'), $this->customerTimezone());

                if ($pickupAt instanceof CarbonImmutable && $pickupAt->isPast()) {
                    $validator->errors()->add('pickup_time', 'Tarikh dan masa pickup telah berlalu mengikut zon waktu anda. Sila pilih masa akan datang.');
                }
            },
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['vehicle_type', 'adults', 'children'])) {
                    return;
                }

                $capacity = $this->selectedVehicle()['capacity'] ?? null;
                $passengers = $this->integer('adults') + $this->integer('children');

                if ($capacity !== null && $passengers > $capacity) {
                    $validator->errors()->add('vehicle_type', "Kenderaan ini memuatkan maksimum {$capacity} penumpang. Sila pilih kenderaan yang lebih besar atau biarkan operator mencadangkan.");
                }
            },
        ];
    }

    /**
     * Timezone the customer's device reported, falling back to the application timezone.
     */
    public function customerTimezone(): string
    {
        $timezone = $this->input('user_timezone');

        return is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true)
            ? $timezone
            : (string) config('app.timezone');
    }

    /**
     * @return array{key: string, name: string, description: string, image: string, capacity: int|null, enabled: bool}|null
     */
    public function selectedVehicle(): ?array
    {
        $vehicleType = $this->input('vehicle_type');

        if (blank($vehicleType)) {
            return null;
        }

        return collect($this->enabledVehicleOptions())->firstWhere('name', $vehicleType);
    }

    /**
     * @return array<int, array{key: string, name: string, description: string, image: string, capacity: int|null, enabled: bool}>
     */
    private function enabledVehicleOptions(): array
    {
        return $this->enabledVehicleOptions ??= collect(WebsiteSetting::transportVehicleOptions(WebsiteSetting::getValue('transport_vehicle_options')))
            ->filter(fn (array $vehicle): bool => $vehicle['enabled'])
            ->values()
            ->all();
    }

    /**
     * @return array<int, ValidationRule|callable|string>
     */
    private function googleMapsUrlRules(): array
    {
        return [
            'nullable',
            'url',
            'max:1000',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (filled($value) && ! GoogleMapsLocation::isSupportedUrl($value)) {
                    $fail('Hanya pautan Google Maps HTTPS yang disokong dibenarkan.');
                }
            },
        ];
    }
}
