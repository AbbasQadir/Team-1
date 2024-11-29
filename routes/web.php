<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/productList', function () {
    return view('products');
});

Route::get('/product', function () {
    return view('specificProduct');
});
