<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bucket;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        
        $activeVaults = Bucket::count(); 

        $users = User::orderBy('created_at', 'desc')->get();

        $globalLogs = ActivityLog::orderBy('created_at', 'desc')->take(10)->get();

        return view('admin', compact('totalUsers', 'activeVaults', 'users', 'globalLogs'));
    }
}