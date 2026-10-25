<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PackageNotificationController extends Controller
{
    public function show(Request $request, string $notification): RedirectResponse
    {
        $databaseNotification = $request->user()->notifications()->findOrFail($notification);
        $packageId = $databaseNotification->data['package_id'] ?? null;

        abort_unless(is_int($packageId), 404);

        $package = Package::query()->findOrFail($packageId);

        $databaseNotification->markAsRead();

        return redirect()->route('admin.packages.edit', $package);
    }
}
