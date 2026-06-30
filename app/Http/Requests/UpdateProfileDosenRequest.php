<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileDosenRequest extends FormRequest
{
    /**
     * Otorisasi: hanya user dengan role dosen DAN sudah punya relasi dosen
     * yang boleh mengakses. Pengecekan kepemilikan data dilakukan di
     * controller via Auth::user()->dosen, bukan berdasarkan parameter id,
     * sehingga di sini cukup pastikan user adalah dosen yang valid.
     */
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->hasRole('dosen')
            && $this->user()->dosen !== null;
    }

    public function rules(): array
    {
        return [
            // TAB 1 - hanya no_hp yang editable
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],

            // TAB 2 - bidang keahlian
            'bidang_keahlian_ids'   => ['nullable', 'array'],
            'bidang_keahlian_ids.*' => ['exists:bidang_keahlians,id'],

            // TAB 3 - profil penelitian
            'topik_metode'     => ['nullable', 'string', 'max:5000'],
            'judul_penelitian' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Format nomor HP tidak valid.',
            'bidang_keahlian_ids.*.exists' => 'Salah satu bidang keahlian yang dipilih tidak valid.',
        ];
    }
}