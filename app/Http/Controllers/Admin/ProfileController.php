<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $profileDetails = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $profileDetails['password'] = $request->string('password')->toString();
        }

        $request->user()->update($profileDetails);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Profil anda berjaya dikemas kini.');
    }
}
