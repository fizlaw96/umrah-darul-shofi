<?php

namespace App\Jobs;

use App\Actions\GenerateImageDerivatives;
use App\Actions\PruneImageDerivatives;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Builds or removes WebP copies after an image attribute changes.
 *
 * Dispatched after the response so admins never wait for image encoding, and
 * so it runs after controllers finish deleting replaced originals.
 */
class ProcessImageDerivatives implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    /**
     * @param  array<int, string>  $pathsToGenerate
     * @param  array<int, string>  $pathsToPrune
     */
    public function __construct(
        public array $pathsToGenerate = [],
        public array $pathsToPrune = [],
    ) {}

    public function handle(GenerateImageDerivatives $generateImageDerivatives, PruneImageDerivatives $pruneImageDerivatives): void
    {
        foreach (array_unique($this->pathsToPrune) as $path) {
            $pruneImageDerivatives->handle($path);
        }

        foreach (array_unique($this->pathsToGenerate) as $path) {
            try {
                $generateImageDerivatives->handle($path);
            } catch (Throwable $throwable) {
                Log::warning('Salinan WebP gagal dijana; imej asal terus digunakan.', [
                    'path' => $path,
                    'exception' => $throwable,
                ]);
            }
        }
    }
}
