<?php

namespace App\Http\Requests;

use App\Models\AqiqahKorbanService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AqiqahKorbanEnquiryRequest extends FormRequest
{
    public const BoyGender = 'Lelaki';

    public const GirlGender = 'Perempuan';

    private ?AqiqahKorbanService $selectedService = null;

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
        $isAqiqah = $this->input('category') === AqiqahKorbanService::Aqiqah;
        $isKorban = $this->input('category') === AqiqahKorbanService::Korban;

        return [
            'category' => ['required', Rule::in(AqiqahKorbanService::categories())],
            'service_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'participant_names' => [Rule::excludeIf(! $isKorban), 'required', 'array', 'min:1', 'max:200'],
            'participant_names.*' => [Rule::excludeIf(! $isKorban), 'string', 'max:255'],
            'child_name' => [Rule::excludeIf(! $isAqiqah), 'required', 'string', 'max:255'],
            'child_gender' => [Rule::excludeIf(! $isAqiqah), 'required', Rule::in([self::BoyGender, self::GirlGender])],
            'father_name' => [Rule::excludeIf(! $isAqiqah), 'nullable', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:40'],
            'user_location' => ['required', 'string', 'max:255'],
            'user_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'user_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'user_timezone' => ['nullable', 'timezone'],
            'requested_execution_date' => [Rule::excludeIf(! $isAqiqah), 'nullable', 'date_format:Y-m-d'],
            'important_information_acknowledged' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'participant_names.required' => 'Masukkan sekurang-kurangnya satu nama peserta korban.',
            'child_name.required' => 'Masukkan nama anak yang diaqiqahkan.',
            'child_gender.required' => 'Pilih jantina anak.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['category', 'service_id'])) {
                    return;
                }

                $service = $this->service();

                if ($service === null) {
                    $validator->errors()->add('service_id', 'Perkhidmatan ini tidak lagi dibuka untuk pendaftaran. Sila pilih tawaran lain.');

                    return;
                }

                if ($service->category !== $this->string('category')->toString()) {
                    $validator->errors()->add('service_id', 'Sila pilih perkhidmatan yang sepadan dengan kategori.');

                    return;
                }

                if ($service->category === AqiqahKorbanService::Korban && ! $validator->errors()->hasAny(['quantity', 'participant_names', 'participant_names.*'])) {
                    $maximumParticipants = $service->participantSlotsPerUnit() * $this->integer('quantity');

                    if (count($this->input('participant_names', [])) > $maximumParticipants) {
                        $validator->errors()->add('participant_names', "Pakej ini memuatkan maksimum {$maximumParticipants} nama peserta untuk kuantiti dipilih.");
                    }
                }
            },
            function (Validator $validator): void {
                if (blank($this->input('requested_execution_date')) || $validator->errors()->hasAny(['requested_execution_date', 'user_timezone'])) {
                    return;
                }

                if ($this->input('requested_execution_date') < $this->customerToday()->toDateString()) {
                    $validator->errors()->add('requested_execution_date', 'Tarikh pelaksanaan mestilah hari ini atau selepasnya.');
                }
            },
        ];
    }

    /**
     * The published service chosen by the customer, provided registration is open today.
     */
    public function service(): ?AqiqahKorbanService
    {
        if ($this->selectedService?->id === $this->integer('service_id')) {
            return $this->selectedService;
        }

        return $this->selectedService = AqiqahKorbanService::query()
            ->published()
            ->openForRegistration($this->customerToday())
            ->find($this->integer('service_id'));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $participantNames = $this->input('participant_names');

        if (is_string($participantNames)) {
            $this->merge([
                'participant_names' => collect(preg_split('/\R/', $participantNames) ?: [])
                    ->map(fn (string $name): string => trim($name))
                    ->filter()
                    ->values()
                    ->all(),
            ]);
        }
    }

    private function customerToday(): CarbonImmutable
    {
        $timezone = $this->input('user_timezone');

        return CarbonImmutable::today(is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true) ? $timezone : null);
    }
}
