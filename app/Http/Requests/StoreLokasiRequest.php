<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLokasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_project'  => 'required|exists:project,id',
            'nama_lokasi' => 'required|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'id_project.required' => 'Project wajib dipilih.',
            'id_project.exists' => 'Project tidak ditemukan.',

            'nama_lokasi.required' => 'Nama lokasi wajib diisi.',
            'nama_lokasi.string' => 'Nama lokasi harus berupa teks.',
            'nama_lokasi.max' => 'Nama lokasi maksimal 50 karakter.',
        ];
    }
}