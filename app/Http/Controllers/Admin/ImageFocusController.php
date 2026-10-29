<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateImageFocusRequest;
use App\Models\ImageFocus;
use Illuminate\Http\JsonResponse;

class ImageFocusController extends Controller
{
    public function update(UpdateImageFocusRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $imageFocus = ImageFocus::query()->updateOrCreate(
            ['path' => $validated['path']],
            [
                'position_x' => $validated['position_x'],
                'position_y' => $validated['position_y'],
            ],
        );

        return response()->json([
            'message' => 'Fokus gambar berjaya disimpan.',
            'position' => $imageFocus->cssPosition(),
        ]);
    }
}
