<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Subscription;
use App\Models\CloudCredential;
use App\Models\Bucket;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Aws\S3\S3Client;

class ServiceController extends Controller
{
    /**
     * Helper: Auto-create bucket di MiniStack
     */
    private function ensureBucketExists($bucketName)
    {
        $cleanBucketName = strtolower($bucketName);
        $miniStackUrl = "http://127.0.0.1:4566/" . $cleanBucketName;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $miniStackUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        curl_close($ch);
    }

    public function index()
    {
        $services = Service::all();
        $activeSubscription = Auth::user()->activeSubscription;

        return view('services', compact('services', 'activeSubscription'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $user = Auth::user();
        $newService = Service::find($request->service_id);

        DB::beginTransaction();

        try {
            $oldSubscription = $user->activeSubscription;
            if ($oldSubscription) {
                $oldSubscription->update(['status' => 'Expired']);

                if ($oldSubscription->cloudCredential) {
                    $oldSubscription->cloudCredential->update(['status' => 'Revoked']);
                }
            }

            $newSubscription = Subscription::create([
                'user_id' => $user->id,
                'service_id' => $newService->id,
                'status' => 'Active', 
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);

            $newAccessKey = 'PXC_ACCESS_' . strtoupper(Str::random(16));
            $newSecretKey = 'pxc_secret_' . Str::random(32);

            CloudCredential::create([
                'subscription_id' => $newSubscription->id, 
                'ministack_access_key' => $newAccessKey,
                'ministack_secret_key' => Crypt::encryptString($newSecretKey), 
                'status' => 'Active',
            ]);

            $bucket = Bucket::where('user_id', $user->id)->first();
            
            if ($bucket) {
                $bucket->update([
                    'allocated_size_mb' => $newService->max_storage_mb 
                ]);
            } else {
                // ⭐ Jika belum punya bucket, buat baru
                $bucketName = 'pixie-' . $user->username . '-' . Str::random(4);
                $bucket = Bucket::create([
                    'user_id' => $user->id,
                    'bucket_name' => $bucketName,
                    'allocated_size_mb' => $newService->max_storage_mb,
                ]);
            }

            // ⭐ AUTO-CREATE BUCKET DI MINISTACK
            $this->ensureBucketExists($bucket->bucket_name);

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Melakukan transisi kluster infrastruktur ke paket: {$newService->name}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect('/dashboard')->with('success', 'Kluster infrastruktur berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui kluster: ' . $e->getMessage());
        }
    }
}