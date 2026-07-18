<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_project' => 'required|exists:project,id',
            'id_role'    => 'required|exists:roles,id',
            'name'       => 'required|string|max:30',
            'nip'        => 'required|string|unique:users,nip|max:20',
            'notlp'      => 'required|string|max:13|regex:/^[0-9]{1,13}$/',
            'alamat'     => 'required|string|max:50',
            'email'      => 'required|email|unique:users,email|min:10',
            'password'   => 'required|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'id_project.required' => 'Project wajib dipilih.',
            'id_project.exists' => 'Project tidak ditemukan.',

            'id_role.required' => 'Role wajib dipilih.',
            'id_role.exists' => 'Role tidak ditemukan.',

            'name.required' => 'Nama user wajib diisi.',
            'name.string' => 'Nama user harus berupa teks.',
            'name.max' => 'Nama user maksimal 30 karakter.',

            'nip.required' => 'NIP wajib diisi.',
            'nip.string' => 'NIP harus berupa teks.',
            'nip.unique' => 'NIP sudah digunakan oleh user lain.',
            'nip.max' => 'NIP maksimal 20 karakter.',

            'notlp.required' => 'Nomor telepon wajib diisi.',
            'notlp.string' => 'Nomor telepon harus berupa teks.',
            'notlp.max' => 'Nomor telepon maksimal 13 angka.',
            'notlp.regex' => 'Nomor telepon hanya boleh berisi angka.',

            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'alamat.max' => 'Alamat maksimal 50 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'email.min' => 'Email minimal 10 karakter.',

            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}