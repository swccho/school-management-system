<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    return view('admin');
})->name('admin');

Route::get('/admin/{any}', function () {
    return view('admin');
})->where('any', '.*')->name('admin.catchall');

Route::get('/teacher', function () {
    return view('teacher');
})->name('teacher');

Route::get('/teacher/{any}', function () {
    return view('teacher');
})->where('any', '.*')->name('teacher.catchall');

Route::get('/student', function () {
    return view('student');
})->name('student');

Route::get('/student/{any}', function () {
    return view('student');
})->where('any', '.*')->name('student.catchall');
