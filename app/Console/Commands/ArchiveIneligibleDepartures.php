<?php

namespace App\Console\Commands;

use App\Actions\ArchivePackage;
use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('packages:archive-ineligible-departures')]
#[Description('Tutup departure yang kurang daripada 30 hari dan arkibkan pakej yang tiada departure layak')]
class ArchiveIneligibleDepartures extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ArchivePackage $archivePackage): int
    {
        $cutoffDate = PackageDeparture::promotionCutoffDate();

        $closedDepartures = PackageDeparture::query()
            ->where('status', 'open')
            ->whereDate('departure_date', '<', $cutoffDate)
            ->update(['status' => 'closed']);

        $packagesToArchive = Package::query()
            ->published()
            ->where('is_date_flexible', false)
            ->has('departures')
            ->whereDoesntHave('departures', fn ($query) => $query->visibleForPromotion())
            ->get();

        $archivedPackages = 0;

        foreach ($packagesToArchive as $package) {
            if ($archivePackage->handle($package)) {
                $archivedPackages++;
            }
        }

        $this->components->info("{$closedDepartures} departure ditutup dan {$archivedPackages} pakej diarkibkan.");

        return self::SUCCESS;
    }
}
