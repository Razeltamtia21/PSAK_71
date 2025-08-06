<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Table Corporate Loan Cabang Detail</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>

    body {
      background-color: #f8f9fa;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 12px;
    }
    .section-header {
      margin-top: 60px;
    }
    h1 {
      font-size: 1.5rem;
      font-weight: bold;
      color: #4e73df;
    }
    .btn-icon-split {
      transition: background-color 0.3s, transform 0.3s;
    }
    .btn-icon-split .icon {
      padding-right: 10px;
    }
    .table thead th {
      vertical-align: middle;
      background-color: #4e73df;
      color: #ffff;
      font-weight: bold;
    }
    .table tbody tr {
      vertical-align: middle;
    }
    .table tbody tr:hover {
        background-color: #e1f5fe; /* Warna biru muda saat dihover */
    }
    .table-responsive {
      margin-top: 10px;
    }
    .card-header {
      background-color: #4e73df;
      color: white;
    }
    .modal-header {
      background-color: #4e73df;
      color: white;
    }
    /* Hover effects untuk tombol */
    .btn-success:hover {
      background-color: #28a745; /* Warna hijau saat dihover */
      color: rgb(255, 255, 255); /* Teks tetap putih */
      transform: scale(1.05); /* Meningkatkan ukuran sedikit saat dihover */
    }
    .btn-warning:hover {
      background-color: #ffc107; /* Warna kuning saat dihover */
      color: black; /* Teks menjadi hitam agar lebih jelas */
      transform: scale(1.05); /* Meningkatkan ukuran sedikit saat dihover */
    }
    .btn-danger:hover {
        background-color: #dc3545;
        color: white;
        transform: scale(1.05);
    }
  </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container mt-5 ms-5">
            <div class="section-header text-center mb-4">
                <h1>Data Table Corporate Loan Cabang Detail</h1>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button type="button" class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#importModal">

                            <i class="fas fa-file-import"></i> Upload

                    </button>

                    <button type="button" class="btn btn-warning btn-icon-split" data-bs-toggle="modal" data-bs-target="#executeModal">

                            <i class="fas fa-play"></i> Execute

                    </button>

                    <button type="button" class="btn btn-danger btn-icon-split" data-bs-toggle="modal" data-bs-target="#clearModal">
                        <i class="fas fa-trash"></i> Clear Data
                    </button>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">tblCorporateLoanCabangDetail</h6>
                </div>
                <div class="table-responsive text-center">
                    <table class="table table-hover table-bordered mx-auto">
                        <thead class="text-center">
                            <tr>
                                <th>IDTRX</th>
                                <th>ID_KTR_CABANG</th>
                                <th>CIF_BANK</th>
                                <th>NO_REKENING</th>
                                <th>STATUS</th>
                                <th>NAMA_DEBITUR</th>
                                <th>MAKSIMAL_KREDIT</th>
                                <th>TANGGAL_REALISASI</th>
                                <th>SUKU_BUNGA</th>
                                <th>JANGKA_WAKTU</th>
                                <th>TGL_JATUH_TEMPO</th>
                                <th>SIFAT_KREDIT</th>
                                <th>JENIS_KREDIT</th>
                                <th>JNS_TRANSAKSI</th>
                                <th>TGL_TRANSAKSI</th>
                                <th>NILAI_PENARIKAN</th>
                                <th>NILAI_PENGEMBALIAN</th>
                                <th>CBAL</th>
                                <th>CUTOFF_DATE</th>
                                <th>KELONGGARAN_TARIK</th>
                                <th>TGL_RESTRUCT</th>
                                <th>TGL_RESTRUCT_REVIEW</th>
                                <th>KET_RESTRUCT</th>
                                <th>NOMINAL_ANGSURAN</th>
                                <th>STATUS_PSAK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tblcorporateloancabangdetail as $tb)
                                <tr class="text-center">
                                    <td>{{ $tb->id }}</td>
                                    <td>{{ $tb->id_ktr_cabang }}</td>
                                    <td>{{ $tb->cif_bank }}</td>
                                    <td>{{ $tb->no_rekening }}</td>
                                    <td>{{ $tb->status }}</td>
                                    <td>{{ $tb->nama_debitur }}</td>
                                    <td>{{ $tb->maksimal_kredit }}</td>
                                    <td>{{ $tb->tanggal_realisasi ? date('d/m/Y', strtotime($tb->tanggal_realisasi)) : '' }}</td>
                                    <td>{{ $tb->suku_bunga }}</td>
                                    <td>{{ $tb->jangka_waktu }}</td>
                                    <td>{{ $tb->tgl_jatuh_tempo ? date('d/m/Y', strtotime($tb->tgl_jatuh_tempo)) : '' }}</td>
                                    <td>{{ $tb->sifat_kredit }}</td>
                                    <td>{{ $tb->jenis_kredit }}</td>
                                    <td>{{ $tb->jns_transaksi }}</td>
                                    <td>{{ $tb->tgl_transaksi ? date('d/m/Y', strtotime($tb->tgl_transaksi)) : '' }}</td>
                                    <td>{{ $tb->nilai_penarikan }}</td>
                                    <td>{{ $tb->nilai_pengembalian }}</td>
                                    <td>{{ $tb->cbal }}</td>
                                    <td>{{ $tb->cutoff_date ? date('d/m/Y', strtotime($tb->cutoff_date)) : '' }}</td>
                                    <td>{{ $tb->kelonggaran_tarik }}</td>
                                    <td>{{ $tb->tgl_restruct ? date('d/m/Y', strtotime($tb->tgl_restruct)) : '' }}</td>
                                    <td>{{ $tb->tgl_restruct_review ? date('d/m/Y', strtotime($tb->tgl_restruct_review)) : '' }}</td>
                                    <td>{{ $tb->tgl_restruct_review ? date('d/m/Y', strtotime($tb->ket_restruct)) : '' }}</td>
                                    <td>{{ $tb->nominal_angsuran }}</td>
                                    <td>{{ $tb->status_psak }}</td>
                                </tr>
                            @endforeach

                            @if(empty($tblcorporateloancabangdetail))
                                <tr class="text-center"><td colspan="25">Data not found</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Import Modal -->
                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Upload File Excel</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('import.excel') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="uploadFile" class="form-label">Pilih File Excel:</label>
                                        <input type="file" name="uploadFile" id="uploadFile" class="form-control" accept=".csv,.xlsx,.xls" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Execute Modal -->
                <div class="modal fade" id="executeModal" tabindex="-1" aria-labelledby="executeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="executeModalLabel">Execute Stored Procedure</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('execute.stored.procedure') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="bulan" class="form-label">Bulan:</label>
                                        <input type="number" name="bulan" id="bulan" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tahun" class="form-label">Tahun:</label>
                                        <input type="number" name="tahun" id="tahun" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="pilihan" class="form-label">Pilihan (365/360):</label>
                                        <select name="pilihan" id="pilihan" class="form-select" required>
                                            <option value="365">365</option>
                                            <option value="360">360</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Execute</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Clear Modal -->
                <div class="modal fade" id="clearModal" tabindex="-1" aria-labelledby="clearModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="clearModalLabel">Konfirmasi Hapus Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('corporate.clear') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus semua data untuk PT ini?</p>
                                    <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan!</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Hapus Data</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>
