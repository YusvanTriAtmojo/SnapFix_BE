<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$aksesRequired): Response
    {
        $user = Auth::guard('api')->user();

        // Pastikan user login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. User not authenticated.',
                'status_code' => 401,
                'data' => null
            ], 401);
        }

        // Ambil semua akses yang dimiliki user melalui role
        $userAkses = $user->role?->akses->pluck('nama_akses')->toArray() ?? [];

        // Cek apakah user memiliki salah satu akses yang diminta
        $hasAccess = !empty(array_intersect($aksesRequired, $userAkses));

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Unauthorized. Required akses(s): ' . implode(', ', $aksesRequired),
                'status_code' => 403,
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}
