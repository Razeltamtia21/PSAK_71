<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Table Corporate Loan Cabang Detail</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        font-size: 12px;
      background-color: #f4f7fc;
      display: flex;
      justify-content: center; /* Center horizontally */
      align-items: center; /* Center vertically */
      height: 100vh; /* Full height of the viewport */
      margin: 0; /* Remove default margin */
    }
    .section-header {
      text-align: center;
      margin-top: 50px; /* Adjusted for better vertical centering */
      margin-bottom: 30px;
    }
    h1 {
        font-size: 25px;
      font-weight: bold;
      color: #007bff;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .table th, .table td {
      vertical-align: middle;
      padding: 12px;
    }
    .table th {
      background-color: #007bff;
      color: white;
    }
    .table-hover tbody tr:hover {
      background-color: #e1f5fe;
    }
    .modal-header {
      background-color: #007bff;
      color: white;
    }
    .btn-primary, .btn-warning, .btn-success {
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #0056b3;
      transform: scale(1.05);
    }
    .btn-warning:hover {
      background-color: #e0a800;
      transform: scale(1.05);
    }
    .btn-success:hover {
      background-color: #218838;
      transform: scale(1.05);
    }
  </style>
</head>
<body>
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="content-wrapper">
        <div class="container ms-5 mt-5">
            <div class="section-header">
                <h1>Data Table Master</h1>
            </div>

            <!-- Button Section -->
            <div class="d-flex justify-content-between mb-3">
                <!-- Button Section -->
                <div>
                    <button type="button" class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import"></i> Upload
                    </button>

                    <button type="button" class="btn btn-warning btn-icon-split" data-bs-toggle="modal" data-bs-target="#executeModal">
                        <i class="fas fa-play"></i> Execute
                    </button>

                    <!-- Tambahkan tombol Clear -->
                    <button type="button" class="btn btn-danger btn-icon-split" data-bs-toggle="modal" data-bs-target="#clearModal">
                        <i class="fas fa-trash"></i> Clear Data
                    </button>
                </div>
            </div>

            <!-- Tambahkan Modal Clear -->
            <div class="modal fade" id="clearModal" tabindex="-1" aria-labelledby="clearModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clearModalLabel">Konfirmasi Hapus Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('simple-interest.tblmaster.clear') }}" method="POST">
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

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">tblmaster</h6>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-hover table-bordered text-center">
                        <thead>
                            <tr>
                                <th>NO_ACC</th>
                                <th>NO_BRANCH</th>
                                <th>DEB_NAME</th>
                                <th>STATUS</th>
                                <th>LN_TYPE</th>
                                <th>ORG_DATE</th>
                                <th>ORG_DATE_DT</th>
                                <th>TERM</th>
                                <th>MTR_DATE</th>
                                <th>MTR_DATE_DT</th>
                                <th>ORG_BAL</th>
                                <th>RATE</th>
                                <th>CBAL</th>
                                <th>PREBAL</th>
                                <th>BILPRN</th>
                                <th>PMTAMT</th>
                                <th>LREBD</th>
                                <th>LREBD_DT</th>
                                <th>NREBD</th>
                                <th>NREBD_DT</th>
                                <th>LN_GRP</th>
                                <th>GROUP</th>
                                <th>BILINT</th>
                                <th>BISIFA</th>
                                <th>BIREST</th>
                                <th>FRELDT</th>
                                <th>FRELDT_DT</th>
                                <th>RESDT</th>
                                <th>RESDT_DT</th>
                                <th>RESTDT</th>
                                <th>RESTDT_DT</th>
                                <th>PROV</th>
                                <th>TRXCOST</th>
                                <th>GOL</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(count($tblmaster) > 0)
                            @foreach($tblmaster as $item)
                            <tr>
                                <td>{{ $item->no_acc }}</td>
                                <td>{{ $item->no_branch }}</td>
                                <td>{{ $item->deb_name }}</td>
                                <td>{{ $item->status }}</td>
                                <td>{{ $item->ln_type }}</td>
                                <td>{{ $item->org_date }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->org_date_dt)) }}</td>
                                <td>{{ $item->term }}</td>
                                <td>{{ $item->mtr_date }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->mtr_date_dt)) }}</td>
                                <td class="text-right">{{ number_format($item->org_bal, 2) }}</td>
                                <td class="text-right">{{ number_format($item->rate*100, 5) }}%</td>
                                <td class="text-right">{{ number_format($item->cbal, 2) }}</td>
                                <td class="text-right">{{ number_format($item->prebal, 2) }}</td>
                                <td class="text-right">{{ number_format($item->bilprn, 2) }}</td>
                                <td class="text-right">{{ number_format($item->pmtamt, 2) }}</td>
                                <td>{{ $item->lrebd }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->lrebd_dt)) }}</td>
                                <td>{{ $item->nrebd }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->nrebd_dt)) }}</td>
                                <td>{{ $item->ln_grp }}</td>
                                <td>{{ $item->group }}</td>
                                <td class="text-right">{{ number_format($item->bilint, 2) }}</td>
                                <td class="text-right">{{ number_format($item->bisifa, 2) }}</td>
                                <td class="text-right">{{ number_format($item->birest, 2) }}</td>
                                <td>{{ $item->freldt }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->freldt_dt)) }}</td>
                                <td>{{ $item->resdt }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->resdt_dt)) }}</td>
                                <td>{{ $item->restdt }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->restdt_dt)) }}</td>
                                <td class="text-right">{{ number_format($item->prov, 2) }}</td>
                                <td class="text-right">{{ number_format($item->trxcost, 2) }}</td>
                                <td>{{ $item->gol }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="34" class="text-center">No data available</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            @if(empty($tblmaster))
            <div class="alert alert-warning text-center mt-3">Data not found</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('simple-interest.tblmaster.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="uploadFile" class="form-label">Pilih File Excel/CSV</label>
                                <input type="file" name="uploadFile" id="uploadFile" class="form-control" accept=".xlsx,.csv" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Import</button>
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
                    <form action="{{ route('simple-interest.tblmaster.execute-procedure') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="bulan" class="form-label">Bulan:</label>
                                <input type="number" name="bulan" id="bulan" class="form-control" required min="1" max="12">
                            </div>
                            <div class="mb-3">
                                <label for="tahun" class="form-label">Tahun:</label>
                                <input type="number" name="tahun" id="tahun" class="form-control" required min="2000">
                            </div>
                            <!-- <div class="mb-3">
                                <label for="no_acc" class="form-label">Nomor Akun:</label>
                                <select name="no_acc" id="no_acc" class="form-select" required>
                                    <option value="">Pilih Nomor Akun</option>
                                    @foreach($tblmaster as $item)
                                        <option value="{{ $item->no_acc }}">{{ $item->no_acc }} - {{ $item->deb_name }}</option>
                                    @endforeach
                                </select>
                            </div> -->
                            <div class="mb-3">
                                <label for="pilihan" class="form-label">Pilihan (365/360):</label>
                                <select name="pilihan" id="pilihan" class="form-select" required>
                                    <option value="">Pilih pilihan</option>
                                    <option value="365">365</option>
                                    <option value="360">360</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Execute</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Modal -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Notifikasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="notificationMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      $(document).ready(function() {
        @if(session('success'))
          $('#notificationMessage').text("{{ session('success') }}");
          $('#notificationModal').modal('show');
        @elseif(session('error'))
          $('#notificationMessage').text("{{ session('error') }}");
          $('#notificationModal').modal('show');
        @endif
      });
    </script>
</body>
</html>
