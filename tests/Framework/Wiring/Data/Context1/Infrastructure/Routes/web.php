<?php declare(strict_types=1);

Illuminate\Support\Facades\Route::get('/context1', function () {
    return 'Hello World';
})->name('context1');
