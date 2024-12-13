<div class="content-wrapper">
    <div class="main-content" style="padding-top: 20px;">
        <div class="container mt-5" style="padding-right: 50px;">
            <section class="section">
                <div class="section-header">
                    <h4>REPORT CALCULATED ACCRUAL COUPON - TREASURY</h4>
                </div>
                @if(session('pesan'))
                    <div class="alert alert-success">{{ session('pesan') }}</div>
                @endif
                <div class="table-responsive text-center">
                    <table class="table table-striped table-bordered custom-table" style="width: 100%; margin: 0 auto;">
                        <thead>
                            <tr>
                                <th style="width: 15%; white-space: nowrap;">Account Number</th>
                                <th style="width: 20%; white-space: nowrap;">Deal Number</th>
                                <th style="width: 15%; white-space: nowrap;">Issuer Name</th>
                                <th style="width: 15%; white-space: nowrap;">Face Value</th>
                                <th style="width: 10%; white-space: nowrap;">Settlement Date</th>
                                <th style="width: 15%; white-space: nowrap;">Tenor (TTM)</th>
                                <th style="width: 10%; white-space: nowrap;">Maturity Date</th>
                                <th style="width: 15%; white-space: nowrap;">Coupon Rate</th>
                                <th style="width: 15%; white-space: nowrap;">Price</th>
                                <th style="width: 15%; white-space: nowrap;">Fair Value</th>
                                <th style="width: 15%; white-space: nowrap;">Outstanding Amount</th>
                                <th style="width: 15%; white-space: nowrap;">EIR Calculated Convertion</th>
                                <th style="width: 10%; white-space: nowrap;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($loans as $loan)
                                <tr>
                                    <td>{{ $loan->no_acc }}</td>
                                    <td>{{$loan->bond_id}}</td>
                                    <td>{{$loan->issuer_name}}</td>
                                    <td>{{ date('d/m/Y', strtotime($loan->face_value)) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($loan->settle_dt)) }}</td>
                                    <td>{{ $loan->tenor}} Tahun</td>
                                    <td>{{ date('d/m/Y', strtotime($loan->mtr_date)) }}</td>
                                    <td>{{ number_format($loan->coupon_rate*100,5) }}%</td>
                                    <td>{{ number_format($loan->price*100,5)}}</td>
                                    <td>{{ number_format((float) str_replace(['$', ','], '', $loan->fair_value)) }}</td>
                                    <td></td>
                                    <td>{{ number_format($loan->eircalc_conv*100,14)}}%</td>
                                    <td>
                                        <a href="{{ route('report-calculated-accrual-coupon.view', ['no_acc' => $loan->no_acc, 'id_pt' => $loan->id_pt])  }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye" style="margin-right: 5px;"></i> View
                                        </a>
                                        {{-- <a href="{{route('under')}}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye" style="margin-right: 5px;"></i> View
                                        </a> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Pagination Links -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="showing-entries">
                    Showing
                    {{$loans->firstItem()}}
                    to
                    {{$loans->lastItem()}}
                    of
                    {{$loans->total()}}
                    Results
                </div>
                <div class="d-flex align-items-center">
                    {{ $loans->appends(['per_page' => request('per_page')])->links('pagination::bootstrap-4') }}
                    <label for="per_page" class="form-label mb-0" style="font-size: 0.8rem; margin-right: 15px; margin-left:30px;">Show</label>
                    <select id="per_page" class="form-select form-select-sm" onchange="changePerPage()" style="width: auto;">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    function changePerPage() {
        const perPage = document.getElementById('per_page').value;
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page'); // Hapus parameter page saat mengganti per_page
        window.location.href = url;
    }
</script>


<!-- Custom CSS -->
<style>
    body {
        display: fixed;
        background-color: #f4f7fc;
        font-family: 'Arial', sans-serif;
    }
    .main-content {
        margin-left: 20px; /* Diperbarui untuk menghapus margin kiri */
        width: 100%; /* Diperbarui lebar */
        padding-top: fixed;
        padding-right: fixed;
    }
    /* STYLE PAGINATION */
    .showing-entries {
        font-size: 14px;
    }
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    #per_page {
    width: 80px; /* Lebar default */
    min-width: 100px; /* Lebar minimum */
    max-width: 150px; /* Lebar maksimum */
    transition: all 0.3s ease; /* Efek transisi halus */
    border-radius: 5px; /* Sudut membulat */
    padding: 5px; /* Jarak dalam */
    cursor: pointer; /* Gaya kursor */
}

/* Tambahkan efek saat dropdown dibuka */
#per_page:focus {
    outline: none; /* Hilangkan outline default */
    box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5); /* Shadow saat aktif */
    border-color: #007bff; /* Warna border aktif */
}

#per_page:focus {
        box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        background-color: #f0f8ff;
        transform: scale(1.05);
    }

    #per_page option {
        transition: background-color 0.2s ease;
    }

    #per_page option:hover {
        background-color: #73b9ff;
    }
    /* STYLE PAGINATION */

    .section-header h4 {
        font-size: 26px;
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
        font-weight: 700;
    }
    .custom-table {
        width: 100%; /* Full width to use available space */
        margin: 20px auto;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        border-radius: 12px;
        font-size: 10px;
    }
    .custom-table th, .custom-table td {
        padding: 8px 12px;
        text-align: center;
        vertical-align: middle;
    }
    .custom-table thead {
        background-color: #4a90e2;
        color: #fff;
    }
    .custom-table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .custom-table tbody tr:hover {
        background-color: #e1f5fe;
        transition: background-color 0.3s ease;
    }
    .custom-table th {
        text-transform: uppercase;
        font-weight: 500;
        font-size: 10px;
        white-space: nowrap;
    }
    .custom-table td a {
        text-decoration: none;
        color: #fff;
        font-size: 12px;
    }
    .custom-table td a.btn-info {
        background-color: #00bcd4;
        padding: 5px 10px;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .custom-table td a.btn-info i {
        margin-right: 5px;
        vertical-align: middle;
    }
    .custom-table td a.btn-info:hover {
        background-color: #0097a7;
        transform: scale(1.05);
    }
</style>

<!-- Font Awesome Link -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous">
function changePerPage() {
        const perPage = document.getElementById('per_page').value;
        window.location.href = `?per_page=${perPage}`; // Redirect dengan parameter per_page
    }
</script>
