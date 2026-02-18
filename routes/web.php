<?php

declare(strict_types=1);

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit')->middleware('throttle:5,1');

Route::get('/actueel', [PostController::class, 'index'])->name('posts.index');
Route::get('/actueel/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '.*')->name('pages.show');