<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::apiResource('books', BookController::class);
Route::apiResource('members', MemberController::class);

Route::post('members/{member}/borrow-book', [MemberController::class, 'borrowBook']);
Route::patch('members/{member}/return-book/{book}', [MemberController::class, 'returnBook']);
