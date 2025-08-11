<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{

    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $user->load('userLevel');

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users', 'username')->ignore($user->user_id, 'user_id'),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'full_name' => 'required|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, underscore, dan hyphen.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $oldData = $user->only(['username', 'email', 'full_name']);

            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
                'full_name' => $request->full_name,
            ];

            // Update password jika diisi
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Log activity
            $this->logActivity('users', $user->user_id, 'profile_update', $oldData, $user->only(['username', 'email', 'full_name']));


            return back()->with('success', 'Profile berhasil diupdate!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate profile: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk log activity
     */
    private function logActivity($tableName, $recordId, $action, $oldData, $newData)
    {
        try {
            $lastLog = \App\Models\ActivityLog::orderBy('log_id', 'desc')->first();
            $lastNumber = $lastLog ? (int) substr($lastLog->log_id, 3) : 0;
            $newNumber = $lastNumber + 1;
            $logId = 'LOG' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);

            \App\Models\ActivityLog::create([
                'log_id' => $logId,
                'user_id' => Auth::id(),
                'table_name' => $tableName,
                'record_id' => $recordId,
                'action' => $action,
                'old_values' => $oldData,
                'new_values' => $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
