<?php declare(strict_types=1);

Illuminate\Support\Facades\Route::get('/context2', function () {
    return 'Hello World';
})->name('context2');
