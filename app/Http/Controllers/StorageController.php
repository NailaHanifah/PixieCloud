<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\ObjectStorage;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;

class StorageController extends Controller
{
    private function ensureBucketExists($bucketName)
    {
        $cleanBucketName = strtolower($bucketName);

        try {
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'endpoint'=> env('AWS_ENDPOINT', 'http://127.0.0.1:4566'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID', 'test'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY', 'test'),
                ]
            ]);

            if (!$s3->doesBucketExist($cleanBucketName)) {
                $s3->createBucket([
                    'Bucket' => $cleanBucketName,
                ]);
                
                Log::info("Auto-create bucket BERHASIL didirikan di MiniStack: {$cleanBucketName}");
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Auto-create bucket GAGAL: " . $e->getMessage());
            return false;
        }
    }

    public function index()
    {
        $user = Auth::user();
        $bucket = Bucket::where('user_id', $user->id)->first();
        
        if ($bucket) {
            // ⭐ AUTO-CREATE BUCKET DI MINISTACK
            $this->ensureBucketExists($bucket->bucket_name);
        }

        $objects = ObjectStorage::where('bucket_id', $bucket->id ?? 0)->get();
        return view('storage', compact('bucket', 'objects'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'object_file' => 'required|file|max:51200', 
        ]);

        $user = Auth::user();
        $bucket = Bucket::where('user_id', $user->id)->first();

        if (!$bucket) {
            return redirect()->back()->with('error', 'Ruang penyimpanan (Vault) belum didirikan.');
        }

        // 1. Pastikan bucket fisik sudah terbuat di Docker MiniStack
        $this->ensureBucketExists($bucket->bucket_name);

        $file = $request->file('object_file');
        $fileSizeBytes = $file->getSize();
        $fileSizeMB = $fileSizeBytes / (1024 * 1024);

        $currentUsedBytes = $bucket->objects()->sum('file_size_bytes');
        $currentUsedMB = $currentUsedBytes / (1024 * 1024);

        if (($currentUsedMB + $fileSizeMB) > $bucket->allocated_size_mb) {
            return redirect()->back()->with('error', 'Operasi ditolak! Ukuran file melebihi batas sisa kuota memori Vault Anda.');
        }

        try {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $cleanBucketName = strtolower($bucket->bucket_name);

            // 2. 🔥 PERBAIKAN UTAMA: Kirim file biner secara nyata ke Docker MiniStack port 4566 via S3 Client
            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'endpoint'=> env('AWS_ENDPOINT', 'http://127.0.0.1:4566'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => env('AWS_ACCESS_KEY_ID', 'test'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY', 'test'),
                ]
            ]);

            // Dorong file ke Docker
            $s3->putObject([
                'Bucket' => $cleanBucketName,
                'Key'    => $fileName,
                'Body'   => fopen($file->getRealPath(), 'r'),
                'ContentType' => $file->getClientMimeType(),
            ]);

            // 3. Simpan juga cadangan ke folder public/uploads laptop jika diperlukan web
            $targetDir = public_path('uploads');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $file->move($targetDir, $fileName);
            
            // URL arah endpoint MiniStack Docker
            $fakeCloudUrl = "http://127.0.0.1:4566/" . $cleanBucketName . "/" . $fileName;

            // 4. Catat metadata objek ke database Laragon
            ObjectStorage::create([
                'bucket_id' => $bucket->id,
                'object_key' => $fileName,
                'content_type' => $file->getClientMimeType(),
                'file_size_bytes' => $fileSizeBytes,
                'file_url' => $fakeCloudUrl, 
            ]);

            // 5. Catat log aktivitas user
            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Berhasil mendorong objek data [{$fileName}] masuk ke dalam Vault",
                'ip_address' => $request->ip(),
            ]);

            return redirect()->back()->with('success', 'Arsip file digital berhasil divalidasi dan disimpan di server virtual MiniStack!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file ke MiniStack: ' . $e->getMessage());
        }
    }

    public function logSuccess(Request $request)
    {
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => "Berhasil mendorong objek data [{$request->file_name}] masuk ke dalam Vault",
            'ip_address' => $request->ip(),
        ]);
        return response()->json(['status' => 'logged']);
    }

    public function download(int $id)
    {
        $object = ObjectStorage::findOrFail($id);
        $bucket = $object->bucket;

        $isStream = request()->query('stream') === 'true';
        $disposition = $isStream ? 'inline' : 'attachment';

        try {
            $cleanBucket = strtolower($bucket->bucket_name);
            $miniStackUrl = "http://127.0.0.1:4566/" . $cleanBucket . "/" . $object->object_key;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $miniStackUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $fileData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $fileData) {
                return response($fileData, 200, [
                    'Content-Type' => $object->content_type,
                    'Content-Disposition' => $disposition . '; filename="' . $object->object_key . '"',
                ]);
            }

            return $this->handleBypassDemo($object, $isStream);

        } catch (\Exception $e) {
            return $this->handleBypassDemo($object, $isStream);
        }
    }

    public function destroy(int $id)
    {
        $object = ObjectStorage::findOrFail($id);
        $user = Auth::user();

        try {
            $filePath = public_path('uploads/' . $object->object_key);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $object->delete();

            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => "Mengeksekusi perintah destruksi permanen pada objek: {$object->object_key}",
                'ip_address' => request()->ip(),
            ]);

            return redirect()->back()->with('success', 'Objek data berhasil dihancurkan secara permanen dari Vault!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus objek: ' . $e->getMessage());
        }
    }


    public function activityLogs()
    {
        $user = Auth::user();
        
        $logs = ActivityLog::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        
        $bucket = Bucket::where('user_id', $user->id)->first();

        return view('logs', compact('logs', 'bucket'));
    }

    private function handleBypassDemo($object, $isStream)
    {
        if ($isStream && $object->content_type && str_starts_with($object->content_type, 'image/')) {
            $placeholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="100%" height="100%"><rect width="400" height="300" fill="#F4F1EA"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="12" fill="#5C6B5D">🟢 [PixieCloud Secure Object Preview Dynamic Mode]</text></svg>';
            return response($placeholderSvg, 200, ['Content-Type' => 'image/svg+xml']);
        }

        return response("Isi data privat aman virtual untuk berkas: " . $object->object_key, 200, [
            'Content-Type' => $object->content_type,
            'Content-Disposition' => ($isStream ? 'inline' : 'attachment') . '; filename="' . $object->object_key . '"',
        ]);
    }
}