<?php 

namespace App;

class Member
{
    private string $name;

    // menyimpan daftar buku yang sedang dipinjam 
    private array $borrowedBooks = [];

    public function __construct(string $name) 
    {
    
        $this -> name = $name;
    }

    public function getName(): string
    {
        return $this -> name;
    }

    // Lengkapi method borrowBook
    // Complete the borrowBook method
    public function borrowBook(Book $book): void
    {
    // periksa apakah buku yang mau di pinjam masih tersedia
    // check if the book to be borrowed is still available
        if ($book->isAvailable()) {

            // ubah status buku menjadi dipinjam ("Borrowed")
            // change the book status to borrowed ("Borrowed")
            $book->borrow();

             $this->borrowedBooks[] = $book;
        }
    }

    public function returnBook(Book $book): void
    {
        // ubah status buku menjadi tersedia kembali ("Available")
        // change the book status to available again ("Available")
        $book->returnBook();

        foreach ($this->borrowedBooks as $index => $borrowedBook){
            if ($borrowedBook === $book) {
                unset($this->borrowedBooks[$index]);
            }
        }
    }
}