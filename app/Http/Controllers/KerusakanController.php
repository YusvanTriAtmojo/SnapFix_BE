<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreKerusakanRequest;
use App\Http\Requests\StorePerbaikanRequest;
use App\Models\UserFcmToken;
use App\Services\FirebaseService;
use App\Models\Kerusakan;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Exception;

class KerusakanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $status = strtolower($request->query('status', 'semua'));
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $user = $request->user();
            $idProject = $user->project ? $user->project->id : null;

            $query = Kerusakan::with(['user.role', 'fasilitas', 'project', 'lokasi']);

            if ($idProject) {
                $query->where('id_project', $idProject);
            }

            if ($status !== 'semua') {
                $query->where('status', $status);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            }

            $query->orderBy('tanggal', 'desc');
            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data kerusakan',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'user_id'       => $item->user_id,
                    'id_project'    => $item->id_project,
                    'id_lokasi'     => $item->id_lokasi,
                    'id_fasilitas'  => $item->id_fasilitas,
                    'tanggal'       => $item->tanggal,
                    'lat_posisi'    => $item->lat_posisi,
                    'lng_posisi'    => $item->lng_posisi,
                    'deskripsi'     => $item->deskripsi,
                    'foto_kerusakan'=> $item->foto_kerusakan ? asset('storage/' . $item->foto_kerusakan) : null,
                    'foto_perbaikan'=> $item->foto_perbaikan ? asset('storage/' . $item->foto_perbaikan) : null,
                    'status'        => $item->status,
                    'user'          => $item->user ? $item->user->name : null,
                    'role'          => $item->user && $item->user->role ? $item->user->role->nama_role : null,
                    'fasilitas'     => $item->fasilitas ? $item->fasilitas->nama_fasilitas : null,
                    'project'       => $item->project ? $item->project->nama_project : null,
                    'lokasi'        => $item->lokasi ? $item->lokasi->nama_lokasi : null,
                ];
            });

            return response()->json([
                'message' => 'Data kerusakan berhasil diambil',
                'status_code' => 200,
                'data' => $formattedData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   public function indexadmin(Request $request)
    {
        try {

            $status = strtolower($request->query('status', 'semua'));
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $idProject = $request->query('id_project');

            // Query untuk menampilkan data
            $query = Kerusakan::with(['user.role', 'fasilitas', 'project', 'lokasi']);

            // Query khusus untuk menghitung total
            $countQuery = Kerusakan::query();

            // Filter tanggal
            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
                $countQuery->whereBetween('tanggal', [$startDate, $endDate]);
            }

            // Filter project
            if ($idProject) {
                $query->where('id_project', $idProject);
                $countQuery->where('id_project', $idProject);
            }

            // Filter status
            if ($status !== 'semua') {
                $query->where('status', $status);
            }

            $query->orderBy('tanggal', 'desc');
            $data = $query->get();

            // Hitung total tanpa dipengaruhi filter status
            $totalKerusakan = $countQuery->count();

            $totalPerbaikan = (clone $countQuery)
                ->where('status', 'diperbaiki')
                ->count();

            $totalSelesai = (clone $countQuery)
                ->where('status', 'selesai')
                ->count();

            $formattedData = $data->map(function ($item) {
                return [
                    'id'             => $item->id,
                    'user_id'        => $item->user_id,
                    'id_project'     => $item->id_project,
                    'id_lokasi'      => $item->id_lokasi,
                    'id_fasilitas'   => $item->id_fasilitas,
                    'tanggal'        => $item->tanggal,
                    'lat_posisi'     => $item->lat_posisi,
                    'lng_posisi'     => $item->lng_posisi,
                    'deskripsi'      => $item->deskripsi,
                    'foto_kerusakan' => $item->foto_kerusakan
                        ? asset('storage/' . $item->foto_kerusakan)
                        : null,
                    'foto_perbaikan' => $item->foto_perbaikan
                        ? asset('storage/' . $item->foto_perbaikan)
                        : null,
                    'status'         => $item->status,
                    'user'           => $item->user?->name,
                    'role'           => $item->user?->role?->nama_role,
                    'fasilitas'      => $item->fasilitas?->nama_fasilitas,
                    'project'        => $item->project?->nama_project,
                    'lokasi'         => $item->lokasi?->nama_lokasi,
                ];
            });

            return response()->json([
                'message' => 'Data kerusakan berhasil diambil',
                'status_code' => 200,
                'total_kerusakan' => $totalKerusakan,
                'total_perbaikan' => $totalPerbaikan,
                'total_selesai' => $totalSelesai,
                'data' => $formattedData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(StoreKerusakanRequest $request)
    {
        try {
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'status_code' => 404,
                    'data' => null,
                ], 404);
            }

            $idProject = $user->project ? $user->project->id : null;

            $fotoPath = null;
            if ($request->hasFile('foto_kerusakan')) {
                $fotoPath = $request->file('foto_kerusakan')
                    ->store('bukti_kerusakan', 'public');
            }

            $kerusakan = Kerusakan::create([
                'user_id'        => $request->user_id,
                'id_project'     => $idProject,
                'id_lokasi'      => $request->id_lokasi,
                'id_fasilitas'   => $request->id_fasilitas,
                'tanggal'        => $request->tanggal ?? now()->toDateString(),
                'lat_posisi'     => $request->lat_posisi,
                'lng_posisi'     => $request->lng_posisi,
                'deskripsi'      => $request->deskripsi,
                'foto_kerusakan' => $fotoPath,
                'status'         => $request->status ?? 'pending',
            ]);

            // Ambil semua token teknisi dalam project yang sama
            $tokens = UserFcmToken::whereHas('user', function ($query) use ($idProject) {
                $query->where('id_project', $idProject)
                    ->whereHas('role', function ($role) {
                        $role->where('nama_role', 'teknisi');
                    });
            })->pluck('token');

            $firebase = new FirebaseService();

            foreach ($tokens as $token) {
                try {
                    $firebase->sendNotification(
                        $token,
                        'Laporan Kerusakan Baru',
                        'Ada laporan kerusakan baru yang perlu diperiksa.'
                    );
                } catch (\Exception $e) {
                }
            }

            return response()->json([
                'message' => 'Kerusakan berhasil ditambahkan',
                'status_code' => 201,
                'data' => [[
                    'id'              => $kerusakan->id,
                    'user_id'         => $kerusakan->user_id,
                    'id_project'      => $kerusakan->id_project,
                    'id_lokasi'       => $kerusakan->id_lokasi,
                    'id_fasilitas'    => $kerusakan->id_fasilitas,
                    'tanggal'         => $kerusakan->tanggal,
                    'lat_posisi'      => $kerusakan->lat_posisi,
                    'lng_posisi'      => $kerusakan->lng_posisi,
                    'deskripsi'       => $kerusakan->deskripsi,
                    'foto_kerusakan'  => $fotoPath
                        ? asset('storage/' . $fotoPath)
                        : null,
                    'status'          => $kerusakan->status,
                ]]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message'     => $e->getMessage(),
                'data'        => null,
            ], 500);
        }
    }

    public function updateStatus(Request $request, $kerusakanId)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:diperbaiki,selesai,pending',
            ]);

            $kerusakan = Kerusakan::find($kerusakanId);
            if (!$kerusakan) {
                return response()->json([
                    'message' => 'Kerusakan tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $kerusakan->status = $request->status;
            $kerusakan->save();

            // Kirim notifikasi ke pelapor jika status diperbaiki atau selesai
            if (in_array($kerusakan->status, ['diperbaiki', 'selesai'])) {

                $token = UserFcmToken::where('user_id', $kerusakan->user_id)
                    ->value('token');

                if ($token) {

                    $firebase = new FirebaseService();

                    $title = 'Status Laporan Berubah';

                    $body = $kerusakan->status == 'diperbaiki'
                        ? 'Laporan kerusakan Anda sedang diperbaiki.'
                        : 'Laporan kerusakan Anda telah selesai diperbaiki.';

                    try {
                        $firebase->sendNotification(
                            $token,
                            $title,
                            $body
                        );
                    } catch (\Exception $e) {
                        // abaikan jika gagal kirim
                    }
                }
            }

            return response()->json([
                'message' => 'Status berhasil diupdate',
                'status_code' => 200,
                'data' => [
                    'id'     => $kerusakan->id,
                    'status' => $kerusakan->status,
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate status: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }


    public function storePerbaikan(StorePerbaikanRequest $request)
    {
        try {
            $kerusakan = Kerusakan::find($request->kerusakan_id);
            if (!$kerusakan) {
                return response()->json([
                    'message' => 'Kerusakan tidak ditemukan',
                    'status_code' => 404,
                    'data' => null,
                ], 404);
            }

            if (
                $kerusakan->foto_perbaikan &&
                Storage::disk('public')->exists($kerusakan->foto_perbaikan)
            ) {
                Storage::disk('public')->delete($kerusakan->foto_perbaikan);
            }

            $fotoPerbaikanPath = $request->file('foto_perbaikan')
                ->store('bukti_perbaikan', 'public');

            $kerusakan->update([
                'tanggal_perbaikan'   => $request->tanggal_perbaikan,
                'deskripsi_perbaikan' => $request->deskripsi_perbaikan,
                'foto_perbaikan'      => $fotoPerbaikanPath,
                'status'              => 'selesai',
            ]);

            $token = UserFcmToken::where('user_id', $kerusakan->user_id)
                ->value('token');

            if ($token) {

                $firebase = new FirebaseService();

                try {

                    $firebase->sendNotification(
                        $token,
                        'Perbaikan Selesai',
                        'Laporan kerusakan Anda telah selesai diperbaiki.'
                    );

                } catch (\Exception $e) {
                }
            }

            return response()->json([
                'message' => 'Laporan perbaikan berhasil ditambahkan',
                'status_code' => 200,
                'data' => [
                    'id' => $kerusakan->id,
                    'tanggal_perbaikan' => $kerusakan->tanggal_perbaikan,
                    'deskripsi_perbaikan' => $kerusakan->deskripsi_perbaikan,
                    'foto_perbaikan' => asset('storage/' . $fotoPerbaikanPath),
                    'status' => $kerusakan->status,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $kerusakan = Kerusakan::find($id);

            if (!$kerusakan) {
                return response()->json([
                    'message' => 'Kerusakan tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            if (strtolower($kerusakan->status) == 'selesai') {
                return response()->json([
                    'message' => 'Kerusakan hanya bisa dihapus jika masih pending',
                    'status_code' => 403,
                ], 403);
            }
            if ($kerusakan->foto_kerusakan && Storage::disk('public')->exists($kerusakan->foto_kerusakan)) {
                Storage::disk('public')->delete($kerusakan->foto_kerusakan);
            }

            if ($kerusakan->foto_perbaikan && Storage::disk('public')->exists($kerusakan->foto_perbaikan)) {
                Storage::disk('public')->delete($kerusakan->foto_perbaikan);
            }
            $kerusakan->delete();

            return response()->json([
                'message' => 'Kerusakan berhasil dihapus',
                'status_code' => 200,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

}
