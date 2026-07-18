<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\Project;
use App\Models\Kerusakan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreFasilitasRequest;
use Exception;

class FasilitasController extends Controller
{
    public function index(Request $request)
    {
        try {

            $query = Fasilitas::with(['project']);

            if ($request->filled('id_project')) {
                $query->where('id_project', $request->id_project);
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data fasilitas',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_project' => $item->id_project,
                    'nama_project' => $item->project?->nama_project,
                    'nama_fasilitas' => $item->nama_fasilitas,
                ];
            });

            return response()->json([
                'message' => 'Data fasilitas berhasil diambil',
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

    public function fasilitasDropdown($userId)
    {
        try {
            $user = User::with('role', 'project')->find($userId);

            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'status_code' => 404,
                    'data' => null,
                ], 404);
            }

            $idProject = $user->id_project ?? null;

            if (!$idProject) {
                return response()->json([
                    'message' => 'User tidak memiliki divisi',
                    'status_code' => 404,
                    'data' => [],
                ], 404);
            }

            $fasilitas = Fasilitas::where('id_project', $idProject)
                            ->get(['id', 'nama_fasilitas']);

            if ($fasilitas->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada fasilitas untuk divisi ini',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedFasilitas = $fasilitas->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'nama_fasilitas' => $item->nama_fasilitas,
                ];
            });

            return response()->json([
                'message' => 'Data fasilitas berhasil diambil',
                'status_code' => 200,
                'data' => $formattedFasilitas,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    public function store(StoreFasilitasRequest $request)
    {
        try {
            $exists = Fasilitas::where('id_project', $request->id_project)
                ->where('nama_fasilitas', $request->nama_fasilitas)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Nama fasilitas sudah ada',
                    'status_code' => 409,
                    'data' => null,
                ], 409);
            }

            $fasilitas = Fasilitas::create([
                'id_project' => $request->id_project,
                'nama_fasilitas' => $request->nama_fasilitas,
            ]);

            return response()->json([
                'message' => 'Fasilitas berhasil ditambahkan',
                'status_code' => 201,
                'data' => [
                    'id' => $fasilitas->id,
                    'id_project' => $fasilitas->id_project,
                    'nama_fasilitas' => $fasilitas->nama_fasilitas,
                ],
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $fasilitas = Fasilitas::find($id);

             if (!$fasilitas) {
                return response()->json([
                    'message' => 'Fasilitas tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $request->validate([
                'nama_fasilitas'  => 'required|string|max:50',
            ]);

            $exists = Fasilitas::where('id_project', $request->id_project)
                ->where('nama_fasilitas', $request->nama_fasilitas)
                ->where('id', '!=', $fasilitas->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Nama fasilitas sudah ada',
                    'status_code' => 409,
                    'data' => null,
                ], 409);
            }

            $fasilitas->update([
                'id_project'  => $request->id_project,
                'nama_fasilitas'  => $request->nama_fasilitas,
            ]);

            return response()->json([
                'message' => 'Fasilitas berhasil diubah',
                'status_code' => 200,
                'data' => [
                    'id' => $fasilitas->id,
                    'id_project'  => $request->id_project,
                    'nama_fasilitas' => $fasilitas->nama_fasilitas
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message'     => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $fasilitas = Fasilitas::find($id);

            if (!$fasilitas) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Fasilitas tidak ditemukan',
                ], 404);
            }

            if ($fasilitas->kerusakan()->exists()) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Fasilitas tidak bisa di hapus karena telah digunakan di kerusakan',
                ], 400);
            }

            $fasilitas->delete();

            return response()->json([
                'message' => 'Fasilitas berhasil dihapus',
                'status_code' => 200,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message'     => $e->getMessage(),
            ], 500);
        }
    }
}
