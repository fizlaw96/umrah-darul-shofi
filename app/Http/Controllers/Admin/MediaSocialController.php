<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaSocialRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class MediaSocialController extends Controller
{
    public function edit(): View
    {
        $settings = array_merge(
            WebsiteSetting::siteDefaults(),
            WebsiteSetting::query()->pluck('value', 'key')->all(),
        );
        $settings['threads_url'] = WebsiteSetting::threadsComUrl($settings['threads_url']);

        return view('admin.media-social.edit', [
            'settings' => $settings,
            'featuredVideoUploadUrl' => WebsiteSetting::publicUrl($settings['featured_video_path']),
            'featuredVideoEmbedUrl' => WebsiteSetting::featuredVideoEmbedUrl($settings['featured_video_url']),
        ]);
    }

    public function update(MediaSocialRequest $request): RedirectResponse
    {
        $values = $request->safe()->except([
            'featured_video_file',
            'remove_featured_video',
            'featured_video_url',
        ]);

        foreach (['instagram_url', 'tiktok_url', 'facebook_url', 'threads_url'] as $key) {
            $values[$key] = $this->socialProfileUrl($key, $values[$key] ?? null);
        }

        $previousVideoPath = WebsiteSetting::getValue('featured_video_path');
        $previousThumbnailPath = WebsiteSetting::getValue('featured_video_thumbnail_path');
        $storedVideoPath = $request->hasFile('featured_video_file')
            ? $request->file('featured_video_file')->store('homepage/video', 'public')
            : null;
        $videoUrl = $request->string('featured_video_url')->trim()->toString();
        $shouldRemoveVideo = $request->boolean('remove_featured_video');

        try {
            DB::transaction(function () use ($values, $storedVideoPath, $videoUrl, $shouldRemoveVideo): void {
                foreach ($values as $key => $value) {
                    WebsiteSetting::setValue($key, $value, 'media');
                }

                if ($storedVideoPath !== null) {
                    WebsiteSetting::setValue('featured_video_path', $storedVideoPath, 'media');
                    WebsiteSetting::setValue('featured_video_url', null, 'media');
                } elseif ($videoUrl !== '') {
                    WebsiteSetting::setValue('featured_video_url', $videoUrl, 'media');
                    WebsiteSetting::setValue('featured_video_path', null, 'media');
                } elseif ($shouldRemoveVideo) {
                    WebsiteSetting::setValue('featured_video_url', null, 'media');
                    WebsiteSetting::setValue('featured_video_path', null, 'media');
                }

                WebsiteSetting::query()->where('key', 'featured_video_thumbnail_path')->delete();
            });
        } catch (Throwable $throwable) {
            if ($storedVideoPath !== null) {
                Storage::disk('public')->delete($storedVideoPath);
            }

            throw $throwable;
        }

        if (($storedVideoPath !== null || $videoUrl !== '' || $shouldRemoveVideo) && filled($previousVideoPath) && ! Str::startsWith($previousVideoPath, ['http://', 'https://'])) {
            Storage::disk('public')->delete($previousVideoPath);
        }

        if (filled($previousThumbnailPath) && ! Str::startsWith($previousThumbnailPath, ['http://', 'https://'])) {
            Storage::disk('public')->delete($previousThumbnailPath);
        }

        return redirect()->route('admin.media-social.edit')->with('success', 'Tetapan video dan media sosial berjaya dikemas kini.');
    }

    private function socialProfileUrl(string $key, ?string $value): ?string
    {
        if (blank($value)) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $key === 'threads_url' ? WebsiteSetting::threadsComUrl($value) : $value;
        }

        $handle = ltrim($value, '@');

        return match ($key) {
            'instagram_url' => "https://www.instagram.com/{$handle}",
            'tiktok_url' => "https://www.tiktok.com/@{$handle}",
            'facebook_url' => "https://www.facebook.com/{$handle}",
            'threads_url' => "https://www.threads.com/@{$handle}",
        };
    }
}
