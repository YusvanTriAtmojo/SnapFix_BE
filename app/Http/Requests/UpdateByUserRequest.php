<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateByUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = auth()->id();

        return [
            'nip'      => 'sometimes|nullable|string|max:20',
            'name'     => 'sometimes|nullable|string|max:30',
            'notlp'    => 'sometimes|nullable|string|max:13',
            'alamat'   => 'sometimes|nullable|string',
            'email'    => 'sometimes|nullable|email|max:255|unique:users,email,' . $id . ',id',
            'password' => 'sometimes|nullable|string|min:6|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'nip.string' => 'NIP harus berupa teks.',
            'nip.max' => 'NIP maksimal 20 karakter.',

            'name.string' => 'Nama user harus berupa teks.',
            'name.max' => 'Nama user maksimal 30 karakter.',

            'notlp.string' => 'Nomor telepon harus berupa teks.',
            'notlp.max' => 'Nomor telepon maksimal 13 angka.',

            'alamat.string' => 'Alamat harus berupa teks.',

            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',

            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}