<?php

namespace App\Http\Controllers\report\Report_Amortised_Initial_Fee;

use App\Models\report_effective;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;


use Dompdf\Dompdf;
use Dompdf\Options;

class effectiveController extends Controller
{
    // Method untuk menampilkan semua data pinjaman korporat
    public function index(Request $request)
    {
        $id_pt = Auth::user()->id_pt;
           // Ambil jumlah item per halaman dari query string, default 10
           $perPage = $request->input('per_page', 10);
           // Ambil data dengan pagination
           $loans = report_effective::fetchAll($id_pt, $perPage);

        return view('report.amortised_initial_fee.effective.master', compact('loans'));
    }

    // Method untuk menampilkan detail pinjaman berdasarkan nomor akun
    public function view($no_acc,$id_pt)
    {
        $no_acc = trim($no_acc);
        $loan = report_effective::getLoanDetails($no_acc,$id_pt);
        $master=report_effective::getMasterDataByNoAcc($no_acc,$id_pt);
        $reports = report_effective::getReportsByNoAcc($no_acc,$id_pt);

        if (!$loan) {
            abort(404, 'Loan not found');
        }


        return view('report.amortised_initial_fee.effective.view', compact('loan', 'reports','master'));
    }

    public function exportExcel($no_acc, $id_pt)
    {
        // Ambil data loan dan reports
        $loan = report_effective::getLoanDetails(trim($no_acc), trim($id_pt));
        $reports = report_effective::getReportsByNoAcc(trim($no_acc), trim($id_pt));
        $master=report_effective::getMasterDataByNoAcc($no_acc,$id_pt);

        // Cek apakah data loan dan reports ada
        if (!$loan || $reports->isEmpty()) {
            return response()->json(['message' => 'No data found for the given account number.'], 404);
        }

        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
// Set informasi pinjaman
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.5);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.5);

$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(5);
$sheet->getColumnDimension('C')->setWidth(30);
$entityName = "PT PRAMATECH";
$infoRows = [
    ['Account Number', ':', "'" . $loan->no_acc],
    ['Debitor Name', ':', $loan->deb_name],
    ['Original Amount', ':', number_format($loan->org_bal, 2)],
    ['Original Loan Date', ':', date('d/m/Y', strtotime($loan->org_date))],
    ['Maturity Loan Date', ':',  date('d/m/Y', strtotime($loan->mtr_date))],
    ['Payment Amount', ':', number_format($master->pmtamt, 2)],
];


$currentRow = 3;
foreach ($infoRows as $info) {
    $sheet->setCellValue('A' . $currentRow, $info[0]);
    $sheet->setCellValue('B' . $currentRow, $info[1]);
    $sheet->setCellValue('C' . $currentRow, $info[2]);
    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension($currentRow)->setRowHeight(25);
    $currentRow++;
}
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(5);
$sheet->getColumnDimension('I')->setWidth(30);

  // Misalkan trxcost adalah string dengan simbol mata uang
  $prov = $master->prov; // Ambil nilai dari database
  // Hapus simbol mata uang dan pemisah ribuan
  $prov = preg_replace('/[^\d.]/', '', $prov);
  // Konversi ke float
  $provFloat = (float)$prov* -1;
  $org_bal = $loan->org_bal;
  $outinitfee = $org_bal+$provFloat;

$infoRows = [
['UpFront Fee', ':', number_format($master->prov, 2)],
['Outstanding Initial Fee', ':', number_format($outinitfee ?? 0, 2) ],
['EIR Fee Calculated', ':', number_format($loan->eircalc_fee * 100, 14) . '%'],
['Term', ':', $master->term . ' Month'],
['Interest Rate', ':', number_format($master->rate * 100, 5) . '%'],
];
$currentRow = 3;
foreach ($infoRows as $info) {
    $sheet->setCellValue('G' . $currentRow, $info[0]);
    $sheet->setCellValue('H' . $currentRow, $info[1]);
    $sheet->setCellValue('I' . $currentRow, $info[2]);
    $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('I' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension($currentRow)->setRowHeight(25);
    $currentRow++;
}

        // Set judul tabel laporan
        $sheet->setCellValue('A10', 'Amortised Initial Fee Effective Report - Report Details');
        $sheet->mergeCells('A10:I10'); // Menggabungkan sel untuk judul tabel
        $sheet->getStyle('A10')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A10')->getFill()->getStartColor()->setARGB('FF006600'); // Warna latar belakang
        $sheet->getStyle('A10')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Set judul kolom tabel
        $headers = ['Month','Payment Date','Payment Amount','Effective Interest Base On Effective Yield','Accrued Interest','Amortised UpFront Fee','Outstanding Amount Initial UpFront Fee','Cummulative Amortized UpFront Fee','Unamortized UpFront Fee'];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '12', $header);
            $sheet->getStyle($columnIndex . '12')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnIndex . '12')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($columnIndex . '12')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Warna latar belakang header
            $sheet->getStyle($columnIndex . '12')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $columnIndex++;
        }
    $row = 13;  
    $cumulativeAmortized = 0; // Inisialisasi variabel kumulatif
    $totalPaymentAmount = 0;
    $totalEffectiveInterest = 0;
    $totalAccruedInterest = 0;
    $totalAmortisedUpFrontFee = 0;
    $totalOutstandingAmountInitialUpFrontFee = 0;
    $totalCumulativeAmortizedUpFrontFee = 0;  

    foreach ($reports as $report) {

    $amortisefee = $report->amortisefee;
    $cumulativeAmortized += $amortisefee;
    $unamortprovFloat = $provFloat;

    // Hitung nilai unamortized
    if ($row == 13) {
        $unamort = $unamortprovFloat;
    } else {
        $unamort = $unamort + $amortisefee;
    }

    // Hitung total untuk setiap kolom
    $totalPaymentAmount += $report->pmtamt ?? 0;
    $totalEffectiveInterest += $report->bunga ?? 0;
    $totalAccruedInterest += $report->accrfee ?? 0;
    $totalAmortisedUpFrontFee += $report->amortisefee ?? 0;
    $totalOutstandingAmountInitialUpFrontFee += $report->outsamtfee ?? 0;
    $totalCumulativeAmortizedUpFrontFee += $cumulativeAmortized;

       // Mengisi data ke dalam kolom
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('A' . $row, $report->bulanke ?? 'Data tidak ditemukan');
    $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('B' . $row, isset($report->tglangsuran) ? date('d/m/Y', strtotime($report->tglangsuran)) : 'Belum di-generate');
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('C' . $row, number_format($report->pmtamt ?? 0));
    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('D' . $row, number_format($report->bunga ?? 0));
    $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('E' . $row, number_format($report->accrfee ?? 0)); // Sama dengan kolom D
    $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('F' . $row, number_format($amortisefee ?? 0));
    $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('G' . $row, number_format($report->outsamtfee ?? 0));
    $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('H' . $row, number_format($cumulativeAmortized));
    $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('I' . $row, number_format($unamort ?? 0));


            // Menambahkan warna latar belakang alternatif pada baris data
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->getStartColor()->setARGB('FFEFEFEF'); // Warna latar belakang untuk baris genap
            }

            $row++;
        }
        //TOTAL AMORTISED INITIAL FEE
      $sheet->setCellValue('A' . $row, "TOTAL");
      $sheet->mergeCells('A' . $row . ':B' . $row); 
      $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('C' . $row, number_format($totalPaymentAmount));
      $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('D' . $row, number_format($totalEffectiveInterest));
      $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('E' . $row, number_format($totalAccruedInterest));
      $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('F' . $row, number_format($totalAmortisedUpFrontFee));
      $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('G' . $row, null);
      $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('H' . $row, null);
      $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('I' . $row, null);

      foreach (range('A', 'I') as $columnID) {
          $sheet->getColumnDimension($columnID)->setAutoSize(true);
      }

        // Mengatur border untuk tabel
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];

        // Set border untuk header tabel
        $sheet->getStyle('A12:I12')->applyFromArray($styleArray);

        // Set border untuk semua data laporan
        $sheet->getStyle('A13:I' . $row)->applyFromArray($styleArray);

        // Mengatur lebar kolom agar lebih rapi
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Siapkan nama file
        $filename = "ReportAmortisedInitialFeeEffective_$no_acc.xlsx";

        // Buat writer dan simpan file Excel
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'phpspreadsheet');
        $writer->save($temp_file);

        // Kembalikan response Excel
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }



    // Method untuk mengekspor data ke PDF
    public function exportPdf($no_acc,$id_pt)
{
    // Ambil data loan dan reports
    $loan = report_effective::getLoanDetails(trim($no_acc), trim($id_pt));
    $reports = report_effective::getReportsByNoAcc(trim($no_acc), trim($id_pt));
    $master=report_effective::getMasterDataByNoAcc(trim($no_acc), trim($id_pt));

    // Cek apakah data loan dan reports ada
    if (!$loan || $reports->isEmpty()) {
        return response()->json(['message' => 'No data found for the given account number.'], 404);
    }

    // Buat spreadsheet baru
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

    // Set informasi pinjaman
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.5);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.5);

$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(5);
$sheet->getColumnDimension('C')->setWidth(30);
$entityName = "PT PRAMATECH";
$infoRows = [
    ['Account Number', ':', "'" . $loan->no_acc],
    ['Debitor Name', ':', $loan->deb_name],
    ['Original Amount', ':', number_format($loan->org_bal, 2)],
    ['Original Loan Date', ':', date('d/m/Y', strtotime($loan->org_date))],
    ['Maturity Loan Date', ':',  date('d/m/Y', strtotime($loan->mtr_date))],
    ['Payment Amount', ':', number_format($master->pmtamt, 2)],
];


$currentRow = 3;
foreach ($infoRows as $info) {
    $sheet->setCellValue('A' . $currentRow, $info[0]);
    $sheet->setCellValue('B' . $currentRow, $info[1]);
    $sheet->setCellValue('C' . $currentRow, $info[2]);
    $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension($currentRow)->setRowHeight(25);
    $currentRow++;
}
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(5);
$sheet->getColumnDimension('I')->setWidth(30);

  // Misalkan trxcost adalah string dengan simbol mata uang
  $prov = $master->prov; // Ambil nilai dari database
  // Hapus simbol mata uang dan pemisah ribuan
  $prov = preg_replace('/[^\d.]/', '', $prov);
  // Konversi ke float
  $provFloat = (float)$prov* -1;
  $org_bal = $loan->org_bal;
  $outinitfee = $org_bal+$provFloat;

$infoRows = [
['UpFront Fee', ':', number_format($master->prov, 2)],
['Outstanding Initial Fee', ':', number_format($outinitfee ?? 0, 2) ],
['EIR Fee Calculated', ':', number_format($loan->eircalc_fee * 100, 14) . '%'],
['Term', ':', $master->term . ' Month'],
['Interest Rate', ':', number_format($master->rate * 100, 5) . '%'],
];
$currentRow = 3;
foreach ($infoRows as $info) {
    $sheet->setCellValue('G' . $currentRow, $info[0]);
    $sheet->setCellValue('H' . $currentRow, $info[1]);
    $sheet->setCellValue('I' . $currentRow, $info[2]);
    $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('I' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension($currentRow)->setRowHeight(25);
    $currentRow++;
}

        // Set judul tabel laporan
        $sheet->setCellValue('A10', 'Amortised Initial Fee Effective Report - Report Details');
        $sheet->mergeCells('A10:I10'); // Menggabungkan sel untuk judul tabel
        $sheet->getStyle('A10')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A10')->getFill()->getStartColor()->setARGB('FF006600'); // Warna latar belakang
        $sheet->getStyle('A10')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Set judul kolom tabel
        $headers = ['Month','Payment Date','Payment Amount','Effective Interest Base On Effective Yield','Accrued Interest','Amortised UpFront Fee','Outstanding Amount Initial UpFront Fee','Cummulative Amortized UpFront Fee','Unamortized UpFront Fee'];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '12', $header);
            $sheet->getStyle($columnIndex . '12')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnIndex . '12')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($columnIndex . '12')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Warna latar belakang header
            $sheet->getStyle($columnIndex . '12')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $columnIndex++;
        }
    $row = 13;  
    $cumulativeAmortized = 0; // Inisialisasi variabel kumulatif
    $totalPaymentAmount = 0;
    $totalEffectiveInterest = 0;
    $totalAccruedInterest = 0;
    $totalAmortisedUpFrontFee = 0;
    $totalOutstandingAmountInitialUpFrontFee = 0;
    $totalCumulativeAmortizedUpFrontFee = 0;  

    foreach ($reports as $report) {

    $amortisefee = $report->amortisefee;
    $cumulativeAmortized += $amortisefee;
    $unamortprovFloat = $provFloat;

    // Hitung nilai unamortized
    if ($row == 13) {
        $unamort = $unamortprovFloat;
    } else {
        $unamort = $unamort + $amortisefee;
    }

    // Hitung total untuk setiap kolom
    $totalPaymentAmount += $report->pmtamt ?? 0;
    $totalEffectiveInterest += $report->bunga ?? 0;
    $totalAccruedInterest += $report->accrfee ?? 0;
    $totalAmortisedUpFrontFee += $report->amortisefee ?? 0;
    $totalOutstandingAmountInitialUpFrontFee += $report->outsamtfee ?? 0;
    $totalCumulativeAmortizedUpFrontFee += $cumulativeAmortized;

       // Mengisi data ke dalam kolom
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('A' . $row, $report->bulanke ?? 'Data tidak ditemukan');
    $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('B' . $row, isset($report->tglangsuran) ? date('d/m/Y', strtotime($report->tglangsuran)) : 'Belum di-generate');
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('C' . $row, number_format($report->pmtamt ?? 0));
    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('D' . $row, number_format($report->bunga ?? 0));
    $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('E' . $row, number_format($report->accrfee ?? 0)); // Sama dengan kolom D
    $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('F' . $row, number_format($amortisefee ?? 0));
    $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('G' . $row, number_format($report->outsamtfee ?? 0));
    $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('H' . $row, number_format($cumulativeAmortized));
    $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('I' . $row, number_format($unamort ?? 0));


            // Menambahkan warna latar belakang alternatif pada baris data
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->getStartColor()->setARGB('FFEFEFEF'); // Warna latar belakang untuk baris genap
            }

            $row++;
        }
        //TOTAL AMORTISED INITIAL COST
      $sheet->setCellValue('A' . $row, "TOTAL");
      $sheet->mergeCells('A' . $row . ':B' . $row); 
      $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('C' . $row, number_format($totalPaymentAmount));
      $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('D' . $row, number_format($totalEffectiveInterest));
      $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('E' . $row, number_format($totalAccruedInterest));
      $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('F' . $row, number_format($totalAmortisedUpFrontFee));
      $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('G' . $row, null);
      $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('H' . $row, null);
      $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $sheet->setCellValue('I' . $row, null);

      foreach (range('A', 'I') as $columnID) {
          $sheet->getColumnDimension($columnID)->setAutoSize(true);
      }


    // Mengatur border untuk tabel
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => Color::COLOR_BLACK],
            ],
        ],
    ];

    // Set border untuk header tabel
    $sheet->getStyle('A12:I12')->applyFromArray($styleArray);

    // Set border untuk semua data laporan
    $sheet->getStyle('A13:I' . $row)->applyFromArray($styleArray);

    // Mengatur lebar kolom agar lebih rapi
    foreach (range('A', 'I') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Siapkan nama file
    $filename = "ReportAmortisedInitialFeeEffective_$no_acc.pdf";

    // Set pengaturan untuk PDF
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);

    // Siapkan direktori untuk menyimpan file sementara
    $temp_file = tempnam(sys_get_temp_dir(), 'phpspreadsheet_pdf');

    // Simpan file PDF
    $writer->save($temp_file);

    // Kembalikan response PDF
    return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
}

public function checkData($no_acc, $id_pt)
{
    try {
        $no_acc = trim($no_acc);
        
        $loan = report_effective::getLoanDetails($no_acc, $id_pt);
        $master = report_effective::getMasterDataByNoAcc($no_acc, $id_pt);
        $reports = report_effective::getReportsByNoAcc($no_acc, $id_pt);

        if (!$loan) {
            return response()->json(['success' => false, 'message' => 'Data loan tidak ditemukan']);
        }
        if (!$master) {
            return response()->json(['success' => false, 'message' => 'Data master tidak ditemukan']);
        }
        if ($reports->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Data laporan tidak ditemukan']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data' => [
                'loan' => $loan,
                'master' => $master,
                'reports_count' => $reports->count()
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
}
}
