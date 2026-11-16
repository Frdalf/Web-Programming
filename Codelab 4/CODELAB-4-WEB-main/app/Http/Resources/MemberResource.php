<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// TODO 7: Lengkapi Resource ini agar menampilkan data Member
// beserta daftar buku yang sedang dipinjam (relasi 'books').
//
// Gunakan BookResource.php sebagai contoh format penulisan.


class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // TODO: tambahkan field 'name' dari data member

            // TODO: tambahkan field 'books' menggunakan whenLoaded('books')
            // Contoh format bisa dilihat pada BookResource.php,
            // bedanya di sini relasinya berupa banyak data (array), bukan satu.
            'id'    => $this->id,
            'name'  => $this->name,
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];
    }
}
