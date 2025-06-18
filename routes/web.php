<?php

use App\Http\Controllers\LabController;
use App\Http\Controllers\SplitterController;
use App\Http\Controllers\TopologyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

// // routes/web.php
// Route::post('/splitter/save-topology', [SplitterController::class, 'save']);

// // routes/web.php
// Route::post('/simpan-topologi', function (\Illuminate\Http\Request $request) {
//     \Illuminate\Support\Facades\Log::info($request->all()); // Debug simpan
//     return response()->json(['status' => 'success']);
// });

Route::get('/lab', [LabController::class, 'index'])->name('lab.index');
Route::post('/lab', [LabController::class, 'store'])->name('lab.store');
Route::get('/lab/{lab}/topologi', [LabController::class, 'topologi'])->name('lab.canvas');
// Route::resource('topologi', [TopologyController::class]);
Route::post('/topologi/save/{$id}', [TopologyController::class, 'save']);
Route::get('/topologi/load/{$id}', [TopologyController::class, 'load']);
