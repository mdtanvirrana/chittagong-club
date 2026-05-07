<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.edit', [
            'admin' => Auth::guard('admin')->user(),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        /** @var AdminUser|null $admin */
        $admin = Auth::guard('admin')->user();

        abort_if(! $admin, 404, 'Admin not found.');

        if (! $admin->passwordMatches((string) $request->input('current_password'))) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $timestamp = now();

        $admin->Password = AdminUser::hashPassword((string) $request->input('password'));
        $admin->LastUpdateDate = $timestamp;
        $admin->LastUpdateTime = $timestamp->format('H:i:s');
        $admin->save();

        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Password changed successfully.');
    }
}
