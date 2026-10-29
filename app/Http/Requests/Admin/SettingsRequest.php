<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    private const COMPANY_LOGO_MAX_UPLOAD_KILOBYTES = 8192;

    private const COMPANY_LOGO_MAX_DIMENSION = 4096;

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
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.self::COMPANY_LOGO_MAX_UPLOAD_KILOBYTES,
                'dimensions:max_width='.self::COMPANY_LOGO_MAX_DIMENSION.',max_height='.self::COMPANY_LOGO_MAX_DIMENSION,
            ],
            'remove_company_logo' => ['nullable', 'boolean'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_footer_logo' => ['nullable', 'boolean'],
            'tab' => ['nullable', Rule::in(['identity', 'contact', 'homepage', 'media', 'legal'])],
            'company_legal_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_whatsapp' => ['nullable', 'string', 'max:40'],
            'office_address' => ['nullable', 'string', 'max:1000'],
            'office_hours' => ['nullable', 'string', 'max:255'],
            'registered_address' => ['nullable', 'string', 'max:1000'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'public_contact_address' => ['nullable', 'string', 'max:1000'],
            'map_url' => ['nullable', 'url', 'max:1000'],
            'motac_license_number' => ['nullable', 'string', 'max:120'],
            'motac_license_valid_until' => ['nullable', 'date'],
            'license_document_url' => ['nullable', 'url', 'max:1000'],
            'privacy_url' => ['nullable', 'url', 'max:255'],
            'terms_url' => ['nullable', 'url', 'max:255'],
            'homepage_company_intro' => ['nullable', 'string', 'max:1000'],
            'homepage_package_heading' => ['nullable', 'string', 'max:255'],
            'about_eyebrow' => ['nullable', 'string', 'max:120'],
            'about_heading' => ['nullable', 'string', 'max:255'],
            'about_description' => ['nullable', 'string', 'max:3000'],
            'about_cta_label' => ['nullable', 'string', 'max:80'],
            'about_cta_url' => ['nullable', 'url', 'max:255'],
            'terms_content' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /**
     * Get the custom validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_logo.image' => 'Logo utama mesti berupa imej JPEG, PNG atau WebP.',
            'company_logo.mimes' => 'Logo utama mesti berupa fail JPEG, PNG atau WebP.',
            'company_logo.max' => 'Logo utama mesti tidak melebihi 8 MB.',
            'company_logo.dimensions' => 'Logo utama tidak boleh melebihi 4,096 × 4,096 piksel.',
        ];
    }
}
