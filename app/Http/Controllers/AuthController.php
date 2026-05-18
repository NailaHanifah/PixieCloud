<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Bucket;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function showRegisterForm()
    {
        return view('register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Catat aktivitas login
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Berhasil login ke sistem',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => '/dashboard'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah'
        ], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();

        try {
            // Generate MiniStack credentials (simulasi)
            $accessKey = 'AKIA' . Str::random(16);
            $secretKey = Str::random(40);

            // Buat user baru
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'ministack_access_key' => $accessKey,
                'ministack_secret_key' => $secretKey,
            ]);

            // Buat subscription default (Pixie Dust Pouch)
            Subscription::create([
                'user_id' => $user->id,
                'plan_name' => 'Pixie Dust Pouch',
                'max_buckets' => 1,
                'max_storage_mb' => 500,
                'status' => 'Active',
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);

            // Buat bucket terisolasi di MiniStack
            $bucketName = 'pixie-' . $request->username . '-' . Str::random(6);
            Bucket::create([
                'user_id' => $user->id,
                'bucket_name' => $bucketName,
                'allocated_size_mb' => 100,
                'created_at' => now(),
            ]);

            // Catat aktivitas registrasi
            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'Melakukan registrasi akun PixieCloud',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'Sistem menginisialisasi plan Pixie Dust Pouch',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Membuat bucket baru: {$bucketName}",
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();

            // Auto login setelah register
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'redirect' => '/dashboard',
                'access_key' => $accessKey,
                'secret_key' => $secretKey,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Logout dari sistem',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}