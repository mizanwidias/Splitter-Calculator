<?php

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

Route::get('/lab', fn() => view('lab.create'));
Route::post('/lab', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'nama' => 'required|string|max:255',
        'deskripsi' => 'nullable|string',
    ]);

    // Simpan ke file JSON atau DB
    $id = Str::uuid();
    Storage::put("labs/{$id}.json", json_encode([
        'id' => $id,
        'nama' => $data['nama'],
        'deskripsi' => $data['deskripsi'],
        'nodes' => [],
        'connections' => []
    ], JSON_PRETTY_PRINT));

    return redirect("/lab/{$id}/edit");
});

Route::get('/lab/{id}/edit', function ($id) {
    $json = Storage::get("labs/{$id}.json");
    $lab = json_decode($json, true);
    return view('lab.canvas', compact('lab'));
});

Route::post('/lab/{id}/save', function ($id, Request $request) {
    $json = $request->getContent();
    Storage::put("labs/{$id}.json", $json);
    return response()->json(['status' => 'ok']);
});

Route::get('/lab-list', function () {
    $files = Storage::files('labs');
    $labs = collect($files)->map(function ($file) {
        $json = Storage::get($file);
        return json_decode($json, true);
    });
    return view('lab.index', compact('labs'));
});
Route::post('/topologi/save', [TopologyController::class, 'save']);
Route::get('/topologi/load', [TopologyController::class, 'load']);

Route::get('/test-db', function () {
    return \Illuminate\Container\Attributes\DB::select('SELECT DATABASE()');
});
