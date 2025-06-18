@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Daftar Lab FTTH</h4>

    <a href="#" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createLabModal">+ Buat Lab Baru</a>

    @if($labs->isEmpty())
        <p class="text-muted">Belum ada lab.</p>
    @else
        <div class="list-group">
            @foreach($labs as $lab)
                <a href="{{ route('lab.canvas', $lab->id) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $lab->nama }}</strong><br>
                            <small>{{ $lab->deskripsi }}</small>
                        </div>
                        <small>{{ $lab->created_at->format('d M Y') }}</small>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Buat Lab -->
<div class="modal fade" id="createLabModal" tabindex="-1" aria-labelledby="createLabModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('lab.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Buat Lab Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nama Lab</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label>Nama Pengguna</label>
                    <input type="text" name="author" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">Buat</button>
            </div>
        </form>
    </div>
</div>
@endsection
