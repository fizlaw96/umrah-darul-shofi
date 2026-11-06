<?php

namespace App\Http\Requests\Admin;

use App\Models\WebsiteSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AqiqahKorbanContactSettingsRequest extends FormRequest
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
            'aqiqah_korban_use_general_whatsapp' => ['nullable', 'boolean'],
            'aqiqah_korban_whatsapp' => [
                'nullable',
                'string',
                'max:40',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && WebsiteSetting::normalizedWhatsAppNumber($value) === null) {
                        $fail('Masukkan nombor WhatsApp antarabangsa yang sah.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->boolean('aqiqah_korban_use_general_whatsapp') && blank($this->input('aqiqah_korban_whatsapp'))) {
                    $validator->errors()->add('aqiqah_korban_whatsapp', 'Masukkan nombor WhatsApp khusus atau gunakan nombor rasmi sedia ada.');
                }
            },
        ];
    }
}
