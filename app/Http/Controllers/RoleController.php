<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Exception;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $data = Role::orderBy('nama_role', 'asc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data role',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_role' => $item->nama_role,
                ];
            });

            return response()->json([
                'message' => 'Data role berhasil diambil',
                'status_code' => 200,
                'data' => $formattedData,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}