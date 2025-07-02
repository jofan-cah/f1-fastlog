<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Tampilkan form login
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.loginf1');
    }

    // Process login
     // Process login - Updated untuk username only
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $username = $request->input('username');
        $password = $request->input('password');

        // Cari user berdasarkan username saja
        $user = User::where('username', $username)
                   ->with('userLevel')
                   ->first();

        // Validasi user dan password
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Cek apakah user aktif
        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak aktif. Hubungi administrator.'],
            ]);
        }

        // Login user
        Auth::login($user, $request->boolean('remember'));

        // Log aktivitas login
        $this->logActivity($user, 'login', null, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Redirect ke halaman yang diminta atau dashboard
        $redirectTo = $request->session()->pull('url.intended', route('dashboard'));

        return redirect($redirectTo)->with('success', 'Selamat datang, ' . $user->full_name . '!');
    }


    // Process logout
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log aktivitas logout
        if ($user) {
            $this->logActivity($user, 'logout', null, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    // Tampilkan form register (opsional)
    public function showRegisterForm()
    {
        $userLevels = UserLevel::all();
        return view('auth.register', compact('userLevels'));
    }

    // Process register (opsional - untuk demo)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'full_name' => 'required|string|max:100',
            'user_level_id' => 'required|string|exists:user_levels,user_level_id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Generate user ID
        $lastUser = User::orderBy('user_id', 'desc')->first();
        $lastNumber = $lastUser ? (int) substr($lastUser->user_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        $userId = 'USR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        // Create user
        $user = User::create([
            'user_id' => $userId,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'user_level_id' => $request->user_level_id,
            'is_active' => true,
        ]);

        // Log aktivitas register
        $this->logActivity($user, 'register', null, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Auto login after register
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat!');
    }

    // Helper method untuk log aktivitas
    private function logActivity($user, $action, $oldData = null, $newData = null)
    {
        try {
            // Generate log ID
            $lastLog = \App\Models\ActivityLog::orderBy('log_id', 'desc')->first();
            $lastNumber = $lastLog ? (int) substr($lastLog->log_id, 3) : 0;
            $newNumber = $lastNumber + 1;
            $logId = 'LOG' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);

            \App\Models\ActivityLog::create([
                'log_id' => $logId,
                'user_id' => $user->user_id,
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'action' => $action,
                'old_values' => $oldData,
                'new_values' => $newData,
                'ip_address' => $newData['ip_address'] ?? null,
                'user_agent' => $newData['user_agent'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Silent fail untuk log
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
