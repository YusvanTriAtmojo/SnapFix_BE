<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerbaikanRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'kerusakan_id'          => 'required|exists:kerusakan,id',
            'tanggal_perbaikan'     => 'required|date',
            'deskripsi_perbaikan'   => 'required|string',
            'foto_perbaikan'        => 'required|image|mimes:jpeg,jpg,png',
        ];
    }

    public function messages()
    {
        return [
            'kerusakan_id.required' => 'Data kerusakan wajib dipilih.',
            'kerusakan_id.exists' => 'Data kerusakan tidak ditemukan.',

            'tanggal_perbaikan.required' => 'Tanggal perbaikan wajib diisi.',
            'tanggal_perbaikan.date' => 'Format tanggal perbaikan tidak valid.',

            'deskripsi_perbaikan.required' => 'Deskripsi perbaikan wajib diisi.',
            'deskripsi_perbaikan.string' => 'Deskripsi perbaikan harus berupa teks.',

            'foto_perbaikan.required' => 'Foto perbaikan wajib diunggah.',
            'foto_perbaikan.image' => 'File foto perbaikan harus berupa gambar.',
            'foto_perbaikan.mimes' => 'Format foto harus jpeg, jpg, atau png.',
        ];
    }
}