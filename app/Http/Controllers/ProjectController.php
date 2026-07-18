<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Exception;
use App\Models\Kerusakan;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProjectRequest;

class ProjectController extends Controller
{
    public function index()
    {
        try {
            $data = Project::orderBy('nama_project', 'asc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data project',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_project' => $item->nama_project,
                ];
            });

            return response()->json([
                'message' => 'Data project berhasil diambil',
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

    public function store(StoreProjectRequest $request)
    {
        try {
            
            $exists = Project::where('nama_project', $request->nama_project)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Nama Project sudah ada',
                    'status_code' => 409,
                    'data' => null,
                ], 409);
            }

            $project = Project::create([
                'id_project' => $request->id_project,
                'nama_project' => $request->nama_project,
            ]);

           return response()->json([
                'message' => 'Project berhasil ditambahkan',
                'status_code' => 201,
                'data' => [
                    'id' => $project->id,
                    'nama_project' => $project->nama_project,
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
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'message' => 'Project tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $request->validate([
                'nama_project' => 'required|string|max:50',
            ]);

           $exists = Project::where('nama_project', $request->nama_project)
                ->where('id', '!=', $project->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Nama project sudah ada',
                    'status_code' => 409,
                    'data' => null,
                ], 409);
            }

            $project->update([
                'nama_project' => $request->nama_project,
            ]);

            return response()->json([
                'message' => 'Project berhasil diubah',
                'status_code' => 200,
                'data' => [
                    'id' => $project->id,
                    'nama_project' => $project->nama_project
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
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'project tidak ditemukan',
                ], 404);
            }

            if ($project->kerusakan()->exists()) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Project tidak bisa di hapus karena telah digunakan di kerusakan',
                ], 400);
            }

            $project->delete();

            return response()->json([
                'message' => 'project berhasil dihapus',
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