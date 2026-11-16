<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// TODO: Lengkapi Model ini agar Member terhubung dengan Book.

class Member extends Model
{
    // TODO 2: Isi fillable dengan kolom yang boleh diisi melalui mass assignment
    // Contoh format: protected $fillable = ['kolom1', 'kolom2'];
    protected $fillable = ['name'];

    /**
     * TODO 3: Definisikan relasi hasMany ke Model Book.
     * Ingat: satu Member dapat meminjam banyak Book.
     *
     * Contoh format:
     * public function namaRelasi()
     * {
     *     return $this->hasMany(NamaModel::class);
     * }
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function books()
    {
        // Lengkapi baris ini
        return $this->hasMany(Book::class);
    }
}
