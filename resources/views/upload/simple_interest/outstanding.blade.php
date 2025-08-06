<head>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<div class="content-wrapper">
    <div class="main-content" style="padding-top: 20px;">
        <div class="container mt-5" style="padding-right: 50px; overflow: visible;">
            <section class="section" style="overflow: visible;">
                <div class="section-header">
                    <h4>DATA TABLE OUTSTANDING - SIMPLE INTEREST</h4>
                </div>
                
                
                <div class="d-flex justify-content-start mb-0 align-items-center">
                    <button type="button" class="btn btn-success btn-icon-split" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import"></i> Upload
                    </button>
                    <button type="button" class="btn btn-danger btn-icon-split me-2" data-bs-toggle="modal" data-bs-target="#clearModal">
                        <i class="fas fa-trash"></i> Clear
                    </button>
                    <button type="button" class="btn btn-warning btn-icon-split me-2" data-bs-toggle="modal" data-bs-target="#executeModal">
                        <i class="fas fa-play"></i> Execute
                    </button>
                    <!-- Tambahan input bulan dan tahun -->
                    <div class="d-flex align-items-center">
                        <select class="form-select me-2" style="width: 120px;" id="monthSelect">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>

                        <input type="number" class="form-select me-2" id="yearInput" 
                               style="width: 100px;" 
                               value="{{ date('Y') }}" 
                               min="2000" 
                               max="2099">
                    </div>
                </div>
                
                <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
                    @if($tblmaster->count() > 0)
                    <table class="table table-striped table-bordered custom-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NO_ACC</th>
                                <th>NO_BRANCH</th>
                                <th>TAHUN</th>
                                <th>BULAN</th>
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
                                <th>PROV</th>
                                <th>TRXCOST</th>
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
                                <th>GOL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tblmaster as $key => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->no_acc }}</td>
                                <td>{{ $item->no_branch }}</td>
                                <td>{{ $item->tahun }}</td>
                                <td>{{ $item->bulan }}</td>
                                <td>{{ $item->deb_name }}</td>
                                <td>{{ $item->status }}</td>
                                <td>{{ $item->ln_type }}</td>
                                <td>{{ $item->org_date }}</td>
                                <td>{{ $item->org_date_dt ? date('d/m/Y', strtotime($item->org_date_dt)) : '' }}</td>
                                <td>{{ $item->term }}</td>
                                <td>{{ $item->mtr_date }}</td>
                                <td>{{ $item->mtr_date_dt ? date('d/m/Y', strtotime($item->mtr_date_dt)) : '' }}</td>
                                <td class="text-right">{{ number_format($item->org_bal, 2) }}</td>
                                <td class="text-right">{{ number_format($item->rate * 100, 2) }}</td>
                                <td class="text-right">{{ number_format($item->cbal, 2) }}</td>
                                <td class="text-right">{{ number_format($item->prebal, 2) }}</td>
                                <td class="text-right">{{ number_format($item->bilprn, 2) }}</td>
                                <td class="text-right">{{ number_format($item->pmtamt, 2) }}</td>
                                <td class="text-right">{{ number_format($item->prov, 2) }}</td>
                                <td class="text-right">{{ number_format($item->trxcost, 2) }}</td>
                                <td>{{ $item->lrebd }}</td>
                                <td>{{ $item->lrebd_dt ? date('d/m/Y', strtotime($item->lrebd_dt)) : '' }}</td>
                                <td>{{ $item->nrebd }}</td>
                                <td>{{ $item->nrebd_dt ? date('d/m/Y', strtotime($item->nrebd_dt)) : '' }}</td>
                                <td>{{ $item->ln_grp }}</td>
                                <td>{{ $item->GROUP }}</td>
                                <td class="text-right">{{ number_format($item->bilint, 2) }}</td>
                                <td>{{ $item->bisifa }}</td>
                                <td>{{ $item->birest }}</td>
                                <td>{{ $item->freldt }}</td>
                                <td>{{ $item->freldt_dt ? date('d/m/Y', strtotime($item->freldt_dt)) : '' }}</td>
                                <td>{{ $item->resdt }}</td>
                                <td>{{ $item->resdt_dt ? date('d/m/Y', strtotime($item->resdt_dt)) : '' }}</td>
                                <td>{{ $item->restdt }}</td>
                                <td>{{ $item->restdt_dt ? date('d/m/Y', strtotime($item->restdt_dt)) : '' }}</td>
                                <td>{{ $item->gol }}</td>
                            </tr>
                            @endforeach
                            
                            <!-- Tambahkan row total -->
                            <tr class="table-secondary font-weight-bold">
                                <td colspan="13" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('org_bal'), 2) }}</strong></td>
                                <td></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('cbal'), 0) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('prebal'), 0) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('bilprn'), 0) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('pmtamt'), 0) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('prov'), 0) }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('trxcost'), 0) }}</strong></td>
                                <td colspan="7"></td>
                                <td class="text-right"><strong>{{ number_format($tblmaster->sum('bilint'), 0) }}</strong></td>
                                <td colspan="9"></td>
                            </tr>
                        </tbody>
                    </table>
                    @else
                    <div class="no-data-message text-center py-5 mt-3">
                        <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Data Not Found</h5>
                        <p class="text-muted">No data available for Year: {{ $tahun }} and Month: {{ date('F', mktime(0, 0, 0, $bulan, 1)) }}</p>
                    </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <!-- Per Page Selector -->
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="form-label mb-0 me-2">Show</label>
                        <select id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage()">
                            <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    
                    <!-- Pagination Links -->
                    <div class="ms-3">
                        {{ $tblmaster->links() }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Upload File Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('simple-interest.outstanding.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulan" class="form-label">Month:</label>
                                <select name="bulan" id="bulan" class="form-control">
                                    <option value="1" {{ date('n') == 1 ? 'selected' : '' }}>January</option>
                                    <option value="2" {{ date('n') == 2 ? 'selected' : '' }}>February</option>
                                    <option value="3" {{ date('n') == 3 ? 'selected' : '' }}>March</option>
                                    <option value="4" {{ date('n') == 4 ? 'selected' : '' }}>April</option>
                                    <option value="5" {{ date('n') == 5 ? 'selected' : '' }}>May</option>
                                    <option value="6" {{ date('n') == 6 ? 'selected' : '' }}>June</option>
                                    <option value="7" {{ date('n') == 7 ? 'selected' : '' }}>July</option>
                                    <option value="8" {{ date('n') == 8 ? 'selected' : '' }}>August</option>
                                    <option value="9" {{ date('n') == 9 ? 'selected' : '' }}>September</option>
                                    <option value="10" {{ date('n') == 10 ? 'selected' : '' }}>October</option>
                                    <option value="11" {{ date('n') == 11 ? 'selected' : '' }}>November</option>
                                    <option value="12" {{ date('n') == 12 ? 'selected' : '' }}>December</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tahun" class="form-label">Year:</label>
                                <input type="number" name="tahun" id="tahun" class="form-control" value="{{ date('Y') }}" required>
                            </div>
                        </div>
                    </div>
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

<!-- Clear Modal -->
<div class="modal fade" id="clearModal" tabindex="-1" aria-labelledby="clearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearModalLabel">Konfirmasi Hapus Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('simple-interest.outstanding.clear') }}" method="POST">
                @csrf
                <input type="hidden" name="tahun" id="clearTahun">
                <input type="hidden" name="bulan" id="clearBulan">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data untuk:</p>
                    <p>Bulan: <span id="displayBulan"></span></p>
                    <p>Tahun: <span id="displayTahun"></span></p>
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

<!-- Execute Modal -->
<div class="modal fade" id="executeModal" tabindex="-1" aria-labelledby="executeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="executeModalLabel">Execute Stored Procedure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('simple-interest.outstanding.execute-procedure') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="execute_bulan" class="form-label">Bulan:</label>
                                <select name="bulan" id="execute_bulan" class="form-control">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="execute_tahun" class="form-label">Tahun:</label>
                                <input type="number" name="tahun" id="execute_tahun" class="form-control" value="{{ date('Y') }}" required>
                            </div>
                        </div>
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

<style>
    /* Gunakan style yang sama dengan accrual interest */
    .custom-table {
        width: 100%;
        margin: 20px auto;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .custom-table th, .custom-table td {
        padding: 8px 12px;
        white-space: nowrap;
    }
    
    .text-right {
        text-align: right;
        padding-right: 20px;
    }
    
    .no-data-message {
        padding: 20px;
    }
    
    .no-data-message h5 {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .no-data-message p {
        color: #888;
        margin-bottom: 0;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
    }

    .form-control, .form-select {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 8px 12px;
        font-size: 14px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    .text-success {
        color: #28a745;
    }

    .text-danger {
        color: #dc3545;
    }


    .alert {
        margin-bottom: 0;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .clickable-account {
        cursor: pointer;
        color: #007bff;
        text-decoration: underline;
    }

    .clickable-account:hover {
        color: #0056b3;
    }

    .dropdown-menu {
        margin: 0;
        padding: 0.5rem 0;
        position: absolute;
        z-index: 1000;
        min-width: 200px;
        background-color: #fff; /* Memastikan background putih */
    }

    .dropdown-item {
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #212529 !important; /* Memastikan teks selalu hitam */
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #16181b !important;
    }

    .dropdown-submenu {
        display: none;
        position: absolute;
        left: 100%;
        top: -7px;
        z-index: 1001;
        min-width: 200px;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.25rem;
    }

    .dropdown-menu > li:hover > .dropdown-submenu {
        display: block;
    }

    .dropdown-item i {
        margin-right: 8px;
        width: 20px;
        line-height: 1;
        vertical-align: middle;
        color: #212529; /* Memastikan ikon juga hitam */
    }

    .dropdown-item i.float-end {
        margin-right: 0;
        margin-left: 8px;
    }

    .container {
        overflow: visible !important;
    }

    .section {
        overflow: visible !important;
    }

    .dropdown {
        position: relative;
    }

    .btn-success {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
        margin-right: 10px;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-icon-split {
        display: inline-flex;
        align-items: center;
    }

    .btn-icon-split i {
        margin-right: 8px;
    }

    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }

    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
    }

    .pagination .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    #per_page {
        width: auto;
        min-width: 70px;
        padding: 4px 8px;
        font-size: 14px;
    }

    .no-data-message {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .no-data-message h5 {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .no-data-message p {
        color: #888;
        margin-bottom: 0;
    }

    .no-data-message i {
        color: #6c757d;
    }

    .table-info {
        background-color: #f2f2f2 !important; /* Warna abu-abu light */
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .text-end {
        text-align: right;
    }
</style>

<script>
// Tambahkan variable untuk menyimpan URL route
const reportUrl = "{{ route('simple-interest.outstanding.index') }}";

// Set nilai default untuk bulan dan tahun dari parameter URL atau data yang dikirim dari controller
document.addEventListener('DOMContentLoaded', function() {
    // Ambil nilai bulan dari controller
    const selectedMonth = "{{ $bulan ?? date('n') }}";
    const selectedYear = "{{ $tahun ?? date('Y') }}";
    
    // Set nilai default untuk select bulan
    document.getElementById('monthSelect').value = selectedMonth;
    document.getElementById('yearInput').value = selectedYear;

    // Event listener untuk modal clear
    $('#clearModal').on('show.bs.modal', function () {
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearInput').value;
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        document.getElementById('clearBulan').value = month;
        document.getElementById('clearTahun').value = year;
        document.getElementById('displayBulan').textContent = monthNames[month - 1];
        document.getElementById('displayTahun').textContent = year;
    });

    // Set nilai default untuk modal execute
    const currentMonth = "{{ date('n') }}";
    const currentYear = "{{ date('Y') }}";
    
    document.getElementById('execute_bulan').value = currentMonth;
    document.getElementById('execute_tahun').value = currentYear;
});

// Event listener untuk perubahan bulan atau tahun
document.getElementById('monthSelect').addEventListener('change', updateReport);
document.getElementById('yearInput').addEventListener('change', updateReport);

function updateReport() {
    const month = document.getElementById('monthSelect').value;
    const year = document.getElementById('yearInput').value;
    const branch = {{ Auth::user()->id_pt  }}; // Sesuaikan dengan nilai branch yang diinginkan
    
    window.location.href = `${reportUrl}?bulan=${month}&tahun=${year}&branch=${branch}`;
}

function changePerPage() {
    const perPage = document.getElementById('per_page').value;
    const month = document.getElementById('monthSelect').value;
    const year = document.getElementById('yearInput').value;
    const url = new URL(window.location.href);
    
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('bulan', month);
    url.searchParams.set('tahun', year);
    url.searchParams.delete('page'); // Reset halaman ke 1 saat mengubah jumlah per halaman
    
    window.location.href = url;
}

</script>

<script>
    // Check if there's a flash message
    @if(session('swal'))
        Swal.fire({
            title: "{{ session('swal')['title'] }}",
            text: "{{ session('swal')['text'] }}",
            icon: "{{ session('swal')['icon'] }}",
            confirmButtonText: 'OK'
        });
    @endif
</script>