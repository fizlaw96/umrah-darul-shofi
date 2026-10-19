<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\PackageDeparture;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
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
            'package_id' => ['required', 'integer', Rule::exists('packages', 'id')],
            'package_departure_id' => ['required', 'integer', Rule::exists('package_departures', 'id')],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'privacy_consent' => ['accepted'],
            'idempotency_key' => ['required', 'uuid', Rule::unique('bookings', 'idempotency_key')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['package_id', 'package_departure_id'])) {
                    return;
                }

                $departure = PackageDeparture::query()->find($this->integer('package_departure_id'));

                if ($departure === null || $departure->package_id !== $this->integer('package_id')) {
                    $validator->errors()->add('package_departure_id', 'Tarikh berlepas tidak sepadan dengan pakej yang dipilih.');

                    return;
                }

                if ($departure->status !== 'open' || $departure->departure_date->isPast()) {
                    $validator->errors()->add('package_departure_id', 'Tarikh berlepas ini tidak lagi dibuka untuk permohonan.');

                    return;
                }

                $isRecentDuplicate = Booking::query()
                    ->where('package_departure_id', $departure->id)
                    ->where('phone', $this->string('phone')->toString())
                    ->where('email', $this->string('email')->lower()->toString())
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->exists();

                if ($isRecentDuplicate) {
                    $validator->errors()->add('email', 'Permohonan yang sama telah diterima. Sila hubungi kami jika anda memerlukan bantuan.');
                }
            },
        ];
    }
}
