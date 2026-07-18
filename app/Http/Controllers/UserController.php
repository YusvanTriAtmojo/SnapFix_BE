<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateByUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'message' => 'Data user berhasil diambil',
            'status_code' => 200,
            'data' => [
                'id'       => $user->id,
                'id_role'  => $user->id_role,
                'role'     => $user->role->nama_role,
                'id_project' => $user->id_project,
                'project'  => $user->project ? $user->project->nama_project : null,
                'nip'      => $user->nip,
                'name'     => $user->name,
                'notlp'    => $user->notlp,
                'alamat'   => $user->alamat,
                'email'    => $user->email,
            ],
        ]);
    }

    public function updateByUserId(UpdateByUserRequest $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validated();

            $validated = array_filter($validated, function ($value) {
                return $value !== null;
            });

            if (!empty($validated['nip'])) {
                $exists = User::where('nip', $validated['nip'])
                    ->where('id_role', $user->id_role)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'message' => 'NIP sudah digunakan oleh pengguna lain dengan role yang sama.',
                        'status_code' => 422,
                        'data' => null,
                    ], 422);
                }
            }

            if (!empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'message' => 'Data user berhasil diperbarui',
                'status_code' => 200,
                'data' => $user->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {

            $query = User::with(['project', 'role']);

            if ($request->filled('id_project')) {
                $query->where('id_project', $request->id_project);
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data user',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'id_project' => $item->id_project,
                    'nama_project' => $item->project?->nama_project,
                    'id_role' => $item->id_role,
                    'nama_role' => $item->role?->nama_role,
                    'name' => $item->name,
                    'nip' => $item->nip,
                    'notlp' => $item->notlp,
                    'alamat' => $item->alamat,
                    'email' => $item->email,
                ];
            });

            return response()->json([
                'message' => 'Data user berhasil diambil',
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

    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();

            $exists = User::where('id_role', $validated['id_role'])
                ->where('nip', $validated['nip'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'NIP sudah digunakan oleh pengguna lain dengan role yang sama.',
                    'status_code' => 422,
                    'data' => null,
                ], 422);
            }

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'message' => 'Data user berhasil ditambahkan',
                'status_code' => 201,
                'data' => [
                    'id'           => $user->id,
                    'id_project'   => $user->id_project,
                    'nama_project' => $user->project?->nama_project,
                    'id_role'      => $user->id_role,
                    'nama_role'    => $user->role?->nama_role,
                    'name'         => $user->name,
                    'nip'          => $user->nip,
                    'notlp'        => $user->notlp,
                    'alamat'       => $user->alamat,
                    'email'        => $user->email,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'status_code' => 404,
                    'data' => null,
                ], 404);
            }

            $exists = User::where('id_role', $request->id_role)
                ->where('nip', $request->nip)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'NIP sudah digunakan oleh pengguna lain dengan role yang sama.',
                    'status_code' => 422,
                    'data' => null,
                ], 422);
            }

            $data = [
                'id_project' => $request->id_project,
                'id_role'    => $request->id_role,
                'name'       => $request->name,
                'nip'        => $request->nip,
                'notlp'      => $request->notlp,
                'alamat'     => $request->alamat,
                'email'      => $request->email,
            ];

            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return response()->json([
                'message' => 'Data user berhasil diperbarui',
                'status_code' => 200,
                'data' => [
                    'id'         => $user->id,
                    'id_project' => $user->id_project,
                    'id_role'    => $user->id_role,
                    'name'       => $user->name,
                    'nip'        => $user->nip,
                    'notlp'      => $user->notlp,
                    'alamat'     => $user->alamat,
                    'email'      => $user->email,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message'     => $e->getMessage(),
                'data'        => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            if ($user->kerusakan()->exists()) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'User tidak bisa di hapus karena telah digunakan di kerusakan',
                ], 400);
            }

            $user->delete();

            return response()->json([
                'message' => 'User berhasil dihapus',
                'status_code' => 200,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message'     => $e->getMessage(),
            ], 500);
        };
    }
}
