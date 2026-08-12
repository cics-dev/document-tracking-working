<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserAccountService
{
    public function rules(?int $userId = null, bool $officeRequired = true, string $prefix = ''): array
    {
        $key = fn (string $field) => $prefix.$field;

        return [
            $key('signature') => ['nullable', 'image', 'max:2048'],
            $key('family_name') => ['required', 'string', 'max:255'],
            $key('given_name') => ['required', 'string', 'max:255'],
            $key('middle_name') => ['nullable', 'string', 'max:255'],
            $key('middle_initial') => ['nullable', 'required_with:'.$key('middle_name'), 'string', 'max:10'],
            $key('honorifics') => ['nullable', 'string', 'max:10'],
            $key('suffix') => ['nullable', 'string', 'max:10'],
            $key('titles') => ['nullable', 'string', 'max:100'],
            $key('gender') => ['required', 'string', 'max:20'],
            $key('email') => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            $key('office_id') => [$officeRequired ? 'required' : 'nullable', 'exists:offices,id'],
            $key('role_id') => ['required', 'exists:roles,id'],
            $key('position') => ['required', 'string', 'max:100'],
            $key('is_head') => ['boolean'],
        ];
    }

    public function values(User $user): array
    {
        $user->loadMissing('profile', 'office');

        return [
            'family_name' => $user->profile?->family_name ?? '',
            'given_name' => $user->profile?->given_name ?? '',
            'middle_name' => $user->profile?->middle_name ?? '',
            'middle_initial' => $user->profile?->middle_initial ?? '',
            'suffix' => $user->profile?->suffix ?? '',
            'honorifics' => $user->profile?->honorifics ?? '',
            'titles' => $user->profile?->titles ?? '',
            'gender' => $user->profile?->gender ?? '',
            'email' => $user->email,
            'office_id' => (string) $user->office_id,
            'position' => $user->position,
            'role_id' => (string) $user->role_id,
            'is_head' => $user->office?->head_id === $user->id,
            'signature' => null,
        ];
    }

    public function save(array $data, ?User $user = null, ?int $forcedOfficeId = null, ?string $existingSignature = null): User
    {
        return DB::transaction(function () use ($data, $user, $forcedOfficeId, $existingSignature): User {
            $signature = $data['signature'] ?? null;
            $signaturePath = $signature instanceof UploadedFile
                ? $signature->store('assets/img', 'public')
                : $existingSignature;
            $name = trim($data['given_name'].' '.(($data['middle_initial'] ?? '') !== '' ? $data['middle_initial'].'. ' : '').$data['family_name'].(($data['suffix'] ?? '') !== '' ? ' '.$data['suffix'] : ''));

            $userData = [
                'name' => $name,
                'email' => $data['email'],
                'office_id' => $forcedOfficeId ?? $data['office_id'],
                'role_id' => $data['role_id'],
                'position' => $data['position'],
                'signature' => $signaturePath,
            ];
            if (! $user) {
                $userData['password'] = 'password';
                $user = User::create($userData);
            } else {
                $user->update($userData);
            }

            $user->profile()->updateOrCreate([], [
                'given_name' => $data['given_name'],
                'middle_name' => $data['middle_name'] ?? '',
                'middle_initial' => $data['middle_initial'] ?? '',
                'family_name' => $data['family_name'],
                'suffix' => $data['suffix'] ?? '',
                'honorifics' => rtrim($data['honorifics'] ?? '', '.'),
                'titles' => $data['titles'] ?? '',
                'gender' => $data['gender'],
            ]);

            if (($data['is_head'] ?? false) && $user->office) {
                $user->office->update(['head_id' => $user->id]);
            }

            return $user;
        });
    }
}
