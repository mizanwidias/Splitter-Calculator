<?php

namespace App\Http\Controllers;

use App\Models\Topology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TopologyController extends Controller
{
    // public function save(Request $request)
    // {
    //     // Validasi manual agar bisa tampilkan semua error
    //     $validator = Validator::make($request->all(), [
    //         'nodes' => 'required|array',
    //         'connections' => 'required|array',
    //         'power' => 'required|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         // Kirim semua error sebagai array untuk ditangani oleh SweetAlert di frontend
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors()->all(),
    //         ], 422);
    //     }

    //     try {
    //         Topology::create([
    //             'nodes' => json_encode($request->nodes),
    //             'connections' => json_encode($request->connections),
    //             'power' => $request->power,
    //         ]);

    //         return response()->json(['success' => true, 'message' => 'Topologi berhasil disimpan.']);
    //     } catch (\Throwable $e) {
    //         Log::error("Gagal simpan topologi: " . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'errors' => ['Terjadi kesalahan server: ' . $e->getMessage()],
    //         ], 500);
    //     }
    // }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'nodes' => 'required|array',
            'connections' => 'required|array',
            'power' => 'nullable|numeric'
        ]);
        
        $topologi = new Topology();
        $topologi->nodes = json_encode($validated['nodes']);
        $topologi->connections = json_encode($validated['connections']);
        $topologi->power = $validated['power'] ?? 0; // kalau ada kolom power
        $topologi->save();
        
        return response()->json(['success' => true, 'message' => 'Topologi disimpan']);
        \Illuminate\Support\Facades\Log::info('Session ID: ' . session()->getId());
        \Illuminate\Support\Facades\Log::info('Session Token: ' . session()->token());
        \Illuminate\Support\Facades\Log::info('Header Token: ' . $request->header('X-CSRF-TOKEN'));
        
    }


    public function load()
    {
        $topologi = Topology::latest()->first(); // atau sesuaikan logicnya
        if (!$topologi) {
            return response()->json(['nodes' => [], 'connections' => []]);
        }

        return response()->json([
            'nodes' => json_decode($topologi->nodes, true),
            'connections' => json_decode($topologi->connections, true)
        ]);
    }
}
