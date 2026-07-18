<?php

namespace App\Http\Controllers;

use App\Models\UserFcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FCMController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        UserFcmToken::updateOrCreate(
            [
                'user_id' => Auth::id(),
            ],
            [
                'token' => $request->token,
            ]
        );

        return response()->json([
            'message' => 'FCM Token berhasil disimpan',
        ]);
    }
}