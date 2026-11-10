<?php

declare(strict_types=1);

use App\Http\Controllers\GreetingController;
use Illuminate\Support\Facades\Route;

Route::get('/greetings', [GreetingController::class, 'index'])->name('greetings.index');
Route::post('/greetings', [GreetingController::class, 'store'])->name('greetings.store');
