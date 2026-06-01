<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Subscription;
use App\Models\ActivityLog;
use App\Models\Bucket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Service;

class AdminController extends Controller
{
    // Hapus method __construct() ini
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function dashboard()
    {
        // Pastikan user login dan admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $pendingPayments = Payment::with(['user', 'service'])
            ->where('status', 'Pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $successPayments = Payment::with(['user', 'service'])
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $totalUsers = User::where('role', 'user')->count();
        $totalRevenue = Payment::where('status', 'Success')->sum('amount');
        $activeSubscriptions = Subscription::where('status', 'Active')->count();

        return view('admin.dashboard', compact(
            'pendingPayments',
            'successPayments',
            'totalUsers',
            'totalRevenue',
            'activeSubscriptions'
        ));
    }

    public function approvePayment($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $payment = Payment::with(['user', 'service'])->findOrFail($id);
            
            if ($payment->status !== 'Pending') {
                return response()->json(['success' => false, 'message' => 'Payment sudah diproses'], 400);
            }

            $payment->update([
                'status' => 'Success',
                'updated_at' => now(),
            ]);

            Subscription::where('user_id', $payment->user_id)
                ->where('status', 'Active')
                ->update(['status' => 'Expired']);

            $subscription = Subscription::create([
                'user_id' => $payment->user_id,
                'service_id' => $payment->service_id,
                'status' => 'Active',
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);

            ActivityLog::create([
                'user_id' => $payment->user_id,
                'activity' => "Admin mengaktifkan paket {$payment->service->name} (Invoice: {$payment->invoice_code})",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectPayment($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $payment = Payment::findOrFail($id);
            
            if ($payment->status !== 'Pending') {
                return response()->json(['success' => false, 'message' => 'Payment sudah diproses'], 400);
            }

            $payment->update([
                'status' => 'Failed',
                'updated_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $payment->user_id,
                'activity' => "Payment ditolak oleh admin (Invoice: {$payment->invoice_code})",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment ditolak'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject payment: ' . $e->getMessage()
            ], 500);
        }
    }
}