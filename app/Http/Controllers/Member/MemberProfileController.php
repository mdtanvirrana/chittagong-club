<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdatePasswordRequest;
use App\Support\MemberProfileViewData;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberProfileController extends Controller
{
    public function index()
    {
        return view('pages.member-profile', MemberProfileViewData::forCurrentMember());
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $memberId = trim((string) $request->session()->get('member.id'));

        abort_if($memberId === '', 404, 'Member not found.');

        $currentPassword = (string) $request->input('current_password');
        $newPassword = (string) $request->input('new_password');

        $memberExists = DB::table('T_MEMBER')
            ->where('tx_org_id', $memberId)
            ->where('tx_password', $currentPassword)
            ->exists();

        if (! $memberExists) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $updated = DB::table('T_MEMBER')
            ->where('tx_org_id', $memberId)
            ->update([
                'tx_password' => $newPassword,
            ]);

        if ($updated < 1) {
            throw ValidationException::withMessages([
                'new_password' => 'Unable to update the password right now. Please try again.',
            ]);
        }

        $request->session()->regenerateToken();

        return redirect()
            ->route('profile')
            ->with('password_status', 'Password changed successfully.');
    }
}
