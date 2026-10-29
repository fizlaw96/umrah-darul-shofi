<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkMediaItemUpdateRequest extends FormRequest
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
            'section' => ['required', 'string', Rule::in(array_keys(MediaItem::homepageSections()))],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['array:id,package_category_id,is_published,show_on_homepage,image'],
            'items.*.id' => ['required', 'integer', 'distinct', Rule::exists('media_items', 'id')],
            'items.*.package_category_id' => ['nullable', 'integer', Rule::exists('package_categories', 'id')],
            'items.*.is_published' => ['nullable', 'boolean'],
            'items.*.show_on_homepage' => ['nullable', 'boolean'],
            'items.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
