<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGelombangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'             => ['required', 'string', 'max:255'],
            'tanggal_mulai'    => ['required', 'date'],
            'tanggal_selesai'  => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active'        => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'                  => 'Nama gelombang wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh kurang dari tanggal mulai.',
        ];
    }
}