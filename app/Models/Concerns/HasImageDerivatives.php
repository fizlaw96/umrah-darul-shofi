<?php

namespace App\Models\Concerns;

use App\Jobs\ProcessImageDerivatives;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps WebP derivatives in step with a model's image attributes.
 *
 * Models list their image columns in imageDerivativeAttributes(). New or replaced
 * images get WebP copies; replaced or deleted images have theirs pruned once the
 * original file has been removed from storage.
 */
trait HasImageDerivatives
{
    public static function bootHasImageDerivatives(): void
    {
        static::created(function (Model $model): void {
            static::dispatchImageDerivatives(array_map(
                fn (string $attribute): mixed => $model->getAttribute($attribute),
                $model->imageDerivativeAttributes(),
            ), []);
        });

        static::updated(function (Model $model): void {
            $pathsToGenerate = [];
            $pathsToPrune = [];
            $previousAttributes = $model->getPrevious();

            foreach ($model->imageDerivativeAttributes() as $attribute) {
                if (! $model->wasChanged($attribute)) {
                    continue;
                }

                $pathsToGenerate[] = $model->getAttribute($attribute);
                $pathsToPrune[] = $previousAttributes[$attribute] ?? null;
            }

            static::dispatchImageDerivatives($pathsToGenerate, $pathsToPrune);
        });

        static::deleted(function (Model $model): void {
            static::dispatchImageDerivatives([], array_map(
                fn (string $attribute): mixed => $model->getAttribute($attribute),
                $model->imageDerivativeAttributes(),
            ));
        });
    }

    /**
     * @return array<int, string>
     */
    abstract public function imageDerivativeAttributes(): array;

    /**
     * @param  array<int, mixed>  $pathsToGenerate
     * @param  array<int, mixed>  $pathsToPrune
     */
    private static function dispatchImageDerivatives(array $pathsToGenerate, array $pathsToPrune): void
    {
        $pathsToGenerate = array_values(array_filter($pathsToGenerate, 'is_string'));
        $pathsToPrune = array_values(array_diff(array_filter($pathsToPrune, 'is_string'), $pathsToGenerate));

        if ($pathsToGenerate === [] && $pathsToPrune === []) {
            return;
        }

        ProcessImageDerivatives::dispatch($pathsToGenerate, $pathsToPrune)->afterResponse();
    }
}
