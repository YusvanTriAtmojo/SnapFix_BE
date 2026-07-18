<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKerusakanRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'user_id'        => 'required|exists:users,id',
            'id_lokasi'      => 'required|exists:lokasi,id',
            'id_fasilitas'   => 'required|exists:fasilitas,id',
            'tanggal'        => 'nullable|date',
            'lat_posisi'     => 'required|numeric',
            'lng_posisi'     => 'required|numeric',
            'deskripsi'      => 'required|string',
            'foto_kerusakan' => 'required|image|mimes:jpeg,jpg,png',
            'status'         => 'nullable|string|in:diperbaiki,selesai,pending',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User wajib dipilih.',
            'user_id.exists' => 'User tidak ditemukan.',

            'id_lokasi.required' => 'Lokasi wajib dipilih.',
            'id_lokasi.exists' => 'Lokasi tidak ditemukan.',

            'id_fasilitas.required' => 'Fasilitas wajib dipilih.',
            'id_fasilitas.exists' => 'Fasilitas tidak ditemukan.',

            'tanggal.date' => 'Format tanggal tidak valid.',

            'lat_posisi.required' => 'Latitude wajib diisi.',
            'lat_posisi.numeric' => 'Latitude harus berupa angka.',

            'lng_posisi.required' => 'Longitude wajib diisi.',
            'lng_posisi.numeric' => 'Longitude harus berupa angka.',

            'deskripsi.required' => 'Deskripsi kerusakan wajib diisi.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',

            'foto_kerusakan.required' => 'Foto kerusakan wajib diunggah.',
            'foto_kerusakan.image' => 'File harus berupa gambar.',
            'foto_kerusakan.mimes' => 'Format foto harus jpeg, jpg, atau png.',

            'status.string' => 'Status harus berupa teks.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ];
    }
}