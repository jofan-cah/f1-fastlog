<?php

// ================================================================
// 1. app/Http/Controllers/UserController.php
// Command: php artisan make:controller UserController
// ================================================================

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     // $this->middleware('auth');
    //     // Nanti bisa ditambah middleware permission
    //     // $this->middleware('permission:users.read')->only(['index', 'show']);
    //     // $this->middleware('permission:users.create')->only(['create', 'store']);
    //     // $this->middleware('permission:users.update')->only(['edit', 'update']);
    //     // $this->middleware('permission:users.delete')->only(['destroy']);
    // }

    //  public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('permission:users,read')->only(['index', 'show']);
    //     $this->middleware('permission:users,create')->only(['create', 'store']);
    //     $this->middleware('permission:users,update')->only(['edit', 'update']);
    //     $this->middleware('permission:users,delete')->only(['destroy']);
    // }

    // Tampilkan daftar users
    public function index(Request $request)
    {
        $query = User::with('userLevel');

        // Filter by level
        if ($request->filled('level')) {
            $query->where('user_level_id', $request->level);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        $userLevels = UserLevel::all();

        return view('users.index', compact('users', 'userLevels'));
    }

    // Tampilkan form create user
    public function create()
    {
        $userLevels = UserLevel::all();
        return view('users.create', compact('userLevels'));
    }

    // Store user baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'full_name' => 'required|string|max:100',
            'user_level_id' => 'required|string|exists:user_levels,user_level_id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            // Generate user ID
            $userId = $this->generateUserId();

            $user = User::create([
                'user_id' => $userId,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'full_name' => $request->full_name,
                'user_level_id' => $request->user_level_id,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Log activity
            $this->logActivity('users', $user->user_id, 'create', null, $user->toArray());

            return redirect()->route('users.index')
                ->with('success', 'User berhasil ditambahkan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    // Tampilkan detail user
    public function show(User $user)
    {
        $user->load('userLevel', 'activityLogs');
        $recentLogs = $user->activityLogs()->latest()->take(10)->get();

        return view('users.show', compact('user', 'recentLogs'));
    }

    // Tampilkan form edit user
    public function edit(User $user)
    {
        $userLevels = UserLevel::all();
        return view('users.edit', compact('user', 'userLevels'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'full_name' => 'required|string|max:100',
            'user_level_id' => 'required|string|exists:user_levels,user_level_id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            $oldData = $user->toArray();

            $updateData = [
                'username' => $request->username,
                'email' => $request->email,
                'full_name' => $request->full_name,
                'user_level_id' => $request->user_level_id,
                'is_active' => $request->boolean('is_active', true),
            ];

            // Update password jika diisi
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Log activity
            $this->logActivity('users', $user->user_id, 'update', $oldData, $user->fresh()->toArray());

            return redirect()->route('users.index')
                ->with('success', 'User berhasil diupdate!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate user: ' . $e->getMessage());
        }
    }

    // Delete user
    public function destroy(User $user)
    {
        try {
            // Cek apakah user yang akan dihapus adalah user yang sedang login
            if ($user->user_id === Auth::id()) {
                return back()->with('error', 'Tidak dapat menghapus user yang sedang login!');
            }

            // Cek apakah user memiliki relasi data
            $hasRelatedData = $user->purchaseOrders()->exists() ||
                             $user->goodsReceived()->exists() ||
                             $user->createdTransactions()->exists();

            if ($hasRelatedData) {
                // Soft delete - set is_active = false
                $oldData = $user->toArray();
                $user->update(['is_active' => false]);

                $this->logActivity('users', $user->user_id, 'deactivate', $oldData, $user->fresh()->toArray());

                return back()->with('warning', 'User tidak dapat dihapus karena memiliki data terkait. User telah dinonaktifkan.');
            } else {
                // Hard delete
                $oldData = $user->toArray();
                $userId = $user->user_id;
                $user->delete();

                $this->logActivity('users', $userId, 'delete', $oldData, null);

                return back()->with('success', 'User berhasil dihapus!');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    // Toggle user status
    public function toggleStatus(User $user)
    {
        try {
            $oldData = $user->toArray();
            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $action = $user->is_active ? 'activate' : 'deactivate';

            $this->logActivity('users', $user->user_id, $action, $oldData, $user->fresh()->toArray());

            return back()->with('success', "User berhasil {$status}!");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    // Helper method untuk generate user ID
    private function generateUserId(): string
    {
        $lastUser = User::orderBy('user_id', 'desc')->first();
        $lastNumber = $lastUser ? (int) substr($lastUser->user_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'USR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Helper method untuk log activity
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

// ================================================================
// 2. app/Http/Controllers/UserLevelController.php
// Command: php artisan make:controller UserLevelController
// ================================================================

