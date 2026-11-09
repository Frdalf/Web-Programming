<?php

namespace App;

class Book
{
    private string $title;
    private string $author;
    private bool $available = true;

    public function __construct(string $title, string $author) {
        $this -> title = $title;
        $this -> author = $author;
    }

    public function getTitle(): string {
        return $this -> title;
    }

    public function isAvailable(): bool {
        return $this -> available;
    }

    public function borrow(): void {
        $this -> available = false;
    }

    public function returnBook(): void {
        $this -> available = true;
    }
}