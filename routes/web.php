<?php

use App\Http\Controllers\TweetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TweetController::class, 'index'])->name('tweet.index');
Route::get('/more', [TweetController::class, 'getMoreTweets'])->name('tweet.more');
Route::post('/store', [TweetController::class, 'store'])->name('tweet.store');
