<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        abort_if(str_contains($path, '..'), 404);

        $storage = Storage::disk('public');

        abort_unless($storage->exists($path), 404);

        return $storage->serve($request, $path, headers: [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
