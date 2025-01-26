<?php

namespace App\Http\Controllers\report\securities;

use App\Models\report_securities;
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
use Illuminate\Support\Facades\DB;

use Dompdf\Dompdf;
use Dompdf\Options;

class outstandingBalanceTreasuryController extends Controller
{
    // Method untuk menampilkan semua data pinjaman korporat
    public function index(Request $request)
    {
        $user = Auth::user();

        $tahun = $request->input('tahun') ?? date('Y');
        $bulan = $request->input('bulan') ?? date('m');


        // Ambil jumlah item per halaman dari query string, default 10
        $perPage = $request->input('per_page', 1000);
        // Ambil data dengan pagination
        $loans = report_securities::fetchInitialRecognition($user->id_pt, $perPage, $tahun, $bulan);

         // Tambahkan debug untuk loans
        //  dd($loans);
        return view('report.securities.report_outstanding_balance_treasury_bond.master', compact('loans', 'tahun', 'bulan', 'user'));
    }

    // Method untuk menampilkan detail pinjaman berdasarkan nomor akunn
    public function view($no_acc, $id_pt)
    {

        $no_acc = trim($no_acc);
        $loan = report_effective::getLoanDetails($no_acc, $id_pt);
        $master=report_effective::getMasterDataByNoAcc($no_acc, $id_pt);
        $reports = report_effective::getReportsByNoAcc($no_acc, $id_pt);
        if (!$loan) {
            abort(404, 'Loan not found');
        }

        // dd($loan, $master, $reports);

        return view('report.securities.report_outstanding_balance_treasury_bond.view', compact('loan', 'reports','master'));
    }

    public function exportExcel($no_acc, $id_pt)
    {
        // Ambil data loan dan reports
        $loan = report_effective::getLoanDetails(trim($no_acc), trim($id_pt));
        $reports = report_effective::getReportsByNoAcc(trim($no_acc), trim($id_pt));
        $master = report_effective::getMasterDataByNoAcc(trim($no_acc), trim($id_pt));
        $entityName = DB::table('public.tblobaleffective')
        ->join('public.tbl_pt', 'tblobaleffective.id_pt', '=', 'tbl_pt.id_pt')
        ->where('tblobaleffective.no_branch', $id_pt)
        ->select('tbl_pt.nama_pt')
        ->first();

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

        $infoRows = [
            ['Entity Name', ': ' . $entityName->nama_pt],
            ['Account Number', ': ' . $loan->no_acc],
            ['Debitor Name', ': ' . $loan->deb_name],
            ['Original Amount', ': ' . number_format($loan->org_bal, 2)],
            ['Original Loan Date', ': ' . date('d/m/Y', strtotime($loan->org_date))],
            ['Term', ': ' . $loan->term . ' Month'],
            ['Maturity Loan Date', ': ' .  date('d/m/Y', strtotime($loan->mtr_date))],
        ];


        $currentRow = 2;
        foreach ($infoRows as $info) {
            $sheet->setCellValue('A' . $currentRow, $info[0]);
            $sheet->setCellValue('C' . $currentRow, $info[1]);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($currentRow)->setRowHeight(15);
            $currentRow++;
        }

        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A4:B4');
        $sheet->mergeCells('A5:B5');
        $sheet->mergeCells('A6:B6');
        $sheet->mergeCells('A7:B7');
        $sheet->mergeCells('A8:B8');
        $sheet->mergeCells('C2:D2');
        $sheet->mergeCells('C3:D3');
        $sheet->mergeCells('C4:D4');
        $sheet->mergeCells('C5:D5');
        $sheet->mergeCells('C6:D6');
        $sheet->mergeCells('C7:D7');
        $sheet->mergeCells('C8:D8');

        // Menghitung nilai org amount
        $upfrontFee = round(-($loan->org_bal * 0.01), 0);
        $CarryingAmount = $loan->nbal-$master->prov;

         // Misalkan trxcost adalah string dengan simbol mata uang
         $trxcost = $master->trxcost; // Ambil nilai dari database
         // Hapus simbol mata uang dan pemisah ribuan
         $trxcost = preg_replace('/[^\d.]/', '', $trxcost);
         // Konversi ke float
         $trxcostFloat = (float)$trxcost;
        $infoRows = [
        ['UpFront Fee', ': -' . number_format($master->prov, 2)],
        ['Transaction Cost', ': ' . number_format($trxcostFloat ?? 0, 2) ],
        ['Carrying Amount', ': ' . number_format($CarryingAmount, 2)],
        ['EIR Exposure', ': ' . number_format($loan->eirex * 100, 14) . '%'],
        ['EIR Calculated', ': ' . number_format($loan->eircalc * 100, 14) . '%'],
        ['Payment Amount', ': ' . number_format($master->pmtamt, 2)],
        ['Interest Rate', ': ' . number_format($master->rate*100, 5) . '%'],
        //        ['Ibase (Year Base)', ':', '360'],
        ];
        $currentRow = 2;
        foreach ($infoRows as $info) {
            $sheet->setCellValue('G' . $currentRow, $info[0]);
            $sheet->setCellValue('H' . $currentRow, $info[1]);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($currentRow)->setRowHeight(15);
            $currentRow++;
        }

        $sheet->mergeCells('H2:I2');
        $sheet->mergeCells('H3:I3');
        $sheet->mergeCells('H4:I4');
        $sheet->mergeCells('H5:I5');
        $sheet->mergeCells('H6:I6');
        $sheet->mergeCells('H7:I7');
        $sheet->mergeCells('H8:I8');


        // Set judul tabel laporan
        $sheet->setCellValue('A11', 'Outstanding Balance Treasury Bond - Report Details');
        $sheet->mergeCells('A11:I11'); // Menggabungkan sel untuk judul tabel
        $sheet->getStyle('A11')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A11')->getFill()->getStartColor()->setARGB('FF006600'); // Warna latar belakang
        $sheet->getStyle('A11')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Set judul kolom tabel
        $headers = [
            'Month',
            'Payment Date',
            'Payment Amount',
            'Interest Recognition',
            'Interest Payment',
            'Amortised',
            'Carrying Amount',
            'Cumulative Amortized',
            'Unamortized'
        ];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '13', $header);
            $sheet->getStyle($columnIndex . '13')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnIndex . '13')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($columnIndex . '13')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Warna latar belakang header
            $sheet->getStyle($columnIndex . '13')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $columnIndex++;
        }

        // Mengisi data laporan ke dalam tabel
        $row = 14; // Mulai dari baris 14 untuk data laporan
        $cumulativeAmortized = 0; // Inisialisasi variabel kumulatif
        foreach ($reports as $report) {
            $amortized = $report->amortized; // Ambil nilai amortized dari laporan
            $cumulativeAmortized += $amortized; // Tambahkan amortized ke total kumulatif

            // Hitung nilai unamortized
            if ($row == 14) {
            // Untuk baris pertama, gunakan nilai pov negatif
            $unamortized = -$master->prov;
            } else {
            // Untuk baris selanjutnya, hitung unamortized berdasarkan cumulative amortized
            $unamortized = $unamortized + $amortized;
            }

            // Mengisi data ke dalam kolom
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('A' . $row, $report->bulanke ?? 'Data tidak ditemukan');
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B' . $row, isset($report->tglangsuran) ? date('d/m/Y', strtotime($report->tglangsuran)) : 'Belum di-generate');
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('C' . $row, number_format($report->pmtamt ?? 0));
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('D' . $row, number_format($report->bungaeir ?? 0));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('E' . $row, number_format($report->bunga ?? 0));
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('F' . $row, number_format($report->amortized));
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('G' . $row, number_format($report->baleir));
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('H' . $row, number_format($cumulativeAmortized));
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('I' . $row, number_format($unamortized));

             // Mengatur angka menjadi rata kanan
            $sheet->getStyle('C' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++; // Pindah ke baris berikutnya
        }
          //TOTAL AMORTISED COST
          $sheet->setCellValue('A' . $row, "TOTAL");
          $sheet->mergeCells('A' . $row . ':B' . $row); 
          $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('C' . $row, number_format($reports->sum('pmtamt')));
          $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('D' . $row, number_format($reports->sum('bungaeir')));
          $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('E' . $row, number_format($reports->sum('bunga')));
          $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('F' . $row, number_format($reports->sum('amortized')));
          $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('G' . $row, null);
          $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('H' . $row, null);
          $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('I' . $row, null);
  
        //   foreach (range('A', 'I') as $columnID) {
        //       $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //   }

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
        // foreach (range('A', 'I') as $columnID) {
        //     $sheet->getColumnDimension($columnID)->setAutoSize(true);
        // }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);

        // Siapkan nama file
        $filename = "ReportOutstandingBalanceTreasuryBond_$no_acc.xlsx";

        // Buat writer dan simpan file Excel
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'phpspreadsheet');
        $writer->save($temp_file);

        // Kembalikan response Excel
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }



    // Method untuk mengekspor data ke PDF
    public function exportPdf($no_acc, $id_pt)
{
        // Ambil data loan dan reports
        $loan = report_effective::getLoanDetails(trim($no_acc), trim($id_pt));
        $reports = report_effective::getReportsByNoAcc(trim($no_acc), trim($id_pt));
        $master = report_effective::getMasterDataByNoAcc(trim($no_acc), trim($id_pt));
        $entityName = DB::table('public.tblobaleffective')
        ->join('public.tbl_pt', 'tblobaleffective.id_pt', '=', 'tbl_pt.id_pt')
        ->where('tblobaleffective.no_branch', $id_pt)
        ->select('tbl_pt.nama_pt')
        ->first();

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

        $infoRows = [
            ['Entity Name', ': ' . $entityName->nama_pt],
            ['Account Number', ': ' . $loan->no_acc],
            ['Debitor Name', ': ' . $loan->deb_name],
            ['Original Amount', ': ' . number_format($loan->org_bal, 2)],
            ['Original Loan Date', ': ' . date('d/m/Y', strtotime($loan->org_date))],
            ['Term', ': ' . $loan->term . ' Month'],
            ['Maturity Loan Date', ': ' .  date('d/m/Y', strtotime($loan->mtr_date))],
        ];


        $currentRow = 2;
        foreach ($infoRows as $info) {
            $sheet->setCellValue('A' . $currentRow, $info[0]);
            $sheet->setCellValue('C' . $currentRow, $info[1]);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($currentRow)->setRowHeight(15);
            $currentRow++;
        }

        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A4:B4');
        $sheet->mergeCells('A5:B5');
        $sheet->mergeCells('A6:B6');
        $sheet->mergeCells('A7:B7');
        $sheet->mergeCells('A8:B8');
        $sheet->mergeCells('C2:D2');
        $sheet->mergeCells('C3:D3');
        $sheet->mergeCells('C4:D4');
        $sheet->mergeCells('C5:D5');
        $sheet->mergeCells('C6:D6');
        $sheet->mergeCells('C7:D7');
        $sheet->mergeCells('C8:D8');

        // Menghitung nilai org amount
        $upfrontFee = round(-($loan->org_bal * 0.01), 0);
        $CarryingAmount = $loan->nbal-$master->prov;

         // Misalkan trxcost adalah string dengan simbol mata uang
         $trxcost = $master->trxcost; // Ambil nilai dari database
         // Hapus simbol mata uang dan pemisah ribuan
         $trxcost = preg_replace('/[^\d.]/', '', $trxcost);
         // Konversi ke float
         $trxcostFloat = (float)$trxcost;
        $infoRows = [
        ['UpFront Fee', ': -' . number_format($master->prov, 2)],
        ['Transaction Cost', ': ' . number_format($trxcostFloat ?? 0, 2) ],
        ['Carrying Amount', ': ' . number_format($CarryingAmount, 2)],
        ['EIR Exposure', ': ' . number_format($loan->eirex * 100, 14) . '%'],
        ['EIR Calculated', ': ' . number_format($loan->eircalc * 100, 14) . '%'],
        ['Payment Amount', ': ' . number_format($master->pmtamt, 2)],
        ['Interest Rate', ': ' . number_format($master->rate*100, 5) . '%'],
        //        ['Ibase (Year Base)', ':', '360'],
        ];
        $currentRow = 2;
        foreach ($infoRows as $info) {
            $sheet->setCellValue('G' . $currentRow, $info[0]);
            $sheet->setCellValue('H' . $currentRow, $info[1]);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($currentRow)->setRowHeight(15);
            $currentRow++;
        }

        $sheet->mergeCells('H2:I2');
        $sheet->mergeCells('H3:I3');
        $sheet->mergeCells('H4:I4');
        $sheet->mergeCells('H5:I5');
        $sheet->mergeCells('H6:I6');
        $sheet->mergeCells('H7:I7');
        $sheet->mergeCells('H8:I8');


        // Set judul tabel laporan
        $sheet->setCellValue('A11', 'Outstanding Balance Treasury Bond - Report Details');
        $sheet->mergeCells('A11:I11'); // Menggabungkan sel untuk judul tabel
        $sheet->getStyle('A11')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A11')->getFill()->getStartColor()->setARGB('FF006600'); // Warna latar belakang
        $sheet->getStyle('A11')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Set judul kolom tabel
        $headers = [
            'Month',
            'Payment Date',
            'Payment Amount',
            'Interest Recognition',
            'Interest Payment',
            'Amortised',
            'Carrying Amount',
            'Cumulative Amortized',
            'Unamortized'
        ];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '13', $header);
            $sheet->getStyle($columnIndex . '13')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnIndex . '13')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($columnIndex . '13')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Warna latar belakang header
            $sheet->getStyle($columnIndex . '13')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $columnIndex++;
        }

        // Mengisi data laporan ke dalam tabel
        $row = 14; // Mulai dari baris 14 untuk data laporan
        $cumulativeAmortized = 0; // Inisialisasi variabel kumulatif
        foreach ($reports as $report) {
            $amortized = $report->amortized; // Ambil nilai amortized dari laporan
            $cumulativeAmortized += $amortized; // Tambahkan amortized ke total kumulatif

            // Hitung nilai unamortized
            if ($row == 14) {
            // Untuk baris pertama, gunakan nilai pov negatif
            $unamortized = -$master->prov;
            } else {
            // Untuk baris selanjutnya, hitung unamortized berdasarkan cumulative amortized
            $unamortized = $unamortized + $amortized;
            }

            // Mengisi data ke dalam kolom
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('A' . $row, $report->bulanke ?? 'Data tidak ditemukan');
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B' . $row, isset($report->tglangsuran) ? date('d/m/Y', strtotime($report->tglangsuran)) : 'Belum di-generate');
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('C' . $row, number_format($report->pmtamt ?? 0));
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('D' . $row, number_format($report->bungaeir ?? 0));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('E' . $row, number_format($report->bunga ?? 0));
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('F' . $row, number_format($report->amortized));
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('G' . $row, number_format($report->baleir));
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('H' . $row, number_format($cumulativeAmortized));
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('I' . $row, number_format($unamortized));

             // Mengatur angka menjadi rata kanan
            $sheet->getStyle('C' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++; // Pindah ke baris berikutnya
        }
          //TOTAL AMORTISED COST
          $sheet->setCellValue('A' . $row, "TOTAL");
          $sheet->mergeCells('A' . $row . ':B' . $row); 
          $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('C' . $row, number_format($reports->sum('pmtamt')));
          $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('D' . $row, number_format($reports->sum('bungaeir')));
          $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('E' . $row, number_format($reports->sum('bunga')));
          $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('F' . $row, number_format($reports->sum('amortized')));
          $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('G' . $row, null);
          $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('H' . $row, null);
          $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
          $sheet->setCellValue('I' . $row, null);
  
        //   foreach (range('A', 'I') as $columnID) {
        //       $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //   }

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
        // foreach (range('A', 'I') as $columnID) {
        //     $sheet->getColumnDimension($columnID)->setAutoSize(true);
        // }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);

    // Siapkan nama file
    $filename = "ReportOutstandingBalanceTreasuryBond_$no_acc.pdf";

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

        return response()->json(['success' => true]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
}
}
