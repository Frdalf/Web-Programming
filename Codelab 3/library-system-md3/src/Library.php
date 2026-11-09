<?php

namespace App;

class Library
{
    private array $books = [];

    public function addBook(Book $book) : void
    {
        $this -> books[] = $book;
    }

   public function listBooks(): void
{
    foreach ($this->books as $book) {

        $status = $book->isAvailable()
            ? "Available"
            : "Borrowed";

        echo $book->getTitle() . " - " . $status . PHP_EOL;
    }
}
}