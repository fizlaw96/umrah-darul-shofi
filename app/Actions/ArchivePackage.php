<?php

namespace App\Actions;

use App\Models\Package;
use App\Models\User;
use App\Notifications\PackageArchived;
use Illuminate\Support\Facades\Notification;

class ArchivePackage
{
    public function handle(Package $package): bool
    {
        if ($package->status === 'archived') {
            return false;
        }

        $package->update([
            'status' => 'archived',
            'published_at' => null,
        ]);

        $this->notify($package);

        return true;
    }

    public function notify(Package $package): void
    {
        Notification::send(
            User::query()->staff()->get(),
            new PackageArchived($package),
        );
    }
}
