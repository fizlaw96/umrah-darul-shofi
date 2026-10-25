<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $administrator = User::query()
            ->staff()
            ->where('email', $validated['email'])
            ->first();

        if ($administrator !== null) {
            $status = Password::sendResetLink(['email' => $administrator->email]);

            if ($status !== Password::RESET_LINK_SENT) {
                return back()->withErrors([
                    'email' => 'Pautan tetapan semula tidak dapat dihantar sekarang. Sila cuba lagi sebentar lagi.',
                ]);
            }
        }

        return back()->with('status', 'Jika alamat e-mel itu mempunyai akses pentadbiran, pautan tetapan semula telah dihantar.');
    }
}
