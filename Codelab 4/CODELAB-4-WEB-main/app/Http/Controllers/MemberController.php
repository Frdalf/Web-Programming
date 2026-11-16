<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Resources\MemberResource;

class MemberController extends Controller
{
    /**
     * Menampilkan semua member beserta buku yang sedang dipinjam.
     * Method ini sudah LENGKAP - tidak perlu diubah.
     */
    public function index()
    {
        $members = Member::with('books')->paginate(10);
        return MemberResource::collection($members);
    }

    /**
     * Menampilkan satu member beserta buku yang sedang dipinjam.
     * Method ini sudah LENGKAP - tidak perlu diubah.
     * (Menggunakan Route Model Binding)
     */
    public function show(Member $member)
    {
        return new MemberResource($member->load('books'));
    }

    /**
     * TODO 4: Lengkapi method ini agar member dapat meminjam buku.
     *
     * Langkah yang perlu dilakukan:
     * 1. Ambil data Book berdasarkan book_id yang dikirim di request
     * 2. Pastikan Book tersebut is_available = true (masih bisa dipinjam)
     * 3. Update Book: set member_id sesuai $member->id, dan is_available jadi false
     * 4. Kembalikan response JSON konfirmasi peminjaman
     */
    public function borrowBook(Request $request, Member $member)
    {
        // Validasi input sederhana (sudah disediakan)
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        // TODO: cari Book berdasarkan $request->book_id
        $book = Book::findOrFail($request->book_id);


        // TODO: cek apakah buku masih tersedia (is_available)
        if (!$book->is_available) {
            return response()->json(['message' => 'Buku sedang dipinjam'], 409);
        }

        // TODO: update Book -> hubungkan ke member ini dan tandai tidak tersedia
        $book->update([
            'member_id'    => $member->id,
            'is_available' => false,
        ]);

        // TODO: kembalikan response JSON, contoh:
        // return response()->json(['message' => 'Buku berhasil dipinjam']);
        return response()->json(['message' => 'Buku berhasil dipinjam']);
    }

    /**
     * TODO 5: Lengkapi method ini agar member dapat mengembalikan buku.
     *
     * Langkah yang perlu dilakukan:
     * 1. Pastikan Book yang dikembalikan memang milik Member ini
     * 2. Update Book: set member_id jadi null, dan is_available jadi true
     * 3. Kembalikan response JSON konfirmasi pengembalian
     */
    public function returnBook(Member $member, Book $book)
    {
        // TODO: cek apakah $book->member_id sama dengan $member->id
        // 1. pastikan buku ini dipinjam oleh member ini
        if ($book->member_id !== $member->id) {
            return response()->json([
                'message' => 'Buku ini tidak sedang dipinjam oleh member tersebut'
            ], 403);
        }

        // TODO: update Book -> lepaskan relasi dan tandai tersedia kembali
        // 2. lepaskan relasi & tandai tersedia
        $book->update([
            'is_available' => true,
            'member_id'    => null,
        ]);

        // TODO: kembalikan response JSON, contoh:
        // return response()->json(['message' => 'Buku berhasil dikembalikan']);
        // 3. response
        return response()->json(['message' => 'Buku berhasil dikembalikan']);
    }
}
