<?php

namespace App\Http\Requests\Admin;

use App\Models\Package;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'slug' => ['required', 'string', 'max:255', Rule::unique('packages', 'slug')->ignore($package instanceof Package ? $package->id : null)],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array', 'max:8'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:60'],
            'duration_nights' => ['required', 'integer', 'min:0', 'max:59'],
            'makkah_nights' => ['nullable', 'integer', 'min:0', 'max:59'],
            'madinah_nights' => ['nullable', 'integer', 'min:0', 'max:59'],
            'hotel_information' => ['nullable', 'string'],
            'flight_information' => ['nullable', 'string'],
            'inclusions' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'string'],
            'itinerary' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
