<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: Lengkapi migration ini agar table members
// memiliki struktur yang sesuai untuk menyimpan data anggota perpustakaan.

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // TODO 1: Tambahkan kolom 'name' bertipe string
            // Contoh format: $table->string('nama_kolom');
            $table->string('name');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
