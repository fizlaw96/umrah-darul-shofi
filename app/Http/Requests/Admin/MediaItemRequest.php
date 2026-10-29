<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaItem;
use App\Models\PackageCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MediaItemRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'section' => ['required', 'string', Rule::in(array_keys(MediaItem::homepageSections()))],
                'gallery_destination' => ['nullable', 'string', 'max:32'],
                'package_category_id' => ['nullable', 'integer', Rule::exists('package_categories', 'id')],
                'images' => ['required', 'array', 'min:1', 'max:18'],
                'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'is_published' => ['nullable', 'boolean'],
                'show_on_homepage' => ['nullable', 'boolean'],
            ];
        }

        return [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'package_category_id' => ['nullable', 'integer', Rule::exists('package_categories', 'id')],
            'is_published' => ['nullable', 'boolean'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'return_section' => ['nullable', 'string', Rule::in(array_keys(MediaItem::homepageSections()))],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $section = $this->isMethod('post')
                    ? $this->string('section')->toString()
                    : $this->route('medium')?->section;

                if ($this->isMethod('post') && $section === MediaItem::FullGallerySection) {
                    $destination = $this->string('gallery_destination')->toString();

                    if ($destination === MediaItem::AboutSection) {
                        return;
                    }

                    $packageCategoryId = filled($destination)
                        ? filter_var($destination, FILTER_VALIDATE_INT)
                        : $this->integer('package_category_id');

                    if ($packageCategoryId === false || $packageCategoryId === 0 || ! PackageCategory::query()->active()->whereKey($packageCategoryId)->exists()) {
                        $validator->errors()->add('gallery_destination', 'Pilih Tentang Kami atau kategori pakej yang aktif untuk galeri keseluruhan.');
                    }
                }

                if (! $this->isMethod('post') || $validator->errors()->hasAny(['section', 'images'])) {
                    return;
                }
            },
        ];
    }
}
