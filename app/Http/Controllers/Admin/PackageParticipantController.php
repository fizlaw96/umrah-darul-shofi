<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreatePackageParticipantSpreadsheet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageParticipantRequest;
use App\Models\Package;
use App\Models\PackageParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PackageParticipantController extends Controller
{
    public function index(Package $package): View
    {
        $package->load([
            'departures' => fn ($query) => $query->orderBy('departure_date'),
            'participants' => fn ($query) => $query->with('departure')->latest(),
        ]);

        return view('admin.packages.show', compact('package'));
    }

    public function store(StorePackageParticipantRequest $request, Package $package): RedirectResponse
    {
        $package->participants()->create($request->validated());

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('success', 'Peserta berjaya ditambah.');
    }

    public function destroy(Package $package, PackageParticipant $participant): RedirectResponse
    {
        abort_unless($participant->package_id === $package->id, 404);

        $participant->delete();

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('success', 'Peserta telah dibuang daripada senarai.');
    }

    public function export(Package $package, CreatePackageParticipantSpreadsheet $spreadsheet): StreamedResponse
    {
        $spreadsheetPath = $spreadsheet->handle($package);

        return response()->streamDownload(function () use ($spreadsheetPath): void {
            try {
                readfile($spreadsheetPath);
            } finally {
                if (file_exists($spreadsheetPath)) {
                    unlink($spreadsheetPath);
                }
            }
        }, Str::slug($package->title).'-senarai-peserta.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
