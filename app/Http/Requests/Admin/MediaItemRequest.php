<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaItem;
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
                'images' => ['required', 'array', 'min:1', 'max:18'],
                'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'is_published' => ['nullable', 'boolean'],
            ];
        }

        return [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->isMethod('post') || $validator->errors()->hasAny(['section', 'images'])) {
                    return;
                }

                $section = $this->string('section')->toString();
                $maximum = MediaItem::sectionLimit($section);
                $currentCount = MediaItem::query()->forSection($section)->count();
                $uploadedCount = count($this->file('images', []));

                if ($currentCount + $uploadedCount > $maximum) {
                    $label = MediaItem::homepageSections()[$section]['label'];
                    $remaining = $maximum - $currentCount;

                    $validator->errors()->add(
                        'images',
                        "Bahagian {$label} hanya boleh memaparkan maksimum {$maximum} gambar. Baki slot: {$remaining}.",
                    );
                }
            },
        ];
    }
}
