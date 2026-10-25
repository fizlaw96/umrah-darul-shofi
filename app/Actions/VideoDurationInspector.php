<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class VideoDurationInspector
{
    public function durationInSeconds(UploadedFile $video): ?float
    {
        $path = $video->getRealPath();

        if ($path === false) {
            return null;
        }

        $ffprobe = config('darul_shofi.ffprobe_binary') ?: (new ExecutableFinder)->find('ffprobe', null, [
            '/opt/homebrew/bin',
            '/usr/local/bin',
            '/usr/bin',
        ]);

        if ($ffprobe === null) {
            return null;
        }

        $process = new Process([
            $ffprobe,
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $path,
        ]);

        try {
            $process->run();
        } catch (\Throwable) {
            return null;
        }

        if (! $process->isSuccessful()) {
            return null;
        }

        $duration = filter_var(trim($process->getOutput()), FILTER_VALIDATE_FLOAT);

        return $duration === false || $duration < 0 ? null : (float) $duration;
    }
}
