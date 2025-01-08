<head>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<div class="content-wrapper" style="font-size: 12px;">
    <div class="main-content" style="padding-top: 20px;">
        <div class="container mt-5">
            <section class="section">
                

                <!-- Loan Details Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"style="font-size: 16px;">REPORT OUTSTANDING - SIMPLE INTEREST</h5>
                    </div>
                    
                </div>
                
                <div class="mb-3 d-flex justify-content-start align-items-center gap-2">
                    <div class="dropdown me-1">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-import"></i> Bulan/Tahun
                        </button>
                    </div>
                    <!--
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Accrual Interest <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('accrual_interest_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('accrual_interest_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Amortised Cost <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('amortised_cost_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('amortised_cost_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Amortised Initial Cost <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('amortised_initial_cost_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('amortised_initial_cost_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Amortised Initial Fee <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('amortised_initial_fee_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('amortised_initial_fee_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Expected Cash Flow <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('expected_cashflow_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('expected_cashflow_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Outstanding <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('outstanding_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('outstanding_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                    Journal <i class="fas fa-chevron-right float-end"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="#" onclick="showModal('journal_effective')">Effective</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="showModal('journal_simple')">Simple Interest</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>-->

                    <div class="d-flex align-items-center ">
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

                        <input type="number" class="form-select" id="yearInput" 
                               style="width: 100px;" 
                               value="{{ date('Y') }}" 
                               min="2000" 
                               max="2099">
                    </div>

                    <a href="#" class="btn btn-danger" id="exportPdf"><i class="fas fa-file-pdf"></i>Export to PDF</a>
                    <a href="#" class="btn btn-success" id="exportExcel"><i class="fas fa-file-excel"></i>Export to Excel</a>
                    <a href="#" class="btn btn-primary" id="exportCsv"><i class="fas fa-file-csv"></i>Download File CSV</a> 
                </div>

                <!-- Report Table -->
                <h2 style="font-size: 16px;">Report Details</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" style=" white-space: nowrap; font-size: 12px;">
                        <thead class="thead-dark text-center">
                            <tr>
                                <th>No.</th>
                                <th>Entity Number</th>
                                <th style="white-space: nowrap; ">Account Number</th>
                                <th>Debitor Name</th>
                                <th>GL Account</th>
                                <th>Loan Type</th>
                                <th>GL Group</th>
                                <th>Original Date</th>
                                <th>Term (Months)</th>
                                <th>Maturity Date</th>
                                <th>Interest Rate</th>
                                <th>EIR Amortised Cost Exposure</th>
                                <th>EIR Amortised Cost Calculated
                                <th>Current Balance</th>
                                <th>Carrying Amount</th>
                                <th>Outstanding Receivable</th>
                                <th>Outstanding Interest</th>
                                <th>Unamortized Transaction Cost</th>
                                <th>Unamortized UpFront Fee</th>
                                <th>Unearned Interest Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($master->isEmpty())
                                <tr>
                                    <td colspan="22" class="text-center">Data tidak ditemukan atau belum di-generate</td>
                                </tr>
                            @else
                            @php
                            $totalOutstandingInterest = 0;
                            $outstandingReceivable = 0;
                            $outstandingInterest = 0;
                            $totalUnamortCost = 0;
                            $totalUnamortFee = 0;
                            $totalInterestIncome = 0;
                            $totalOutstandingReceivable = 0;
                            $nourut =0;
                            @endphp
                                @foreach ($master as $index => $loan)
                                @php
                                $trxcost = $loan->trxcost; 
                                $trxcost = preg_replace('/[^\d.]/', '', $trxcost);
                                $trxcostFloat = (float)$trxcost;
                                $outstandingInterest = $loan->bilint ?? 0;
                                $totalOutstandingInterest += $outstandingInterest;
                                $amortized = $loan->cum_amortisecost; // Ambil nilai amortized dari laporan
                                    // Hitung nilai unamortized
                                    if ($loop->first) {
                                        // Untuk baris pertama, gunakan nilai trxcost
                                        $unamortCost = $trxcostFloat;;
                                    } else {
                                        // Untuk baris selanjutnya, hitung unamortized berdasarkan cumulative amortized
                                        $unamortCost = $trxcostFloat - $amortized;
                                    }
                                $totalUnamortCost += $unamortCost;

                                $prov = $loan->prov; // Ambil nilai dari database
                                // Hapus simbol mata uang dan pemisah ribuan
                                $prov = preg_replace('/[^\d.]/', '', $prov);
                                // Konversi ke float
                                $provFloat = (float)$prov* -1;
                                $amortizedUpFrontFee = $loan->cum_amortisefee;

                                // Hitung nilai unamortized Fee
                                $unamortFee = $loan->prov * -1 + $loan->cum_amortisefee;
                                $totalUnamortFee += $unamortFee;

                                $bunga = $loan->cum_bunga;
                                $totalInterestIncome += $loan->cum_bunga;
                                // hitung nilai unaerned interest income
                                //if ($loop->first) {
                                // $interestIncome = $totalInterestIncome;
                                // } else {
                                //       $totalInterestIncome -= $bunga;
                                //      $interestIncome = $totalInterestIncome;
                                //}

                                $bilint = $loan->bilint;
                                $bilprn = $loan->bilprn;
                                $outstandingReceivable = $bilprn + $bilint;
                                $totalOutstandingReceivable += $outstandingReceivable;
                                $nourut += 1;
                                @endphp
                                    <tr>
                                            <td class="text-center">{{ $nourut }}</td>
                                            <td class="text-center">{{ $loan->no_branch }}</td>
                                            <td class="text-center" style="width: 200px;">
                                            <div class="dropdown">
                                                <span class="clickable-account" data-bs-toggle="dropdown" aria-expanded="false">
                                                    {{ $loan->no_acc }}
                                                </span>
                                                
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Accrued Interest <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'accrual_interest_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'accrual_interest_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Amortised Cost <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'amortised_cost_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'amortised_cost_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Amortised Initial Cost <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'amortised_initial_cost_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'amortised_initial_cost_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Amortised Initial Fee <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'amortised_initial_fee_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'amortised_initial_fee_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Expected Cash Flow <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'expected_cashflow_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'expected_cashflow_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Outstanding <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'outstanding_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'outstanding_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="dropdown">
                                                            Journal <i class="fas fa-chevron-right float-end"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-submenu">
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'journal_effective')">Effective</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="showModalWithAccount('{{ $loan->no_acc }}', 'journal_simple')">Simple Interest</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                            <td class="text-center">{{ $loan->deb_name }}</td>
                                            <td class="text-center">{{ $loan->coa }}</td>
                                            <td class="text-center">{{ $loan->ln_type }}</td>
                                            <td class="text-center">{{ $loan->GROUP }}</td>
                                            <td class="text-center">{{ date('d/m/Y', strtotime($loan->org_date_dt)) }}</td>
                                            <td class="text-center">{{ $loan->term }}</td>
                                            <td class="text-center">{{ date('d/m/Y', strtotime($loan->mtr_date_dt)) }}</td>
                                            <td class="text-right">{{ number_format($loan->rate*100 ?? 0, 5) }}%</td>
                                            <td class="text-right">{{ number_format($loan->eirex*100 ?? 0, 14) }}%</td>
                                            <td class="text-right">{{ number_format($loan->eircalc*100 ?? 0, 14) }}%</td>
                                            <td class="text-right">{{ number_format($loan->cbal ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($loan->carrying_amount ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($outstandingReceivable ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($loan->bilint ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($unamortCost ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($unamortFee ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($loan->cum_bunga ?? 0) }}</td>
                                    </tr>
                                @endforeach
                                <!-- Row Total / Average -->
                                <tr class="font-weight-normal text-right">
                                    <td colspan="10" class="text-center">TOTAL</td>
                                    <td>{{ number_format($master->avg('rate')*100 ?? 0, 5) }}%</td>
                                    <td>{{ number_format($master->avg('eirex')*100?? 0, 14) }}%</td>
                                    <td>{{ number_format($master->avg('eircalc')*100?? 0, 14) }}%</td>
                                    <td>{{ number_format($master->sum('cbal')?? 0) }}</td>
                                    <td>{{ number_format($master->sum('carrying_amount')?? 0) }}</td>
                                    <td>{{ number_format($totalOutstandingReceivable ?? 0) }}</td>
                                    <td>{{ number_format($master->sum('bilint')?? 0) }}</td>
                                    <td>{{ number_format($totalUnamortCost ?? 0)}}</td>
                                    <td>{{ number_format($totalUnamortFee ?? 0)}}</td>
                                    <td>{{ number_format($totalInterestIncome ?? 0)}}</td>
                                   
                                   
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report Parameters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="mb-3  ">
                        <label for="reportType" class="form-label me-2">Report Type</label>
                        <select class="form-select" id="reportType" required style="width: 100%;">
                            <!-- Options will be populated by JavaScriptt -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="entityNumber" class="form-label">Entity Number</label>
                        <div class="d-flex align-items-center">
                            <input type="number" 
                                   class="form-control" 
                                   id="entityNumber" 
                                   required
                                   @if(!$isSuperAdmin) disabled @endif
                                   value="{{ $user->id_pt ?? '' }}">
                            <span id="entityLabel" class="ms-3 text-muted" style="display: inline-block;"></span>
                        </div>
                        <small id="entityError" class="text-danger" style="display: none;">Data tidak ditemukan</small>
                    </div>
                    <div class="mb-3">
                        <label for="accountNumber" id="accountNumberLabel" class="form-label">Account Number</label>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control" id="accountNumber" required>
                            <span id="accountLabel" class="ms-3 text-muted"></span>
                        </div>
                        <small id="accountError" class="text-danger" style="display: none;">Data tidak ditemukan</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="viewReport()">View Report</button>
            </div>
        </div>
    </div>
</div>


<script>
function showModal(type) {
    const reportTypeSelect = document.getElementById('reportType');
    const accountNumberInput = document.getElementById('accountNumber');
    const accountNumberLabel = document.getElementById('accountNumberLabel');
    reportTypeSelect.innerHTML = ''; 
    
    // Sembunyikan atau tampilkan input "Account Number" berdasarkan tipe laporan
    if (type.includes('outstanding')) {
        accountNumberInput.style.display = 'none';
        accountNumberLabel.style.display = 'none';
        accountNumberInput.removeAttribute('required');
    } else {
        accountNumberInput.style.display = 'block';
        accountNumberLabel.style.display = 'block';
        accountNumberInput.setAttribute('required', 'required');
    }
    
    let options;
    switch(type) {
        case 'accrual_interest_effective':
        case 'accrual_interest_simple':
            options = `
                <option value="accrual_interest_effective">Effective</option>
                <option value="accrual_interest_simple">Simple Interest</option>
            `;
            break;
            
        case 'amortised_cost_effective':
        case 'amortised_cost_simple':
            options = `
                <option value="amortised_cost_effective">Effective</option>
                <option value="amortised_cost_simple">Simple Interest</option>
            `;
            break;
            
        case 'amortised_initial_cost_effective':
        case 'amortised_initial_cost_simple':
            options = `
                <option value="amortised_initial_cost_effective">Effective</option>
                <option value="amortised_initial_cost_simple">Simple Interest</option>
            `;
            break;
            
        case 'amortised_initial_fee_effective':
        case 'amortised_initial_fee_simple':
            options = `
                <option value="amortised_initial_fee_effective">Effective</option>
                <option value="amortised_initial_fee_simple">Simple Interest</option>
            `;
            break;
            
        case 'expected_cashflow_effective':
        case 'expected_cashflow_simple':
            options = `
                <option value="expected_cashflow_effective">Effective</option>
                <option value="expected_cashflow_simple">Simple Interest</option>
            `;
            break;
            
        // case 'initial_recognition_effective':
        // case 'initial_recognition_simple':
        //     options = `
        //         <option value="initial_recognition_effective">Effective</option>
        //         <option value="initial_recognition_simple">Simple Interest</option>
        //     `;
        //     break;
            
        case 'outstanding_effective':
        case 'outstanding_simple':
            options = `
                <option value="outstanding_effective">Effective</option>
                <option value="outstanding_simple">Simple Interest</option>
            `;
            break;
            
        case 'journal_effective':
        case 'journal_simple':
            options = `
                <option value="journal_effective">Effective</option>
                <option value="journal_simple">Simple Interest</option>
            `;
            break;
            
        default:
            console.error('Tipe report tidak valid:', type);
            options = '<option value="">Pilih tipe report</option>';
            break;
    }
    
    reportTypeSelect.innerHTML = options;
    reportTypeSelect.value = type; // Set nilai sesuai tipe yang dipilih
    
    $('#reportModal').modal('show');
}

function showModalWithAccount(accountNumber, type) {
    
    // Bersihkan whitespace dan pastikan tipe data string
    const cleanAccountNumber = accountNumber.toString().trim();
    
    const accountNumberInput = document.getElementById('accountNumber');
    
    // Set nilai dengan timeout untuk memastikan DOM sudah siap
    setTimeout(() => {
        // Set nilai menggunakan setAttribute
        accountNumberInput.setAttribute('value', cleanAccountNumber);
        // Set juga menggunakan property value
        accountNumberInput.value = cleanAccountNumber;
        
        const event = new Event('change', { bubbles: true });
        accountNumberInput.dispatchEvent(event);
        
        // Trigger blur event
        const blurEvent = new Event('blur', { bubbles: true });
        accountNumberInput.dispatchEvent(blurEvent);
    }, 100);

    const reportTypeSelect = document.getElementById('reportType');
    reportTypeSelect.innerHTML = ''; 
    
    let options;
    switch(type) {
        case 'accrual_interest_effective':
        case 'accrual_interest_simple':
            options = `
                <option value="accrual_interest_effective">Effective</option>
                <option value="accrual_interest_simple">Simple Interest</option>
            `;
            break;
            
        case 'amortised_cost_effective':
        case 'amortised_cost_simple':
            options = `
                <option value="amortised_cost_effective">Effective</option>
                <option value="amortised_cost_simple">Simple Interest</option>
            `;
            break;
            
        case 'amortised_initial_cost_effective':
        case 'amortised_initial_cost_simple':
            options = `
                <option value="amortised_initial_cost_effective">Effective</option>
                <option value="amortised_initial_cost_simple">Simple Interest</option>
            `;
            break;
            
        case 'amortised_initial_fee_effective':
        case 'amortised_initial_fee_simple':
            options = `
                <option value="amortised_initial_fee_effective">Effective</option>
                <option value="amortised_initial_fee_simple">Simple Interest</option>
            `;
            break;
            
        case 'expected_cashflow_effective':
        case 'expected_cashflow_simple':
            options = `
                <option value="expected_cashflow_effective">Effective</option>
                <option value="expected_cashflow_simple">Simple Interest</option>
            `;
            break;
            
        case 'initial_recognition_effective':
        case 'initial_recognition_simple':
            options = `
                <option value="initial_recognition_effective">Effective</option>
                <option value="initial_recognition_simple">Simple Interest</option>
            `;
            break;
            
        case 'outstanding_effective':
        case 'outstanding_simple':
            options = `
                <option value="outstanding_effective">Effective</option>
                <option value="outstanding_simple">Simple Interest</option>
            `;
            break;
            
        case 'journal_effective':
        case 'journal_simple':
            options = `
                <option value="journal_effective">Effective</option>
                <option value="journal_simple">Simple Interest</option>
            `;
            break;
    }
    
    reportTypeSelect.innerHTML = options;
    reportTypeSelect.value = type;
    
    
    if (!{{ $isSuperAdmin ? 'true' : 'false' }}) {
        const entityNumber = document.getElementById('entityNumber').value;
        if (entityNumber) {
            fetch(`/check-entity/${entityNumber}`)
                .then(response => response.json())
                .then(data => {
                    const entityLabel = document.getElementById('entityLabel');
                    if (data.success) {
                        entityLabel.innerHTML = data.entity_name;
                        entityLabel.style = `
                            display: inline-block;
                            visibility: visible;
                            margin-left: 10px;
                            padding: 4px 8px;
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                            border-radius: 4px;
                            font-size: 14px;
                        `;
                    }
                });
        }
    }
    $('#reportModal').modal('show');

    setTimeout(() => {
        const accountNumberInput = document.getElementById('accountNumber');
        const entityNumberInput = document.getElementById('entityNumber');
        const event = new Event('blur');
        
        accountNumberInput.dispatchEvent(event);
        entityNumberInput.dispatchEvent(event);
    }, 100);

}


function viewReport() {
    const form = document.getElementById('reportForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const reportType = document.getElementById('reportType').value;
    const entityNumber = document.getElementById('entityNumber').value;
    const accountNumber = document.getElementById('accountNumber').value;

    // Jika tipe laporan adalah "Outstanding", langsung anggap data.success = true
    if (reportType.includes('outstanding')) {
        closeModal();
        redirectToReport(reportType, null, entityNumber);
        return;
    }

    // Tentukan URL pengecekan berdasarkan jenis report
    let checkUrl;
    switch(reportType) {
        case 'accrual_interest_effective':
            checkUrl = `/check-report-accrual-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'accrual_interest_simple':
            checkUrl = `/check-report-accrual-simple/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_cost_effective':
            checkUrl = `/check-report-amortised-cost-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_cost_simple':
            checkUrl = `/check-report-amortised-cost-simple/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_cost_effective':
            checkUrl = `/check-report-amortised-initial-cost-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_cost_simple':
            checkUrl = `/check-report-amortised-initial-cost-simple/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_fee_effective':
            checkUrl = `/check-report-amortised-initial-fee-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_fee_simple':
            checkUrl = `/check-report-amortised-initial-fee-simple/${accountNumber}/${entityNumber}`;
            break;
        case 'expected_cashflow_effective':
            checkUrl = `/check-report-expected-cashflow-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'expected_cashflow_simple':
            checkUrl = `/check-report-expected-cashflow-simple/${accountNumber}/${entityNumber}`;
            break;
        // case 'initial_recognition_effective':
        //     checkUrl = `/check-report-initial-recognition-effective/${accountNumber}/${entityNumber}`;
        //     break;
        // case 'initial_recognition_simple':
        //     checkUrl = `/check-report-initial-recognition-simple/${accountNumber}/${entityNumber}`;
        //     break;
        case 'outstanding_effective':
            checkUrl = `/check-report-outstanding-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'outstanding_simple':
            checkUrl = `/check-report-outstanding-simple/${accountNumber}/${entityNumber}`;
            break;
        case 'journal_effective':
            checkUrl = `/check-report-journal-effective/${accountNumber}/${entityNumber}`;
            break;
        case 'journal_simple':
            checkUrl = `/check-report-journal-simple/${accountNumber}/${entityNumber}`;
            break;
    }

    // Cek ketersediaan data
    fetch(checkUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tutup modal
                closeModal();
                
                // Redirect ke halaman report
                redirectToReport(reportType, accountNumber, entityNumber);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Data tidak ditemukan',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Terjadi kesalahan saat memeriksa data',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

function redirectToReport(reportType, accountNumber, entityNumber) {
    let url;
    switch(reportType) {
        case 'accrual_interest_effective':
            url = `/report-accrual-effective/view/${accountNumber}/${entityNumber}`;
            break;
        case 'accrual_interest_simple':
            url = `/report/accrual-interest/simple-interest/view/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_cost_effective':
            url = `/report-amortised-cost-effective/view/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_cost_simple':
            url = `/report-amortised-cost-simple-interest/view/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_cost_effective':
            url = `/report-amortised-initial-cost-effective/view/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_cost_simple':
            url = `/report-amortised-initial-cost-simple-interest/view/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_fee_effective':
            url = `/report-amortised-initial-fee-effective/view/${accountNumber}/${entityNumber}`;
            break;
        case 'amortised_initial_fee_simple':
            url = `/report-amortised-initial-fee-simple-interest/view/${accountNumber}/${entityNumber}`;
            break;
        case 'expected_cashflow_effective':
            url = `/report-expective-cash-flow-effective/view/${accountNumber}/${entityNumber}`;
            break;
        case 'expected_cashflow_simple':
            url = `/report-expective-cash-flow-simple-interest/view/${accountNumber}/${entityNumber}`;
            break;
        // case 'initial_recognition_effective':
        //     url = `/report-initial-recognition-effective/view/${accountNumber}/${entityNumber}`;
        //     break;
        // case 'initial_recognition_simple':
        //     url = `/report-initial-recognition-simple/view/${accountNumber}/${entityNumber}`;
        //     break;
        case 'outstanding_effective':
            url = `/report-outstanding-effective/view/${entityNumber}`;
            break;
        case 'outstanding_simple':
            url = `/report-outstanding-simple-interest/view/${entityNumber}`;
            break;
        case 'journal_effective':
            url = `/report-journal-effective/view/${accountNumber}/${entityNumber}`;
            break;
        case 'journal_simple':
            url = `/report-journal-simple-interest/view/${accountNumber}/${entityNumber}`;
            break;
    }
    window.location.href = url;
}

// Fungsi untuk menutup modal
function closeModal() {
    $('#reportModal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');
}

// Event listener saat dokumen siap
$(document).ready(function() {
    // Reset modal state saat halaman dimuat
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    });

    // Handle tombol close
    $('[data-bs-dismiss="modal"]').on('click', function(e) {
        e.preventDefault();
        closeModal();
    });

    // Tambahkan event listener untuk modal
    $('#reportModal').on('shown.bs.modal', function () {
        // Re-check entity saat modal ditampilkan
        if (!{{ $isSuperAdmin ? 'true' : 'false' }}) {
            const entityNumber = document.getElementById('entityNumber').value;
            if (entityNumber) {
                fetch(`/check-entity/${entityNumber}`)
                    .then(response => response.json())
                    .then(data => {
                        const entityLabel = document.getElementById('entityLabel');
                        if (data.success) {
                            entityLabel.innerHTML = data.entity_name;
                            entityLabel.style = `
                                display: inline-block;
                                visibility: visible;
                                margin-left: 10px;
                                padding: 4px 8px;
                                background-color: #d4edda;
                                color: #155724;
                                border: 1px solid #c3e6cb;
                                border-radius: 4px;
                                font-size: 14px;
                            `;
                        }
                    });
            }
        }
    });
});


// Event listener untuk document ready
document.addEventListener('DOMContentLoaded', function() {
    const entityInput = document.getElementById('entityNumber');
    const entityLabel = document.getElementById('entityLabel');
    
    const isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};
    
    if (!isSuperAdmin) {
        entityInput.value = '{{ $user->id_pt ?? "" }}';
        entityInput.disabled = true;
        
        const entityNumber = entityInput.value;
        if (entityNumber) {
            fetch(`/check-entity/${entityNumber}`)
                .then(response => response.json())
                .then(data => {
                    const entityLabel = document.getElementById('entityLabel');
                    const entityError = document.getElementById('entityError');
                    
                    if (data.success) {
                        // Reset classes terlebih dahulu
                        entityLabel.className = '';
                        
                        // Set content
                        entityLabel.innerHTML = data.entity_name;
                        
                        // Tambahkan style inline
                        entityLabel.style = `
                            display: inline-block;
                            visibility: visible;
                            margin-left: 10px;
                            padding: 4px 8px;
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                            border-radius: 4px;
                            font-size: 14px;
                        `;
                        
                        entityError.style.display = 'none';
                        
                    } else {
                        // Reset classes
                        entityLabel.className = '';
                        
                        entityLabel.innerHTML = 'Data tidak ditemukan';
                        entityLabel.style = `
                            display: inline-block;
                            visibility: visible;
                            margin-left: 10px;
                            padding: 4px 8px;
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                            border-radius: 4px;
                            font-size: 14px;
                        `;
                        entityError.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    } else {
        entityInput.addEventListener('blur', entityCheckFunction);
    }
});

// Definisikan fungsi check entity
const entityCheckFunction = function() {
    const entityNumber = this.value;
    const entityLabel = document.getElementById('entityLabel');
    const entityError = document.getElementById('entityError');

    if (entityNumber) {
        fetch(`/check-entity/${entityNumber}`)
            .then(response => {
                console.log(response);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    entityLabel.textContent = data.entity_name;
                    entityLabel.classList.remove('alert-danger');
                    entityLabel.classList.add('alert', 'alert-success', 'py-1', 'px-2');
                    entityLabel.style.display = 'inline-block';
                    entityError.style.display = 'none';
                } else {
                    entityLabel.textContent = 'Data tidak ditemukan';
                    entityLabel.classList.remove('alert-success');
                    entityLabel.classList.add('alert', 'alert-danger', 'py-1', 'px-2');
                    entityLabel.style.display = 'inline-block';
                    entityError.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        entityLabel.style.display = 'none';
        entityError.style.display = 'none';
    }
};

// Event listener untuk account number
document.getElementById('accountNumber').addEventListener('blur', function() {
    const accountNumber = this.value;
    const accountLabel = document.getElementById('accountLabel');
    const accountError = document.getElementById('accountError');
    const entityNumber = document.getElementById('entityNumber').value;
    
    if (accountNumber) {
        fetch(`/check-account-corporate/${accountNumber}?entity_number=${entityNumber}`)
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    accountLabel.textContent = data.deb_name;
                    accountLabel.classList.remove('alert-danger');
                    accountLabel.classList.add('alert', 'alert-success', 'py-1', 'px-2');
                    accountLabel.style.display = 'inline-block';
                    accountError.style.display = 'none';
                } else {
                    accountLabel.textContent = 'Data tidak ditemukan';
                    accountLabel.classList.remove('alert-success');
                    accountLabel.classList.add('alert', 'alert-danger', 'py-1', 'px-2');
                    accountLabel.style.display = 'inline-block';
                    accountError.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        accountLabel.style.display = 'none';
        accountError.style.display = 'none';
    }
});


document.addEventListener('DOMContentLoaded', function() {
    // Set nilai default untuk bulan dan tahun dari parameter URL atau data yang dikirim dari controller
    const selectedMonth = "{{ $bulan ?? date('n') }}";
    const selectedYear = "{{ $tahun ?? date('Y') }}";
    
    // Set nilai default untuk select bulan dan input tahun
    document.getElementById('monthSelect').value = selectedMonth;
    document.getElementById('yearInput').value = selectedYear;
});

// Event listener untuk perubahan bulan atau tahun
document.getElementById('monthSelect').addEventListener('change', updateReport);
document.getElementById('yearInput').addEventListener('change', updateReport);

function updateReport() {
    const month = document.getElementById('monthSelect').value;
    const year = document.getElementById('yearInput').value;
    const id_pt = "{{ Auth::user()->id_pt ?? '' }}";
    
    // Sesuaikan dengan route yang benar
    window.location.href = `/report-outstanding-simple-interest/view/${id_pt}?bulan=${month}&tahun=${year}`;
} // Update export URL dynamically based on selected month and year
    document.getElementById('exportExcel').addEventListener('click', function (e) {
        e.preventDefault();
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearInput').value;

        // Redirect to the export route with query parameters
        window.location.href = `{{ route('report-outstanding-si.exportExcel', ['id_pt' => Auth::user()->id_pt]) }}?bulan=${month}&tahun=${year}`;
    });

    document.getElementById('exportPdf').addEventListener('click', function (e) {
        e.preventDefault();
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearInput').value;

        // Redirect to the export route with query parameters
        window.location.href = `{{ route('report-outstanding-si.exportPdf', ['id_pt' => Auth::user()->id_pt]) }}?bulan=${month}&tahun=${year}`;
    });
    document.getElementById('exportCsv').addEventListener('click', function (e) {
        e.preventDefault();
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearInput').value;

        // Redirect to the export route with query parameters
        window.location.href = `{{ route('report-outstanding-si.exportCsv', ['id_pt' => Auth::user()->id_pt]) }}?bulan=${month}&tahun=${year}`;
    });
</script>

<style>
    /* ... style yang sudah ada ... */
    
    .table-secondary {
        background-color: #f2f2f2 !important;
    }

    .font-weight-bold {
        font-weight: normal;
    }

    .text-end {
        text-align: right;
    }

    .text-right {
        text-align: right;
        padding-right: 20px;
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
        color: #212529;
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

    /* Style untuk dropdown menu */
    .dropdown-menu {
        margin: 0;
        padding: 0.5rem 0;
        position: absolute;
        z-index: 1000;
        min-width: 250px;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 4px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        padding: 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #212529 !important;
        font-size: 15px !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    /* Style untuk submenu */
    .dropdown-submenu {
        display: none;
        position: absolute;
        left: 100%;
        top: 0;
        min-width: 200px;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 4px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    }

    /* Perbaikan untuk hover behavior */
    .dropdown-menu li {
        position: relative;
    }

    .dropdown-menu li:hover > .dropdown-submenu {
        display: block;
    }

    .dropdown-menu li:hover > a {
        background-color: #f8f9fa;
    }

    /* Style untuk icon */
    .dropdown-item i.float-end {
        margin-left: 8px;
        font-size: 14px;
    }

    /* Memastikan container tidak overflow */
    .container {
        overflow: visible !important;
    }

    .section {
        overflow: visible !important;
    }

    .dropdown {
        position: relative;
    }

    /* Tambahan untuk memastikan submenu tetap terlihat */
    .dropdown-menu .dropdown-item {
        position: relative;
        padding-right: 30px; /* Ruang untuk icon chevron */
    }

    /* Memastikan submenu muncul di level yang sama */
    .dropdown-menu > li > .dropdown-submenu {
        top: 0;
    }

    /* Style untuk form input dan select */
    .form-control, .form-select {
        padding: 8px 12px;
        font-size: 14px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* Style untuk label */
    .form-label {
        font-weight: normal;
        color: #495057;
        font-size: 14px;
        margin-bottom: 0.5rem;
    }

    /* Style untuk entity dan account label */
    #entityLabel, #accountLabel {
        margin-left: 10px;
        font-size: 14px;
        border-radius: 4px;
        display: inline-block;
        min-width: 150px;
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

    /* Style untuk modal */
    .modal-content {
        border-radius: 6px;
    }

    .modal-header {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-body {
        padding: 1rem;
    }

    .modal-footer {
        padding: 1rem;
        border-top: 1px solid #dee2e6;
    }

    /* Style untuk form groups */
    .mb-3 {
        margin-bottom: 1rem !important;
    }

    /* Style untuk input groups */
    .d-flex.align-items-center {
        gap: 0.5rem;
    }

    /* Style untuk error messages */
    .text-danger {
        font-size: 12px;
        margin-top: 0.25rem;
    }

    /* Style untuk buttons dalam modal */
    .modal-footer .btn {
        padding: 8px 16px;
        font-size: 14px;
        border-radius: 4px;
    }

    .clickable-account {
        cursor: pointer;
        color: #007bff;
        text-decoration: none;
    }

    .clickable-account {
        text-decoration: underline;
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
        color: #212529;
    }
</style>
