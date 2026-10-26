<?php

namespace App\Http\Requests\Admin;

use App\Actions\VideoDurationInspector;
use App\Models\PackageCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PackageCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('name', '')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('package_category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('package_categories', 'slug')->ignore($category instanceof PackageCategory ? $category->id : null)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_video' => ['nullable', 'file', 'mimes:mp4,webm', 'max:'.config('darul_shofi.hero_video_max_kilobytes', 102400)],
            'remove_background_image' => ['nullable', 'boolean'],
            'remove_background_video' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = $this->route('package_category');
                $heroSlide = $category instanceof PackageCategory ? $category->heroSlide : null;
                $hasImage = $this->hasFile('background_image')
                    || (filled($heroSlide?->background_image) && ! $this->boolean('remove_background_image'));
                $hasVideo = $this->hasFile('background_video')
                    || (filled($heroSlide?->background_video) && ! $this->boolean('remove_background_video'));

                if ($hasImage && $hasVideo) {
                    $validator->errors()->add('background_image', 'Pilih satu sahaja: poster imej atau video hero.');

                    return;
                }

                $video = $this->file('background_video');

                if ($video === null || $validator->errors()->has('background_video')) {
                    return;
                }

                $inspector = app(VideoDurationInspector::class);
                $duration = $inspector->durationInSeconds($video);

                if ($duration === null && $inspector->canInspect()) {
                    $validator->errors()->add('background_video', 'Tempoh video tidak dapat disahkan. Pastikan FFprobe tersedia pada pelayan.');
                } elseif ($duration !== null && $duration > 10) {
                    $validator->errors()->add('background_video', 'Video hero mesti berdurasi maksimum 10 saat.');
                }
            },
        ];
    }
}
