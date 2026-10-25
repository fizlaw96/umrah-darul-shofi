<?php

namespace App\Http\Requests\Admin;

use App\Models\WebsiteSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MediaSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'featured_video_title' => ['nullable', 'string', 'max:255'],
            'featured_video_url' => ['nullable', 'url', 'max:1000'],
            'featured_video_file' => ['nullable', 'file', 'mimes:mp4,webm', 'max:'.config('darul_shofi.hero_video_max_kilobytes', 102400)],
            'remove_featured_video' => ['nullable', 'boolean'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'tiktok_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'threads_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $videoUrl = $this->string('featured_video_url')->trim()->toString();
                $videoFile = $this->file('featured_video_file');

                if (! $validator->errors()->hasAny(['featured_video_url', 'featured_video_file'])) {
                    if ($videoUrl !== '' && $videoFile !== null) {
                        $validator->errors()->add('featured_video_url', 'Pilih sama ada pautan YouTube atau fail video sahaja.');
                    }

                    if ($videoUrl !== '' && WebsiteSetting::featuredVideoEmbedUrl($videoUrl) === null) {
                        $validator->errors()->add('featured_video_url', 'Masukkan pautan video YouTube yang sah.');
                    }

                    $hasCurrentVideo = filled(WebsiteSetting::getValue('featured_video_path'))
                        || filled(WebsiteSetting::getValue('featured_video_url'));

                    if (($videoUrl !== '' || $videoFile !== null) && $hasCurrentVideo && ! $this->boolean('remove_featured_video')) {
                        $validator->errors()->add('featured_video_file', 'Keluarkan video semasa terlebih dahulu sebelum memilih video baharu.');
                    }
                }

                foreach (['instagram_url', 'tiktok_url', 'facebook_url', 'threads_url'] as $key) {
                    $value = $this->string($key)->trim()->toString();

                    if ($value !== '' && filter_var($value, FILTER_VALIDATE_URL) === false && preg_match('/^@?[A-Za-z0-9._-]+$/', $value) !== 1) {
                        $validator->errors()->add($key, 'Masukkan URL profil yang sah atau pengecam akaun sahaja.');
                    }
                }
            },
        ];
    }
}
