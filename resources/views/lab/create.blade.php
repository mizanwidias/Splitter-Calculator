@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Buat Lab Baru</h4>
    <form method="POST" action="/lab">
        @csrf
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lab</label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama') }}" required>
            @error('nama')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="author" class="form-label">Nama Pengguna</label>
            <input type="text" class="form-control @error('author') is-invalid @enderror" name="author" value="{{ old('author') }}" required>
            @error('author')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn btn-primary">Mulai</button>
    </form>
</div>
@endsection
