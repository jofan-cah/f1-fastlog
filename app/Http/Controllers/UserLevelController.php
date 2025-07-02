<?php

namespace App\Http\Controllers;

use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserLevelController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Nanti bisa ditambah middleware permission
        // $this->middleware('permission:user_levels.read')->only(['index', 'show']);
        // $this->middleware('permission:user_levels.create')->only(['create', 'store']);
        // $this->middleware('permission:user_levels.update')->only(['edit', 'update']);
        // $this->middleware('permission:user_levels.delete')->only(['destroy']);
    }

    // Tampilkan daftar user levels
    public function index(Request $request)
    {
        $query = UserLevel::withCount('users');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('level_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $userLevels = $query->orderBy('created_at', 'asc')->paginate(10);

        return view('user-levels.index', compact('userLevels'));
    }

    // Tampilkan form create user level
    public function create()
    {
        // Default permissions structure untuk reference
        $defaultPermissions = [
            'dashboard' => ['read'],
            'users' => ['create', 'read', 'update', 'delete'],
            'user_levels' => ['create', 'read', 'update', 'delete'],
            'categories' => ['create', 'read', 'update', 'delete'],
            'suppliers' => ['create', 'read', 'update', 'delete'],
            'items' => ['create', 'read', 'update', 'delete'],
            'purchase_orders' => ['create', 'read', 'update', 'delete', 'approve'],
            'goods_receiveds' => ['create', 'read', 'update', 'delete'],
            'stocks' => ['create', 'read', 'update', 'delete', 'adjust'],
            'transactions' => ['create', 'read', 'update', 'delete', 'approve'],
            'reports' => ['read', 'export'],
            'activity_logs' => ['read'],
            'qr_scanner' => ['read', 'scan'],
            'settings' => ['read', 'update']
        ];

        return view('user-levels.create', compact('defaultPermissions'));
    }

    // Store user level baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level_name' => 'required|string|max:50|unique:user_levels,level_name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate user level ID
            $userLevelId = $this->generateUserLevelId();

            $userLevel = UserLevel::create([
                'user_level_id' => $userLevelId,
                'level_name' => $request->level_name,
                'description' => $request->description,
                'permissions' => $request->permissions ?? [],
            ]);

            // Log activity
            $this->logActivity('user_levels', $userLevel->user_level_id, 'create', null, $userLevel->toArray());

            return redirect()->route('user-levels.index')
                ->with('success', 'User level berhasil ditambahkan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user level: ' . $e->getMessage());
        }
    }

    // Tampilkan detail user level
    public function show(UserLevel $userLevel)
    {
        $userLevel->load('users');
        return view('user-levels.show', compact('userLevel'));
    }

    // Tampilkan form edit user level
    public function edit(UserLevel $userLevel)
    {
        // Default permissions structure untuk reference
        $defaultPermissions = [
            'dashboard' => ['read'],
            'users' => ['create', 'read', 'update', 'delete'],
            'user_levels' => ['create', 'read', 'update', 'delete'],
            'categories' => ['create', 'read', 'update', 'delete'],
            'suppliers' => ['create', 'read', 'update', 'delete'],
            'items' => ['create', 'read', 'update', 'delete'],
            'purchase_orders' => ['create', 'read', 'update', 'delete', 'approve'],
            'goods_receiveds' => ['create', 'read', 'update', 'delete'],
            'stocks' => ['create', 'read', 'update', 'delete', 'adjust'],
            'transactions' => ['create', 'read', 'update', 'delete', 'approve'],
            'reports' => ['read', 'export'],
            'activity_logs' => ['read'],
            'qr_scanner' => ['read', 'scan'],
            'settings' => ['read', 'update']
        ];

        return view('user-levels.edit', compact('userLevel', 'defaultPermissions'));
    }

    // Update user level
    public function update(Request $request, UserLevel $userLevel)
    {
        $validator = Validator::make($request->all(), [
            'level_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user_levels', 'level_name')->ignore($userLevel->user_level_id, 'user_level_id')
            ],
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $oldData = $userLevel->toArray();

            $userLevel->update([
                'level_name' => $request->level_name,
                'description' => $request->description,
                'permissions' => $request->permissions ?? [],
            ]);

            // Log activity
            $this->logActivity('user_levels', $userLevel->user_level_id, 'update', $oldData, $userLevel->fresh()->toArray());

            return redirect()->route('user-levels.index')
                ->with('success', 'User level berhasil diupdate!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate user level: ' . $e->getMessage());
        }
    }

    // Delete user level
    public function destroy(UserLevel $userLevel)
    {
        try {
            // Cek apakah user level memiliki users
            if ($userLevel->users()->exists()) {
                return back()->with('error', 'User level tidak dapat dihapus karena masih digunakan oleh user!');
            }

            $oldData = $userLevel->toArray();
            $userLevelId = $userLevel->user_level_id;
            $userLevel->delete();

            // Log activity
            $this->logActivity('user_levels', $userLevelId, 'delete', $oldData, null);

            return back()->with('success', 'User level berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user level: ' . $e->getMessage());
        }
    }

    // Helper method untuk generate user level ID
    private function generateUserLevelId(): string
    {
        $lastLevel = UserLevel::orderBy('user_level_id', 'desc')->first();
        $lastNumber = $lastLevel ? (int) substr($lastLevel->user_level_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'LVL' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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

