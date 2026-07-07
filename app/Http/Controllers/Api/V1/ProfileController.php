<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\MemberAccess;
use App\Support\MemberProfileViewData;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $memberId = trim((string) data_get($request->user(), 'member_id'));
        $data = MemberProfileViewData::forMemberId($memberId);
        $member = $data['member'];

        return response()->json([
            'profile' => [
                'id' => (string) $member->PrvCusID,
                'full_name' => $data['fullName'],
                'initials' => $data['initials'],
                'category' => (string) ($member->MemberCategory ?? ''),
                'status' => (string) ($member->MemExpTypeName ?? ''),
                'birth_date' => $data['birthDate'],
                'age' => $data['age'],
                'join_date' => $data['joinDate'],
                'expiry_date' => $this->formatDate($member->ExpDt ?? null),
                'blood_group' => (string) ($member->BloodGroup ?? ''),
                'gender' => $this->genderLabel($member->Sex ?? null),
                'religion' => (string) ($member->Religion ?? ''),
                'nationality' => (string) ($member->Nationality ?? ''),
                'phone' => (string) ($member->Phone ?? ''),
                'mobile' => (string) ($member->Mobile ?? ''),
                'email' => (string) ($member->Email ?? ''),
                'address' => (string) ($member->Address ?? ''),
                'city' => (string) ($member->City ?? ''),
                'profession' => (string) ($member->Profession ?? ''),
                'company' => (string) ($member->ComName ?? ''),
                'nid' => (string) ($member->NID ?? ''),
                'passport' => (string) ($member->PassportNo ?? ''),
                'father_name' => (string) ($member->FatherName ?? ''),
                'mother_name' => (string) ($member->MotherName ?? ''),
                'marital_status' => $data['isMarried'] ? 'Married' : (string) ($member->MaritalStatus ?? ''),
                'credit_balance' => (float) ($member->CreditBal ?? 0),
                'has_photo' => $data['hasProfilePhoto'],
                'photo_url' => $data['profilePhotoUrl'],
                'photo_thumb_url' => $data['profilePhotoThumbUrl'],
                'photo_preview_url' => $data['profilePhotoPreviewUrl'],
                'qr_value' => $data['memberQrValue'],
                'children' => $data['children'],
                'children_count' => $data['childrenCount'],
                'has_more_children' => $data['hasMoreChildren'],
                'is_married' => $data['isMarried'],
                'spouse' => [
                    'name' => (string) ($member->SpouseName ?? ''),
                    'blood_group' => (string) ($member->SpoBlood ?? ''),
                    'mobile' => (string) ($member->SpoMobile ?? ''),
                    'wedding_date' => $data['weddingDt'],
                ],
            ],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'max:40', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Enter your current password.',
            'new_password.required' => 'Enter a new password.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.max' => 'The new password may not be greater than 40 characters.',
            'new_password.confirmed' => 'Confirm the new password.',
            'new_password.different' => 'The new password must be different from your current password.',
        ]);

        $memberId = trim((string) data_get($request->user(), 'member_id'));

        abort_if($memberId === '', 404, 'Member not found.');

        if (! MemberAccess::activeMemberExists($memberId) || ! MemberAccess::credentialsMatch($memberId, (string) $validated['current_password'])) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $updated = MemberAccess::changePassword($memberId, (string) $validated['new_password'], 'changed');

        if (! $updated) {
            throw ValidationException::withMessages([
                'new_password' => 'Unable to update the password right now. Please try again.',
            ]);
        }

        return response()->json(['message' => 'Password changed successfully.']);
    }

    private function genderLabel(mixed $value): string
    {
        return match (strtolower(trim((string) $value))) {
            'm' => 'Male',
            'f' => 'Female',
            default => (string) ($value ?? ''),
        };
    }

    private function formatDate(mixed $value): string
    {
        if (! $value) {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable) {
            return '—';
        }
    }
}
