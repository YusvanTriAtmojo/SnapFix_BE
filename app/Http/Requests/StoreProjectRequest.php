<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_project' => 'required|string|max:50',
        ];
    }

    public function messages()
    {
        return [

            'nama_project.required' => 'Nama project wajib diisi.',
            'nama_project.string' => 'Nama project harus berupa teks.',
            'nama_project.max' => 'Nama project maksimal 50 karakter.',
        ];
    }
}