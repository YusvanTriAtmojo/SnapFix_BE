<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Akses;
use App\Models\User;
use App\Models\RoleAkses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class RoleAksesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $filterRole = $request->input('filterRole');

            $query = RoleAkses::with(['role', 'akses'])
                ->orderBy('id_role', 'asc');

            if (!empty($filterRole)) {
                $query->where('id_role', $filterRole);
            }

            $roleAkses = $query->get();

            $data = $roleAkses
                ->groupBy('id_role')
                ->map(function ($items) {
                    $first = $items->first();

                    return [
                        'id_role' => $first->id_role,
                        'nama_role' => optional($first->role)->nama_role,
                        'akses' => $items->map(function ($item) {
                            return [
                                'id_role_akses' => $item->id,
                                'id_akses' => $item->id_akses,
                                'nama_akses' => optional($item->akses)->nama_akses,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            return response()->json([
                'message' => 'Data role akses berhasil diambil',
                'status_code' => 200,
                'data' => $data,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }


    public function update(Request $request, $idRole)
    {
        try {

            $request->validate([
                'id_akses' => 'required|array|min:1',
                'id_akses.*' => 'exists:akses,id',
            ]);

            Role::findOrFail($idRole);

            RoleAkses::where('id_role', $idRole)->delete();

            foreach ($request->id_akses as $idAkses) {
                RoleAkses::create([
                    'id_role' => $idRole,
                    'id_akses' => $idAkses,
                ]);
            }

            return response()->json([
                'message' => 'Role akses berhasil diperbarui',
                'status_code' => 200,
            ]);

        }
        catch (ValidationException $e) {
            throw $e;
        }
        catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
            ],500);

        }
    }
}
