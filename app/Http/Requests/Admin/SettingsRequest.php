<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_whatsapp' => ['nullable', 'string', 'max:40'],
            'office_address' => ['nullable', 'string', 'max:1000'],
            'office_hours' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'company_logo_url' => ['nullable', 'url', 'max:255'],
            'privacy_url' => ['nullable', 'url', 'max:255'],
            'terms_url' => ['nullable', 'url', 'max:255'],
            'homepage_company_intro' => ['nullable', 'string', 'max:1000'],
            'homepage_package_heading' => ['nullable', 'string', 'max:255'],
            'homepage_hero_videos' => ['nullable', 'string', 'max:10000'],
            'homepage_partner_logos' => ['nullable', 'string', 'max:10000'],
            'homepage_about_images' => ['nullable', 'string', 'max:10000'],
            'homepage_gallery_images' => ['nullable', 'string', 'max:20000'],
            'homepage_service_categories' => ['nullable', 'string', 'max:3000'],
            'homepage_campaign_title' => ['nullable', 'string', 'max:255'],
            'homepage_campaign_description' => ['nullable', 'string', 'max:1000'],
            'homepage_campaign_link' => ['nullable', 'url', 'max:255'],
            'homepage_campaign_cta' => ['nullable', 'string', 'max:80'],
            'homepage_video_url' => ['nullable', 'url', 'max:255'],
            'homepage_video_cover' => ['nullable', 'url', 'max:255'],
        ];
    }
}
