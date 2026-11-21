<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaFileController extends Controller
{
    /**
     * Serve a public upload without starting a session, so browsers and the
     * Varnish layer may cache it and no session row is written per image.
     */
    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        abort_if(str_contains($path, '..'), 404);

        $storage = Storage::disk('public');

        abort_unless($storage->exists($path), 404);

        $response = response()->file($storage->path($path), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->isNotModified($request);

        return $response;
    }
}
