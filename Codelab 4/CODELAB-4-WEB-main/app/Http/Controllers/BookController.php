<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;

// Controller ini sudah LENGKAP - tidak perlu diubah.
// Gunakan sebagai referensi format penulisan Controller CRUD standar.

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('member')->paginate(10);
        return BookResource::collection($books);
    }

    // Route Model Binding: Laravel otomatis mencari Book berdasarkan ID di URL
    public function show(Book $book)
    {
        return new BookResource($book->load('member'));
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());
        return (new BookResource($book))->response()->setStatusCode(201);
    }

    public function update(StoreBookRequest $request, Book $book)
    {
        $book->update($request->validated());
        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
