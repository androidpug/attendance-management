<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function list(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'pending');

        $pending = AttendanceCorrection::where('user_id', $user->id)
            ->where('status', 0)
            ->with(['attendanceRecord'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = AttendanceCorrection::where('user_id', $user->id)
            ->where('status', 1)
            ->with(['attendanceRecord'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('request.list', compact('pending', 'approved', 'tab'));
    }
}