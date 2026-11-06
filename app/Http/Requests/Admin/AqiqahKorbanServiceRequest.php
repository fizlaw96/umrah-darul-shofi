<?php

namespace App\Http\Requests\Admin;

use App\Models\AqiqahKorbanService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AqiqahKorbanServiceRequest extends FormRequest
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
            'category' => ['required', Rule::in(AqiqahKorbanService::categories())],
            'name' => ['required', 'string', 'max:255'],
            'animal_type' => ['required', Rule::in(AqiqahKorbanService::animalTypes())],
            'quantity_description' => ['required', Rule::in(AqiqahKorbanService::quantityDescriptions())],
            'service_location' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'full_description' => ['nullable', 'string', 'max:5000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'promotion_badge' => ['nullable', Rule::in(array_keys(AqiqahKorbanService::promotionBadges()))],
            'korban_season' => [Rule::excludeIf($this->input('category') !== AqiqahKorbanService::Korban), 'nullable', 'string', 'max:255'],
            'registration_opens_on' => [Rule::excludeIf($this->input('category') !== AqiqahKorbanService::Korban), 'nullable', 'date_format:Y-m-d'],
            'registration_closes_on' => [Rule::excludeIf($this->input('category') !== AqiqahKorbanService::Korban), 'nullable', 'date_format:Y-m-d', Rule::when(filled($this->input('registration_opens_on')), ['after_or_equal:registration_opens_on'])],
            'registration_period' => [Rule::excludeIf($this->input('category') !== AqiqahKorbanService::Korban), 'nullable', 'string', 'max:255'],
            'additional_terms' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
