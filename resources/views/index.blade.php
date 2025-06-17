@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h4>Kalkulator Splitter Loss (FTTH)</h4>
    </div>
    <div class="card-body">
      <form id="lossForm">
      <div class="mb-3">
            <label for="light_source" class="form-label">Light Source (dBm)</label>
            <input type="number" step="0.01" class="form-control" id="light_source" name="light_source" placeholder="-3 dBm" required>
        </div>

        {{-- Field lain seperti panjang kabel, splicing, konektor, splitter --}}
        <div class="mb-3">
            <label for="kabel_panjang" class="form-label">Panjang Kabel (meter)</label>
            <input type="number" class="form-control" id="kabel_panjang" name="kabel_panjang" required>
        </div>

        <div class="mb-3">
            <label for="jumlah_splicing" class="form-label">Jumlah Splicing</label>
            <input type="number" class="form-control" id="jumlah_splicing" name="jumlah_splicing" required>
        </div>

        <div class="mb-3">
            <label for="jumlah_konektor" class="form-label">Jumlah Konektor</label>
            <input type="number" class="form-control" id="jumlah_konektor" name="jumlah_konektor" required>
        </div>

        <div class="mb-3">
            <label for="nilai_splitter" class="form-label">Nilai Splitter (dB)</label>
            <input type="number" step="0.01" class="form-control" id="nilai_splitter" name="nilai_splitter" required>
        </div>
        <div class="row mb-4">
          <label class="col-sm-4 col-form-label">Tipe Splitter</label>
          <div class="col-sm-8">
            <select id="splitter" class="form-select">
              <option value="3.5">1:2</option>
              <option value="7.2">1:4</option>
              <option value="10.5">1:8</option>
              <option value="13.5">1:16</option>
              <option value="17.0">1:32</option>
              <option value="20.5">1:64</option>
            </select>
          </div>
        </div>
        <div class="text-end">
          <button type="button" onclick="hitungLoss()" class="btn btn-primary">Hitung Total Loss</button>
        </div>
      </form>

      <div id="hasil" class="alert mt-4 d-none">
        <strong>Total Loss:</strong> <span id="totalLoss" class="fw-bold"></span> dB<br>
        <span id="statusText" class="fw-bold"></span>
      </div>
    </div>
  </div>
</div>

<script>
  function hitungLoss() {
    const kabel = parseFloat(document.getElementById('kabel').value) || 0
    const splice = parseInt(document.getElementById('splicing').value) || 0
    const connector = parseInt(document.getElementById('connector').value) || 0
    const splitter = parseFloat(document.getElementById('splitter').value) || 0

    const loss = kabel * 0.35 + splice * 0.1 + connector * 0.3 + splitter
    const lossText = loss.toFixed(2)
    const hasil = document.getElementById('hasil')
    const statusText = document.getElementById('statusText')
    const lossField = document.getElementById('totalLoss')

    hasil.classList.remove('d-none')
    lossField.textContent = lossText

    if (loss < 20) {
      hasil.className = 'alert alert-success mt-4'
      statusText.textContent = 'Status: OK (Bagus)'
    } else if (loss < 28) {
      hasil.className = 'alert alert-warning mt-4'
      statusText.textContent = 'Status: Warning (Perlu Dicek)'
    } else {
      hasil.className = 'alert alert-danger mt-4'
      statusText.textContent = 'Status: Tinggi (Tidak Direkomendasikan)'
    }
  }
</script>
@endsection
