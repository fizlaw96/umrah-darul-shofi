<?php

namespace App\Http\Requests\Admin;

use App\Actions\VideoDurationInspector;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class HeroSlideRequest extends FormRequest
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
            'package_category_id' => ['nullable', 'integer', Rule::exists('package_categories', 'id')],
            'package_id' => ['nullable', 'integer', Rule::exists('packages', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url', 'max:255'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_video' => ['nullable', 'file', 'mimes:mp4,webm', 'max:'.config('darul_shofi.hero_video_max_kilobytes', 102400)],
            'remove_background_image' => ['nullable', 'boolean'],
            'remove_background_video' => ['nullable', 'boolean'],
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
                $video = $this->file('background_video');

                if ($video !== null && ! $validator->errors()->has('background_video')) {
                    $duration = app(VideoDurationInspector::class)->durationInSeconds($video);

                    if ($duration === null) {
                        $validator->errors()->add('background_video', 'Tempoh video tidak dapat disahkan. Pastikan FFprobe tersedia pada pelayan.');
                    } elseif ($duration > 10) {
                        $validator->errors()->add('background_video', 'Video hero mesti berdurasi maksimum 10 saat.');
                    }
                }

                if ($this->isMethod('post') && $this->file('background_image') === null && $video === null) {
                    $validator->errors()->add('background_image', 'Muat naik sekurang-kurangnya satu imej poster atau video untuk hero.');
                }
            },
        ];
    }
}
