<?php

use Illuminate\Support\Facades\Route;

// Define a simple route for the home page
Route::get('/', function () {
    return view('welcome');
});