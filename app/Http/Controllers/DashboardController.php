<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\CloudCredential;
use App\Models\Bucket;
use App\Models\ObjectStorage;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'Active')
            ->first();

        $service = $activeSubscription ? $activeSubscription->service : null;

        $credential = null;
        if ($activeSubscription) {
            $credential = CloudCredential::where('subscription_id', $activeSubscription->id)
                ->where('status', 'Active')
                ->first();
        }

        $bucket = Bucket::where('user_id', $user->id)->first();
        $buckets = Bucket::where('user_id', $user->id)->get(); 

        $activityLogs = $user->activityLogs()->orderBy('created_at', 'desc')->limit(10)->get();

        $totalFiles = 0;
        $usedStorageMB = 0;
        $usagePercentage = 0;

        if ($bucket) {
            $totalFiles = ObjectStorage::where('bucket_id', $bucket->id)->count();

            $totalBytes = ObjectStorage::where('bucket_id', $bucket->id)->sum('file_size_bytes');
            $usedStorageMB = round($totalBytes / (1024 * 1024), 2);

            if ($bucket->allocated_size_mb > 0) {
                $usagePercentage = ($usedStorageMB / $bucket->allocated_size_mb) * 100;
                $usagePercentage = min(100, $usagePercentage); 
            }
        }

        $totalStorageUsed = $usedStorageMB;
        $maxStorage = $bucket ? $bucket->allocated_size_mb : ($service ? $service->max_storage_mb : 0);
        $remainingStorage = $maxStorage - $totalStorageUsed;

        $availableServices = Service::all();

        return view('dashboard', compact(
            'user',
            'activeSubscription',
            'service',
            'credential',
            'bucket',
            'buckets',
            'activityLogs',
            'totalFiles',
            'usedStorageMB',
            'totalStorageUsed',
            'maxStorage',
            'remainingStorage',
            'usagePercentage',
            'availableServices'
        ));
    }
}