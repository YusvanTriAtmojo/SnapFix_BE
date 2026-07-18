<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFasilitasRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_project'     => 'required|exists:project,id',
            'nama_fasilitas' => 'required|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'id_project.required' => 'Project wajib dipilih.',
            'id_project.exists' => 'Project yang dipilih tidak ditemukan.',

            'nama_fasilitas.required' => 'Nama fasilitas wajib diisi.',
            'nama_fasilitas.string' => 'Nama fasilitas harus berupa teks.',
            'nama_fasilitas.max' => 'Nama fasilitas maksimal 50 karakter.',
        ];
    }
}