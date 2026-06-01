<?php

namespace App\Http\Controllers;

use App\Models\CloudCredential;
use App\Models\Subscription;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class CredentialController extends Controller
{
    public function showCredentials(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required|string',
        ]);
        
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi yang Anda masukkan salah.'
            ], 401);
        }
        
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'Active')
            ->first();

        if (!$activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'Kluster paket sewa aktif tidak ditemukan.'
            ], 404);
        }

        $credential = CloudCredential::where('subscription_id', $activeSubscription->id)
            ->where('status', 'Active')
            ->first();

        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Rune Credentials aktif tidak ditemukan atau telah dicabut (Revoked).'
            ], 404);
        }
        
        $secretKey = Crypt::decryptString($credential->ministack_secret_key);
        
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Melakukan otentikasi kunci untuk melihat Rune Credentials privat',
            'ip_address' => $request->ip(),
        ]);
        
        return response()->json([
            'success' => true,
            'access_key' => $credential->ministack_access_key,
            'secret_key' => $secretKey,
        ]);
    }
    
    public function revealCredentialsPage()
    {
        $user = Auth::user();

        // 1. Cari kontrak sewa aktif
        $activeSubscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'Active')
            ->first();

        // 2. Ambil data kuncinya (Hanya Access Key yang dikirim mentah, Secret Key tetap disembunyikan sampai isi password)
        $credential = null;
        if ($activeSubscription) {
            $credential = \App\Models\CloudCredential::where('subscription_id', $activeSubscription->id)
                ->where('status', 'Active')
                ->first();
        }

        // 3. Ambil data Vault (Bucket)
        $bucket = \App\Models\Bucket::where('user_id', $user->id)->first();

        // 4. Hitung ringkasan alokasi file secara riil
        $totalFiles = 0;
        $usedStorageMB = 0;
        $usagePercentage = 0;

        if ($bucket) {
            $totalFiles = \App\Models\ObjectStorage::where('bucket_id', $bucket->id)->count();
            $totalBytes = \App\Models\ObjectStorage::where('bucket_id', $bucket->id)->sum('file_size_bytes');
            $usedStorageMB = round($totalBytes / (1024 * 1024), 2);

            if ($bucket->allocated_size_mb > 0) {
                $usagePercentage = ($usedStorageMB / $bucket->allocated_size_mb) * 100;
                $usagePercentage = min(100, $usagePercentage);
            }
        }

        return view('dashboard', compact(
            'activeSubscription',
            'credential',
            'bucket',
            'totalFiles',
            'usedStorageMB',
            'usagePercentage'
        ));
    }
}