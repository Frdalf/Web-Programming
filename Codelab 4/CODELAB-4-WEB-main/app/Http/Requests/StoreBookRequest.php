<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Form Request ini sudah LENGKAP - tidak perlu diubah.
// Gunakan sebagai referensi format penulisan Form Request Validation
// yang membedakan aturan untuk POST (required) dan PATCH (sometimes).

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPatch = $this->isMethod('PATCH');

        return [
            'title'  => $isPatch ? 'sometimes|string|max:255' : 'required|string|max:255',
            'author' => $isPatch ? 'sometimes|string|max:255' : 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'  => 'Judul buku wajib diisi',
            'author.required' => 'Nama penulis wajib diisi',
        ];
    }
}
