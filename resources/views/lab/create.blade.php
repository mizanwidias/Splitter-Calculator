@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Buat Lab Baru</h4>
    <form method="POST" action="/lab">
        @csrf
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lab</label>
            <input type="text" class="form-control" name="nama" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
        </div>
        <button class="btn btn-primary">Mulai</button>
    </form>
</div>
@endsection
