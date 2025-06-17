<?php

use App\Http\Controllers\SplitterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/splitter', function () {
    return view('index');
});

Route::get('/topologi', function () {
    return view('topologi.index');
});

Route::get('/topologi-map', function () {
    return view('topologi.map');
});

// routes/web.php
Route::post('/splitter/save-topology', [SplitterController::class, 'save']);
