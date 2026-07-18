<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
                'status_code' => 401,
                'data' => null
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();
            $project = $user->project ?? null;

            $aksesList = $user->role->roleAkses
                ->map(function ($roleAkses) {
                    return $roleAkses->akses->nama_akses;
                });

            $formatedUser = [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role->nama_role,
                'id_project'   => $project ? $project->id : null,
                'akses'        => $aksesList,
                'nama_project' => $project ? $project->nama_project : null,
                'token'        => $token
            ];

            return response()->json([
                'message' => 'Login berhasil',
                'status_code' => 200,
                'data' => $formatedUser
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::guard('api')->user();
            $project = $user->project ?? null;

            $aksesList = $user->role->roleAkses
                ->map(function ($roleAkses) {
                    return $roleAkses->akses->nama_akses;
                });

            return response()->json([
                'message'     => 'User ditemukan',
                'status_code' => 200,
                'data'        => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'role'         => $user->role->nama_role,
                    'akses'        => $aksesList,
                    'id_project'   => $project ? $project->id_project : null,
                    'nama_project' => $project ? $project->nama_project : null,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message'     => $e->getMessage(),
                'status_code' => 500,
                'data'        => null
            ], 500);
        }
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message'     => 'Logout berhasil',
            'status_code' => 200,
            'data'        => null
        ], 200);
    }
}
