<?php

namespace App\Http\Requests\Admin;

use App\Models\Package;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PackageRequest extends FormRequest
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
        $package = $this->route('package');

        return [
            'package_category_id' => ['required', 'integer', Rule::exists('package_categories', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80', Rule::unique('packages', 'code')->ignore($package instanceof Package ? $package->id : null)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('packages', 'slug')->ignore($package instanceof Package ? $package->id : null)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:20000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'array', 'max:3'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_image_path' => ['nullable', 'string', 'max:255'],
            'gallery_primary_index' => ['nullable', 'integer', 'min:0', 'max:2'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'room_prices' => ['nullable', 'array', 'max:5'],
            'room_prices.*' => ['array'],
            'room_prices.*.room_type' => ['nullable', 'string', 'max:120'],
            'room_prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'room_prices.*.hotel_key' => ['nullable', 'uuid'],
            'room_prices.*.existing_image' => ['nullable', 'string', 'max:255'],
            'room_prices.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:60'],
            'duration_nights' => ['required', 'integer', 'min:0', 'max:59'],
            'season_label' => ['nullable', 'string', Rule::in($this->selectableSeasonYears())],
            'departure_airport' => ['nullable', 'string', 'max:120'],
            'arrival_airport' => ['nullable', 'string', 'max:120'],
            'airline' => ['nullable', 'string', 'max:120'],
            'proposed_departure_date' => ['nullable', 'date'],
            'makkah_nights' => ['nullable', 'integer', 'min:0', 'max:59'],
            'madinah_nights' => ['nullable', 'integer', 'min:0', 'max:59'],
            'makkah_hotel' => ['nullable', 'string', 'max:255'],
            'makkah_hotel_rating' => ['nullable', 'integer', 'between:1,5'],
            'madinah_hotel' => ['nullable', 'string', 'max:255'],
            'madinah_hotel_rating' => ['nullable', 'integer', 'between:1,5'],
            'walking_distance' => ['nullable', 'string', 'max:255'],
            'hotel_information' => ['nullable', 'string'],
            'flight_information' => ['nullable', 'string'],
            'flight_options_present' => ['nullable', 'boolean'],
            'flight_options' => ['nullable', 'array', 'max:6'],
            'flight_options.*' => ['array'],
            'flight_options.*.airline' => ['nullable', 'string', 'max:120'],
            'flight_options.*.departure_airport' => ['nullable', 'string', 'max:120'],
            'flight_options.*.arrival_airport' => ['nullable', 'string', 'max:120'],
            'flight_options.*.proposed_departure_date' => ['nullable', 'date'],
            'flight_options.*.notes' => ['nullable', 'string', 'max:2000'],
            'flight_options.*.existing_images' => ['nullable', 'array', 'max:4'],
            'flight_options.*.existing_images.*' => ['nullable', 'string', 'max:255'],
            'flight_options.*.images' => ['nullable', 'array', 'max:4'],
            'flight_options.*.images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hotel_options_present' => ['nullable', 'boolean'],
            'hotel_options' => ['nullable', 'array', 'max:6'],
            'hotel_options.*' => ['array'],
            'hotel_options.*.key' => ['nullable', 'uuid', 'distinct'],
            'hotel_options.*.location' => ['nullable', Rule::in(['Makkah', 'Madinah'])],
            'hotel_options.*.name' => ['nullable', 'string', 'max:255'],
            'hotel_options.*.rating' => ['nullable', 'integer', 'between:1,5'],
            'hotel_options.*.nights' => ['nullable', 'integer', 'min:0', 'max:59'],
            'hotel_options.*.walking_distance' => ['nullable', 'string', 'max:255'],
            'hotel_options.*.notes' => ['nullable', 'string', 'max:2000'],
            'hotel_options.*.existing_images' => ['nullable', 'array', 'max:4'],
            'hotel_options.*.existing_images.*' => ['nullable', 'string', 'max:255'],
            'hotel_options.*.images' => ['nullable', 'array', 'max:4'],
            'hotel_options.*.images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'ziarah_places' => ['nullable', 'array', 'max:20'],
            'ziarah_places.*' => ['array'],
            'ziarah_places.*.name' => ['nullable', 'string', 'max:120'],
            'ziarah_places.*.location' => ['nullable', 'string', 'max:120'],
            'ziarah_places.*.description' => ['nullable', 'string', 'max:1000'],
            'ziarah_places.*.existing_image' => ['nullable', 'string', 'max:255'],
            'ziarah_places.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gifts' => ['nullable', 'array', 'max:5'],
            'gifts.*' => ['array'],
            'gifts.*.title' => ['nullable', 'string', 'max:120'],
            'gifts.*.description' => ['nullable', 'string', 'max:1000'],
            'gifts.*.existing_image' => ['nullable', 'string', 'max:255'],
            'gifts.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'itinerary_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_itinerary_pdf' => ['nullable', 'boolean'],
            'service_options_present' => ['nullable', 'boolean'],
            'package_services' => ['nullable', 'array', 'max:20'],
            'package_services.*' => ['array'],
            'package_services.*.name' => ['nullable', 'string', 'max:120'],
            'package_services.*.included' => ['nullable', 'boolean'],
            'package_services.*.excluded' => ['nullable', 'boolean'],
            'itinerary' => ['nullable', 'string'],
            'itinerary_days' => ['nullable', 'array', 'max:60'],
            'itinerary_days.*' => ['array'],
            'itinerary_days.*.title' => ['nullable', 'string', 'max:255'],
            'itinerary_days.*.description' => ['nullable', 'string', 'max:3000'],
            'departure_rows' => ['nullable', 'array', 'max:12'],
            'departure_rows.*' => ['array'],
            'departure_rows.*.id' => ['nullable', 'integer', Rule::exists('package_departures', 'id')],
            'departure_rows.*.departure_date' => ['nullable', 'date'],
            'departure_rows.*.return_date' => ['nullable', 'date'],
            'departure_rows.*.departure_airport' => ['nullable', 'string', 'max:120'],
            'departure_rows.*.price' => ['nullable', 'numeric', 'min:0'],
            'departure_rows.*.capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'departure_rows.*.status' => ['nullable', Rule::in(['draft', 'open', 'closed', 'cancelled'])],
            'is_featured' => ['nullable', 'boolean'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'intent' => ['nullable', Rule::in(['draft', 'publish'])],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireCompleteRows($validator, 'room_prices', [
                    'room_type' => 'Jenis bilik',
                    'price' => 'Harga seorang',
                    'hotel_key' => 'Hotel penginapan',
                ]);
                $this->validateRoomHotelKeys($validator);
                $this->requireCompleteRows($validator, 'ziarah_places', [
                    'name' => 'Nama tempat ziarah',
                ]);
                $this->requireCompleteRows($validator, 'gifts', [
                    'title' => 'Nama hadiah',
                ]);
                $this->rejectOverlappingPackageServices($validator);
                $this->requireCompleteRows($validator, 'flight_options', [
                    'airline' => 'Syarikat penerbangan',
                ]);
                $this->requireCompleteRows($validator, 'hotel_options', [
                    'name' => 'Nama hotel',
                ]);
                $this->requireCompleteRows($validator, 'departure_rows', [
                    'departure_date' => 'Tarikh pergi',
                    'return_date' => 'Tarikh balik',
                    'price' => 'Harga seorang',
                ]);
                $this->validateDepartureRows($validator);
            },
        ];
    }

    /**
     * @param  array<string, string>  $requiredFields
     */
    private function requireCompleteRows(Validator $validator, string $input, array $requiredFields): void
    {
        $rows = $this->input($input, []);

        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $hasValue = collect($row)->contains(fn (mixed $value): bool => filled($value))
                || $this->hasFile("{$input}.{$index}.image")
                || $this->hasFile("{$input}.{$index}.images");

            if (! $hasValue) {
                continue;
            }

            foreach ($requiredFields as $field => $label) {
                if (blank($row[$field] ?? null)) {
                    $validator->errors()->add("{$input}.{$index}.{$field}", "{$label} diperlukan apabila satu baris diisi.");
                }
            }
        }
    }

    private function rejectOverlappingPackageServices(Validator $validator): void
    {
        $packageServices = $this->input('package_services', []);

        if (! is_array($packageServices)) {
            return;
        }

        foreach ($packageServices as $index => $packageService) {
            if (! is_array($packageService)) {
                continue;
            }

            if (filter_var($packageService['included'] ?? false, FILTER_VALIDATE_BOOLEAN)
                && filter_var($packageService['excluded'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $validator->errors()->add("package_services.{$index}.included", 'Satu perkhidmatan hanya boleh dipilih sebagai termasuk atau tidak termasuk.');
            }
        }
    }

    private function validateDepartureRows(Validator $validator): void
    {
        $departureRows = $this->input('departure_rows', []);

        if (! is_array($departureRows)) {
            return;
        }

        $departureDates = [];

        foreach ($departureRows as $index => $departureRow) {
            if (! is_array($departureRow) || blank($departureRow['departure_date'] ?? null)) {
                continue;
            }

            $departureDate = (string) $departureRow['departure_date'];
            $returnDate = $departureRow['return_date'] ?? null;

            if (filled($returnDate) && $returnDate < $departureDate) {
                $validator->errors()->add("departure_rows.{$index}.return_date", 'Tarikh balik mesti pada atau selepas tarikh pergi.');
            }

            if (in_array($departureDate, $departureDates, true)) {
                $validator->errors()->add("departure_rows.{$index}.departure_date", 'Setiap jadual berlepas perlu menggunakan tarikh pergi yang berbeza.');
            }

            $status = $departureRow['status'] ?? 'draft';

            if ($status === 'open' && blank($departureRow['capacity'] ?? null)) {
                $validator->errors()->add("departure_rows.{$index}.capacity", 'Kapasiti diperlukan untuk membuka jadual berlepas kepada pelanggan.');
            }

            $departureDates[] = $departureDate;
        }
    }

    /** @return array<int, string> */
    private function selectableSeasonYears(): array
    {
        return array_map(
            fn (int $year): string => (string) $year,
            range(now()->year, now()->addYears(2)->year),
        );
    }

    private function validateRoomHotelKeys(Validator $validator): void
    {
        $roomPrices = $this->input('room_prices', []);
        $hotelOptions = $this->input('hotel_options', []);

        if (! is_array($roomPrices) || ! is_array($hotelOptions)) {
            return;
        }

        $hotelKeys = collect($hotelOptions)
            ->filter(fn (mixed $hotel): bool => is_array($hotel) && filled($hotel['name'] ?? null))
            ->pluck('key')
            ->filter(fn (mixed $key): bool => is_string($key) && filled($key))
            ->all();

        foreach ($roomPrices as $index => $roomPrice) {
            if (! is_array($roomPrice) || blank($roomPrice['hotel_key'] ?? null)) {
                continue;
            }

            if (! in_array($roomPrice['hotel_key'], $hotelKeys, true)) {
                $validator->errors()->add("room_prices.{$index}.hotel_key", 'Hotel penginapan yang dipilih tidak sah.');
            }
        }
    }
}
