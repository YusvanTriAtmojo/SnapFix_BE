<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Akses;
use Illuminate\Http\Request;

class AksesController extends Controller
{
    public function index()
    {
        try {
            $data = Akses::orderBy('nama_akses', 'asc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data akses',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_akses' => $item->nama_akses,
                ];
            });

            return response()->json([
                'message' => 'Data akses berhasil diambil',
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
