<?php

namespace App\Http\Controllers\report\Report_Initial_Recognition;

use App\Http\Controllers\Controller;
use App\Models\InitialRecognitionEffective;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

use Illuminate\Support\Collection;

class effectiveController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('https://psak.pramatech.id');
        }

        $isSuperAdmin = $user->role === 'superadmin';


        $branch = $request->input('branch', $user->id_pt);

        if ($branch != $user->id_pt) {
            $branch = $user->id_pt;
        }

        $tahun = $request->input('tahun') ?? date('Y');
        $bulan = $request->input('bulan') ?? date('m');

        // $result1 = InitialRecognitionEffective::getInitialRecognition('999', '2024', '5');;
        // dd($id_pt);

        try {
            $loans = InitialRecognitionEffective::getInitialRecognition($branch, $tahun, $bulan);
            return view('report.initial_recognition.effective.master', compact('loans', 'bulan', 'tahun', 'user', 'isSuperAdmin'));
        } catch (\Exception $e) {
            \Log::error('Error in report-initial-recognition: ' . $e->getMessage(), [
                'branch' => $branch,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'request' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Failed to fetch data: ' . $e->getMessage());
        }
        // dd($loans);
    }

    public function exportExcel(Request $request, $id_pt)
    {
        $user = Auth::user();

        $namaBulan = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));

        $bulanNama = $namaBulan[$bulan];

        $loans = InitialRecognitionEffective::getInitialRecognition($id_pt, $tahun, $bulan);

        if (empty($loans)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang sesuai dengan kriteria yang dipilih',
                'details' => [
                    'branch' => $id_pt,
                    'bulan' => $bulanNama,
                    'tahun' => $tahun
                ]
            ], 404);
        }

        $bulanAngka = $request->input('bulan', date('n'));
        $loanFirst = $loans[0];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        // // Set page orientation and size
        // $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        // $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        // $sheet->getPageMargins()->setTop(0.5);
        // $sheet->getPageMargins()->setRight(0.5);
        // $sheet->getPageMargins()->setLeft(0.5);
        // $sheet->getPageMargins()->setBottom(0.5);

        if (is_array($loans) && count($loans) > 0) {
            $loanFirst = $loans[0];
            // Set informasi pinjaman
            $sheet->setCellValue('A2', 'Entity Number');
            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C2', $loanFirst->no_branch);
            $sheet->setCellValue('A3', 'Entitiy Name');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C3', $loanFirst->jdname);
            $sheet->setCellValue('A4', 'Date Of Report');
            $sheet->getStyle('A4')->getFont()->setBold(true);
            $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C4', $bulanNama . ' - ' . $tahun);
        } else {
            $sheet->setCellValue('C2', 'Tidak ada data');
            $sheet->setCellValue('C3', 'Tidak ada data');
        }
        $sheet->mergeCells('A2:B2'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('A3:B3'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('A4:B4'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('C2:D2'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('C3:D3'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('C4:D4'); // Menggabungkan sel untuk judul tabel

        // Set title
        $sheet->setCellValue('A6', 'REPORT INITIAL RECOGNITION NEW LOAN BY ENTITY - CONTRACTUAL EFFECTIVE');
        $sheet->mergeCells('A6:W6'); // Menggabungkan sel untuk judul tabel
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A6')->getFill()->getStartColor()->setARGB('FF006600'); // Warna latar belakang
        $sheet->getStyle('A6')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Set headers
        $headers = [
            'No.',
            'Entity Number',
            'Account Number',
            'Debitor Name',
            'GL Account',
            'Loan Type',
            'GL Group',
            'Original Date',
            'Term (Months)',
            'Interest Rate',
            'Maturity Date',
            'Payment Amount',
            'Original Balance',
            'Current Balance',
            'Carrying Amount',
            'EIR Amortised Cost Exposure',
            'EIR Amortised Cost Calculated',
            'EIR Calculated Convertion',
            'EIR Calculated Transaction Cost',
            'EIR Calculated UpFront Fee',
            'Outstanding Amount',
            'Outstanding Amount Initial Transaction Cost',
            'Outstanding Amount Initial UpFront Fee'
        ];

        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '8', $header);
            $sheet->getStyle($columnIndex . '8')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnIndex . '8')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($columnIndex . '8')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Warna latar belakang header
            $sheet->getStyle($columnIndex . '8')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $columnIndex++;
        }

        // Add data with styling
        $row = 9;
        $nourut = 0;
        foreach ($loans as $loan) {
            $nourut += 1;
            $sheet->getStyle('A' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('A' . $row, $nourut);
            $sheet->getStyle('B' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B' . $row, $loan->no_branch);
            $sheet->getStyle('C' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('C' . $row, $loan->no_acc);
            $sheet->getStyle('D' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('D' . $row, $loan->deb_name);
            $sheet->getStyle('E' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('E' . $row, $loan->coa);
            $sheet->getStyle('F' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('F' . $row, $loan->ln_type);
            $sheet->getStyle('G' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('G' . $row, $loan->glgroup);
            $sheet->getStyle('H' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('H' . $row, $loan->orgdtconv);
            $sheet->getStyle('I' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('I' . $row, $loan->term);
            $sheet->getStyle('J' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('J' . $row, number_format($loan->rate * 100, 5) . '%');
            $sheet->getStyle('K' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('K' . $row, $loan->mtrdtconv);
            $sheet->getStyle('L' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('L' . $row, number_format($loan->pmtamt));
            $sheet->getStyle('M' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('M' . $row, number_format($loan->org_bal));
            $sheet->getStyle('N' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('N' . $row, number_format($loan->oldbal));
            $sheet->getStyle('O' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('O' . $row, number_format($loan->baleir));
            $sheet->getStyle('P' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('P' . $row, number_format($loan->eirex * 100, 14) . '%');
            $sheet->getStyle('Q' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('Q' . $row, number_format($loan->eircalc * 100, 14) . '%');
            $sheet->getStyle('R' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('R' . $row, number_format($loan->eircalc_conv * 100, 14) . '%');
            $sheet->getStyle('S' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('S' . $row, number_format($loan->eircalc_cost * 100, 14) . '%');
            $sheet->getStyle('T' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('T' . $row, number_format($loan->eircalc_fee * 100, 14) . '%');
            $sheet->getStyle('U' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('U' . $row, number_format($loan->outsamtconv));
            $sheet->getStyle('V' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('V' . $row, number_format($loan->outsamtcost));
            $sheet->getStyle('W' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('W' . $row, number_format($loan->outsamtfee));

            $sheet->getStyle('A' . $row . ':W' . $row)->getFont()->setBold(true);

            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setARGB('FFEFEFEF');
            }
            $row++;
        }
        $loansCollection = new Collection($loans);
        $averageRate = $loansCollection->avg('rate');
        $averageEirex = $loansCollection->avg('eirex');
        $averageEircalc = $loansCollection->avg('eircalc');
        $averageEircalcConv = $loansCollection->avg('eircalc_conv');
        $averageRateEircalcCost = $loansCollection->avg('eircalc_cost');
        $averageEircalcFee = $loansCollection->avg('eircalc_fee');
        $totalPmtamt = $loansCollection->sum('pmtamt');
        $totalOrgBal = $loansCollection->sum('org_bal');
        $totalOldbal = $loansCollection->sum('oldbal');
        $totalOutsamtconv = $loansCollection->sum('outsamtconv');
        $totalOutsamtcost = $loansCollection->sum('outsamtcost');
        $totalOutsamtfee = $loansCollection->sum('outsamtfee');

        $sheet->setCellValue('A' . $row, "TOTAL:");
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('J' . $row, number_format($averageRate * 100, 5) . '%');
        $sheet->getStyle('K' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('K' . $row, null);
        $sheet->getStyle('L' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('L' . $row, number_format($totalPmtamt));
        $sheet->getStyle('M' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('M' . $row, number_format($totalOrgBal));
        $sheet->getStyle('N' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('N' . $row, number_format($totalOldbal));
        $sheet->getStyle('O' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('O' . $row, null);
        $sheet->getStyle('P' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('P' . $row, number_format($averageEirex * 100, 14) . '%');
        $sheet->getStyle('Q' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('Q' . $row, number_format($averageEircalc * 100, 14) . '%');
        $sheet->getStyle('R' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('R' . $row, number_format($averageEircalcConv * 100, 14) . '%');
        $sheet->getStyle('S' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('S' . $row, number_format($averageRateEircalcCost * 100, 14) . '%');
        $sheet->getStyle('T' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('T' . $row, number_format($averageEircalcFee * 100, 14) . '%');
        $sheet->getStyle('U' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('U' . $row, number_format($totalOutsamtconv));
        $sheet->getStyle('V' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('V' . $row, number_format($totalOutsamtcost));
        $sheet->getStyle('W' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('W' . $row, number_format($totalOutsamtfee));



        $sheet->getStyle('A' . $row . ':W' . $row)->getFont()->setBold(true);

        // Menambahkan warna latar belakang alternatif pada baris data

        $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setARGB('FFEFEFEF'); // Warna latar belakang untuk baris


        // Mengatur border untuk tabel
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];

        $sheet->getStyle('A8:W8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A8:W8')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(18);
        $sheet->getColumnDimension('P')->setWidth(24);
        $sheet->getColumnDimension('Q')->setWidth(24);
        $sheet->getColumnDimension('R')->setWidth(24);
        $sheet->getColumnDimension('S')->setWidth(24);
        $sheet->getColumnDimension('T')->setWidth(24);
        $sheet->getColumnDimension('U')->setWidth(18);
        $sheet->getColumnDimension('V')->setWidth(24);
        $sheet->getColumnDimension('W')->setWidth(24);

        // Set border untuk header tabel
        $sheet->getStyle('A6:W6')->applyFromArray($styleArray);

        // Set border untuk semua data laporan
        $sheet->getStyle('A8:W' . $row)->applyFromArray($styleArray);

        // Mengatur lebar kolom agar lebih rapi
        // foreach (range('A', 'W') as $columnID) {
        //     $sheet->getColumnDimension($columnID)->setAutoSize(true);
        // }


        // Set filename
        $filename = "ReportInitialRecognitionEffective_{$id_pt}_{$bulanNama}_{$tahun}.xlsx";

        // Create writer and save Excel file
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'phpspreadsheet');
        $writer->save($temp_file);

        // Return Excel response
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request, $id_pt)
    {
        $user = Auth::user();

        $namaBulan = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));

        $bulanNama = $namaBulan[$bulan];

        $loans = InitialRecognitionEffective::getInitialRecognition($id_pt, $tahun, $bulan);

        if (empty($loans)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang sesuai dengan kriteria yang dipilih',
                'details' => [
                    'branch' => $id_pt,
                    'bulan' => $bulanNama,
                    'tahun' => $tahun
                ]
            ], 404);
        }

        $bulanAngka = $request->input('bulan', date('n'));
        $loanFirst = $loans[0];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        // // Set page orientation and size
        // $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        // $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        // $sheet->getPageMargins()->setTop(0.5);
        // $sheet->getPageMargins()->setRight(0.5);
        // $sheet->getPageMargins()->setLeft(0.5);
        // $sheet->getPageMargins()->setBottom(0.5);

        if (is_array($loans) && count($loans) > 0) {
            $loanFirst = $loans[0];
            // Set informasi pinjaman
            $sheet->setCellValue('A2', 'Entity Number');
            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C2', $loanFirst->no_branch);
            $sheet->setCellValue('A3', 'Entitiy Name');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C3', $loanFirst->jdname);
            $sheet->setCellValue('A4', 'Date Of Report');
            $sheet->getStyle('A4')->getFont()->setBold(true);
            $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C4', $bulanNama . ' - ' . $tahun);
        } else {
            $sheet->setCellValue('C2', 'Tidak ada data');
            $sheet->setCellValue('C3', 'Tidak ada data');
        }
        $sheet->mergeCells('A2:B2'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('A3:B3'); // Menggabungkan sel untuk judul tabel
        $sheet->mergeCells('A4:B4'); // Menggabungkan sel untuk judul tabel

        // Set title
        $sheet->setCellValue('A6', 'REPORT INITIAL RECOGNITION NEW LOAN BY ENTITY - CONTRACTUAL EFFECTIVE');
        $sheet->mergeCells('A6:W6'); // Menggabungkan sel untuk judul tabel
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A6')->getFill()->getStartColor()->setARGB('FF006600'); // Warna latar belakang
        $sheet->getStyle('A6')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Set headers
        $headers = [
            'No.',
            'Entity Number',
            'Account Number',
            'Debitor Name',
            'GL Account',
            'Loan Type',
            'GL Group',
            'Original Date',
            'Term (Months)',
            'Interest Rate',
            'Maturity Date',
            'Payment Amount',
            'Original Balance',
            'Current Balance',
            'Carrying Amount',
            'EIR Amortised Cost Exposure',
            'EIR Amortised Cost Calculated',
            'EIR Calculated Convertion',
            'EIR Calculated Transaction Cost',
            'EIR Calculated UpFront Fee',
            'Outstanding Amount',
            'Outstanding Amount Initial Transaction Cost',
            'Outstanding Amount Initial UpFront Fee'
        ];

        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '8', $header);
            $sheet->getStyle($columnIndex . '8')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($columnIndex . '8')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($columnIndex . '8')->getFill()->getStartColor()->setARGB('FF4F81BD'); // Warna latar belakang header
            $sheet->getStyle($columnIndex . '8')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $columnIndex++;
        }

        // Add data with styling
        $row = 9;
        $nourut = 0;
        foreach ($loans as $loan) {
            $nourut += 1;
            $sheet->getStyle('A' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('A' . $row, $nourut);
            $sheet->getStyle('B' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B' . $row, $loan->no_branch);
            $sheet->getStyle('C' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('C' . $row, $loan->no_acc);
            $sheet->getStyle('D' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('D' . $row, $loan->deb_name);
            $sheet->getStyle('E' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('E' . $row, $loan->coa);
            $sheet->getStyle('F' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('F' . $row, $loan->ln_type);
            $sheet->getStyle('G' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('G' . $row, $loan->glgroup);
            $sheet->getStyle('H' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('H' . $row, $loan->orgdtconv);
            $sheet->getStyle('I' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('I' . $row, $loan->term);
            $sheet->getStyle('J' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('J' . $row, number_format($loan->rate * 100, 5) . '%');
            $sheet->getStyle('K' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('K' . $row, $loan->mtrdtconv);
            $sheet->getStyle('L' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('L' . $row, number_format($loan->pmtamt));
            $sheet->getStyle('M' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('M' . $row, number_format($loan->org_bal));
            $sheet->getStyle('N' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('N' . $row, number_format($loan->oldbal));
            $sheet->getStyle('O' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('O' . $row, number_format($loan->baleir));
            $sheet->getStyle('P' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('P' . $row, number_format($loan->eirex * 100, 14) . '%');
            $sheet->getStyle('Q' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('Q' . $row, number_format($loan->eircalc * 100, 14) . '%');
            $sheet->getStyle('R' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('R' . $row, number_format($loan->eircalc_conv * 100, 14) . '%');
            $sheet->getStyle('S' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('S' . $row, number_format($loan->eircalc_cost * 100, 14) . '%');
            $sheet->getStyle('T' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('T' . $row, number_format($loan->eircalc_fee * 100, 14) . '%');
            $sheet->getStyle('U' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('U' . $row, number_format($loan->outsamtconv));
            $sheet->getStyle('V' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('V' . $row, number_format($loan->outsamtcost));
            $sheet->getStyle('W' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('W' . $row, number_format($loan->outsamtfee));

            $sheet->getStyle('A' . $row . ':W' . $row)->getFont()->setBold(true);

            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setARGB('FFEFEFEF');
            }
            $row++;
        }
        $loansCollection = new Collection($loans);
        $averageRate = $loansCollection->avg('rate');
        $averageEirex = $loansCollection->avg('eirex');
        $averageEircalc = $loansCollection->avg('eircalc');
        $averageEircalcConv = $loansCollection->avg('eircalc_conv');
        $averageRateEircalcCost = $loansCollection->avg('eircalc_cost');
        $averageEircalcFee = $loansCollection->avg('eircalc_fee');
        $totalPmtamt = $loansCollection->sum('pmtamt');
        $totalOrgBal = $loansCollection->sum('org_bal');
        $totalOldbal = $loansCollection->sum('oldbal');
        $totalOutsamtconv = $loansCollection->sum('outsamtconv');
        $totalOutsamtcost = $loansCollection->sum('outsamtcost');
        $totalOutsamtfee = $loansCollection->sum('outsamtfee');

        $sheet->setCellValue('A' . $row, "TOTAL:");
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('J' . $row, number_format($averageRate * 100, 5) . '%');
        $sheet->getStyle('K' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('K' . $row, null);
        $sheet->getStyle('L' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('L' . $row, number_format($totalPmtamt));
        $sheet->getStyle('M' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('M' . $row, number_format($totalOrgBal));
        $sheet->getStyle('N' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('N' . $row, number_format($totalOldbal));
        $sheet->getStyle('O' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('O' . $row, null);
        $sheet->getStyle('P' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('P' . $row, number_format($averageEirex * 100, 14) . '%');
        $sheet->getStyle('Q' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('Q' . $row, number_format($averageEircalc * 100, 14) . '%');
        $sheet->getStyle('R' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('R' . $row, number_format($averageEircalcConv * 100, 14) . '%');
        $sheet->getStyle('S' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('S' . $row, number_format($averageRateEircalcCost * 100, 14) . '%');
        $sheet->getStyle('T' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('T' . $row, number_format($averageEircalcFee * 100, 14) . '%');
        $sheet->getStyle('U' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('U' . $row, number_format($totalOutsamtconv));
        $sheet->getStyle('V' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('V' . $row, number_format($totalOutsamtcost));
        $sheet->getStyle('W' . $row, $nourut)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('W' . $row, number_format($totalOutsamtfee));



        $sheet->getStyle('A' . $row . ':W' . $row)->getFont()->setBold(true);

        // Menambahkan warna latar belakang alternatif pada baris data

        $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':W' . $row)->getFill()->getStartColor()->setARGB('FFEFEFEF'); // Warna latar belakang untuk baris


        // Mengatur border untuk tabel
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];

        $sheet->getStyle('A8:W8')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A8:W8')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(18);
        $sheet->getColumnDimension('P')->setWidth(24);
        $sheet->getColumnDimension('Q')->setWidth(24);
        $sheet->getColumnDimension('R')->setWidth(24);
        $sheet->getColumnDimension('S')->setWidth(24);
        $sheet->getColumnDimension('T')->setWidth(24);
        $sheet->getColumnDimension('U')->setWidth(18);
        $sheet->getColumnDimension('V')->setWidth(24);
        $sheet->getColumnDimension('W')->setWidth(24);

        // Set border untuk header tabel
        $sheet->getStyle('A6:W6')->applyFromArray($styleArray);

        // Set border untuk semua data laporan
        $sheet->getStyle('A8:W' . $row)->applyFromArray($styleArray);

        // // Mengatur lebar kolom agar lebih rapi
        // foreach (range('A', 'W') as $columnID) {
        //     $sheet->getColumnDimension($columnID)->setAutoSize(true);
        // }

        // Set pengaturan untuk PDF
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);

        // Siapkan direktori untuk menyimpan file sementara
        $temp_file = tempnam(sys_get_temp_dir(), 'phpspreadsheet_pdf');

        // Simpan file PDF
        $writer->save($temp_file);

        $filename = "ReportInitialRecognitionEffective_{$id_pt}_{$bulanNama}_{$tahun}.pdf";

        // Kembalikan response PDF
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }
}
