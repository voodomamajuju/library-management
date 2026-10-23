<?php

use Illuminate\Support\Facades\Route;

// Admin UI routes (frontend). The backend is a JSON API under /api,
// consumed separately from these pages.

Route::view('/', 'dashboard')->name('dashboard');
Route::view('/login', 'auth.login')->name('login');
Route::view('/member/login', 'auth.member-login')->name('member.login');
Route::view('/member/register', 'auth.member-register')->name('member.register');
Route::view('/member/dashboard', 'member.dashboard')->name('member.dashboard');
Route::view('/books', 'books.index')->name('books.index');
Route::view('/books/create', 'books.create')->name('books.create');
Route::get('/books/{book}/edit', function ($book) {
    return view('books.edit', ['bookId' => $book]);
})->name('books.edit');
Route::view('/members', 'members.index')->name('members.index');
Route::view('/borrows', 'borrows.index')->name('borrows.index');
