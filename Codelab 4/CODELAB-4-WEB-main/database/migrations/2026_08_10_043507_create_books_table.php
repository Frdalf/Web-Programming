<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration ini sudah LENGKAP - tidak perlu diubah.
// Gunakan sebagai referensi format penulisan migration.

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->boolean('is_available')->default(true);

            // Kolom foreign key ke table members.
            // nullable() karena buku bisa belum dipinjam siapa pun.
            $table->foreignId('member_id')->nullable()->constrained();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
