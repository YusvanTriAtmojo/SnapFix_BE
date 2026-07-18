<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreLokasiRequest;
use App\Models\Lokasi;
use App\Models\Project;
use App\Models\Kerusakan;
use App\Models\User;
use Exception;

class LokasiController extends Controller
{
    public function lokasiDropdown($userId)
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

            $lokasi = Lokasi::where('id_project', $idProject)
                            ->get(['id', 'nama_lokasi']);

            if ($lokasi->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada lokasi untuk divisi ini',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedLokasi = $lokasi->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'nama_lokasi' => $item->nama_lokasi,
                ];
            });

            return response()->json([
                'message' => 'Data lokasi berhasil diambil',
                'status_code' => 200,
                'data' => $formattedLokasi,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {

            $query = Lokasi::with(['project']);

            if ($request->filled('id_project')) {
                $query->where('id_project', $request->id_project);
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data Lokasi',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_project' => $item->id_project,
                    'nama_project' => $item->project?->nama_project,
                    'nama_lokasi' => $item->nama_lokasi,
                ];
            });

            return response()->json([
                'message' => 'Data Lokasi berhasil diambil',
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

    public function store(StoreLokasiRequest $request)
    {
        try {
            $exists = Lokasi::where('id_project', $request->id_project)
                ->where('nama_lokasi', $request->nama_lokasi)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Nama lokasi sudah ada',
                    'status_code' => 409,
                    'data' => null,
                ], 409);
            }

            $lokasi = Lokasi::create([
                'id_project' => $request->id_project,
                'nama_lokasi' => $request->nama_lokasi,
            ]);

            return response()->json([
                'message' => 'Lokasi berhasil ditambahkan',
                'status_code' => 201,
                'data' => [
                    'id' => $lokasi->id,
                    'id_project' => $lokasi->id_project,
                    'nama_lokasi' => $lokasi->nama_lokasi,
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
            $lokasi = Lokasi::find($id);

             if (!$lokasi) {
                return response()->json([
                    'message' => 'Lokasi tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $request->validate([
                'nama_lokasi'  => 'required|string|max:50',
            ]);

            $exists = Lokasi::where('id_project', $request->id_project)
                ->where('nama_lokasi', $request->nama_lokasi)
                ->where('id', '!=', $lokasi->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Nama lokasi sudah ada',
                    'status_code' => 409,
                    'data' => null,
                ], 409);
            }

            $lokasi->update([
                'id_project'  => $request->id_project,
                'nama_lokasi'  => $request->nama_lokasi,
            ]);

            return response()->json([
                'message' => 'Lokasi berhasil diubah',
                'status_code' => 200,
                'data' => [
                    'id' => $lokasi->id,
                    'id_project'  => $request->id_project,
                    'nama_lokasi' => $lokasi->nama_lokasi
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
            $lokasi = Lokasi::find($id);

            if (!$lokasi) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Lokasi tidak ditemukan',
                ], 404);
            }

            if ($lokasi->kerusakan()->exists()) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Lokasi tidak bisa di hapus karena telah digunakan di kerusakan',
                ], 400);
            }

            $lokasi->delete();

            return response()->json([
                'message' => 'Lokasi berhasil dihapus',
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
