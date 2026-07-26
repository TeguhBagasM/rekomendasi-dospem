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
            'nama' => ['required', 'string', 'max:100'],
            'periode_akademik' => ['required', 'digits:5', 'unique:gelombangs,periode_akademik', 'regex:/^\d{4}[12]$/'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama gelombang wajib diisi.',
            'periode_akademik.required' => 'Periode akademik wajib diisi.',
            'periode_akademik.digits' => 'Periode akademik harus terdiri dari 5 digit angka.',
            'periode_akademik.unique' => 'Periode akademik sudah digunakan oleh gelombang lain.',
            'periode_akademik.regex' => 'Format periode akademik tidak valid. Gunakan format YYYY1 (Ganjil) atau YYYY2 (Genap), contoh: 20251, 20262.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh kurang dari tanggal mulai.',
        ];
    }
}
