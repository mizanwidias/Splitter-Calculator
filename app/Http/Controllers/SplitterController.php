<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SplitterController extends Controller
{

    public function save(Request $request)
    {
        // Simpan ke database atau file
        \Illuminate\Support\Facades\Storage::put('topologi.json', json_encode([
            'data' => $request->topology,
            'total_loss' => $request->total_loss,
            'saved_at' => now()
        ]));

        return response()->json(['status' => 'success']);
    }
}
