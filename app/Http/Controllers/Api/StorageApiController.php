<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CloudCredential;
use App\Models\Subscription;
use App\Models\Bucket;
use App\Models\Object as FileObject;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;

class StorageApiController extends Controller
{
    public function externalUpload(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'file' => 'required|file|max:10240',
        ]);

        // 2. Cari credentials melalui subscription
        $credential = CloudCredential::with(['subscription.user'])
            ->where('ministack_access_key', $request->access_key)
            ->where('status', 'Active')
            ->first();

        if (!$credential) {
            return response()->json(['message' => 'Access Key tidak valid!'], 401);
        }

        // Cek apakah subscription masih aktif
        $subscription = $credential->subscription;
        if (!$subscription || $subscription->status !== 'Active') {
            return response()->json(['message' => 'Subscription tidak aktif!'], 401);
        }

        // 3. Verifikasi secret key
        try {
            $decryptedSecret = Crypt::decryptString($credential->ministack_secret_key);
            if ($decryptedSecret !== $request->secret_key) {
                return response()->json(['message' => 'Secret Key salah!'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Secret Key tidak valid!'], 401);
        }

        // 4. Ambil bucket user
        $user = $subscription->user;
        $bucket = Bucket::where('user_id', $user->id)->first();
        
        if (!$bucket) {
            return response()->json(['message' => 'Bucket tidak ditemukan.'], 404);
        }

        // 5. Upload ke MiniStack
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'endpoint' => env('AWS_ENDPOINT', 'http://localhost:4566'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID', 'test'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY', 'test'),
                ]
            ]);

            $result = $s3->putObject([
                'Bucket' => $bucket->bucket_name,
                'Key' => $fileName,
                'Body' => fopen($file->getRealPath(), 'r'),
                'ContentType' => $file->getMimeType(),
            ]);

            // 6. Simpan metadata ke tabel objects
            $object = FileObject::create([
                'bucket_id' => $bucket->id,
                'object_key' => $fileName,
                'content_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
                'file_url' => $result['ObjectURL'],
            ]);

            // 7. Activity log
            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Mengunggah objek [{$fileName}] dari luar platform via API",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload berhasil!',
                'object_name' => $fileName,
                'size_bytes' => $file->getSize(),
                'url' => $result['ObjectURL']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}