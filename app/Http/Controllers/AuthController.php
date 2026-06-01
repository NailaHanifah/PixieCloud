<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\CloudCredential;
use App\Models\Bucket;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLandingPage()
    {
        return view('welcome');
    }

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

        try {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Berhasil login ke portal PixieCloud',
                    'ip_address' => $request->ip(),
                ]);

                $redirectUrl = (Auth::user()->role === 'admin') ? '/admin/dashboard' : '/dashboard';

                return redirect($redirectUrl)->with('success', 'Gerbang akses terbuka. Selamat datang kembali!');
            }

            return redirect()->back()->withInput()->with('error', 'Alamat email atau kata sandi Anda tidak cocok dengan catatan kami.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Otentikasi gagal: ' . $e->getMessage());
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user', 
            ]);

            $defaultService = Service::find(1);
            
            if (!$defaultService) {
                throw new \Exception('Kluster spesifikasi layanan bawaan tidak ditemukan.');
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'service_id' => $defaultService->id,
                'status' => 'Active', 
                'start_date' => now(),
                'end_date' => now()->addMonth(), 
            ]);

            $accessKey = 'PXC_ACCESS_' . strtoupper(Str::random(16));
            $secretKey = 'pxc_secret_' . Str::random(32);

            CloudCredential::create([
                'subscription_id' => $subscription->id,
                'ministack_access_key' => $accessKey,
                'ministack_secret_key' => Crypt::encryptString($secretKey), 
                'status' => 'Active', 
            ]);

            $bucketName = 'pixie-' . strtolower($request->username) . '-' . Str::random(4);
            
            Bucket::create([
                'user_id' => $user->id,
                'bucket_name' => $bucketName,
                'allocated_size_mb' => $defaultService->max_storage_mb, 
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'Melakukan registrasi identitas akun baru di portal PixieCloud',
                'ip_address' => $request->ip(),
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Mengaktifkan kluster paket {$defaultService->name} secara instan via Self-Service",
                'ip_address' => $request->ip(),
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Mendirikan ruang penyimpanan terisolasi (Vault): {$bucketName}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            Auth::login($user);

            return redirect('/dashboard')->with('success', 'Akun dan infrastruktur data Anda berhasil didirikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Registrasi gagal: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Meninggalkan portal PixieCloud (Logout)',
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}