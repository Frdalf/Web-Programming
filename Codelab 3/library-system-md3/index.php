<?php

require_once 'src/Book.php';
require_once 'src/Member.php';
require_once 'src/Library.php';

use App\Book;
use App\Library;
use App\Member;

$Library = new Library();

$book1 = new Book("Clean Code", "Robert Martin");
$book2 = new Book("Design Patterns", "Gang of Four");

$member = new Member("Alice");

$Library -> addBook($book1);
$Library -> addBook($book2);

// pinjam buku awalnya hanya melalui class buku itu sendiri
// initially, borrowing a book is only done through the book class itself
// $book1 -> borrow();

// sekarang anggota bisa meminjam buku langsung
// now members can borrow books directly
$member -> borrowBook($book1);

echo "Member : " . $member->getName() . PHP_EOL;
echo PHP_EOL;
echo "Library Books" . PHP_EOL;

$Library -> listBooks();
echo PHP_EOL;

// Anggota bisa mengembalikan buku
// Members can return books
$member->returnBook($book1);

echo "After Returning Book" . PHP_EOL;

// Tampilkan kembali status buku
// Display the book status again
$Library->listBooks();


