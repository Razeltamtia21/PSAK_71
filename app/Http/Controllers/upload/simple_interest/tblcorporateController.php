<?php

namespace App\Http\Controllers\upload\simple_interest;

use App\Models\UploadSimpleInterest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class tblcorporateController extends Controller
{
    protected $homeModel;

    public function __construct(UploadSimpleInterest $homeModel)
    {
        $this->homeModel = $homeModel;
    }

    public function index()
    {
        $user = Auth::user();
        $id_pt = $user->id_pt;

        $data['title'] = 'Laravel 11 - PHPSpreadsheet';
        $data['tblcorporateloancabangdetail'] = $this->homeModel->fetchTblCorporateLoanCabangDetail($id_pt);
        return view('upload.simple_interest.layouts.appcorporate', $data);
        dd($data);
    }

    private function validateData($data) {
        \Log::info('Memulai validasi data:', ['data' => $data]);
        
        // Cek nilai-nilai pentingg
        $criticalFields = ['id_ktr_cabang', 'cif_bank', 'no_rekening'];
        foreach ($criticalFields as $field) {
            if (empty($data[$field])) {
                \Log::error("Field kritis '$field' kosong", [
                    'nilai' => $data[$field] ?? 'null'
                ]);
                return false;
            }
        }

        // Validasi format tanggal
        $dateFields = ['tanggal_realisasi', 'tgl_jatuh_tempo', 'tgl_transaksi', 'cutoff_date', 'tgl_restruct', 'tgl_restruct_review'];
        foreach ($dateFields as $field) {
            if (!empty($data[$field]) && !strtotime($data[$field])) {
                \Log::error("Format tanggal tidak valid untuk field '$field'", [
                    'nilai' => $data[$field]
                ]);
                return false;
            }
        }

        \Log::info('Validasi berhasil');
        return true;
    }

    public function importExcel(Request $request)
    {
        try {
            if (!$request->hasFile('uploadFile')) {
                throw new \Exception('File tidak ditemukan');
            }

            $file = $request->file('uploadFile');
            \Log::info('File diterima:', [
                'nama_file' => $file->getClientOriginalName(),
                'tipe_file' => $file->getMimeType(),
                'ukuran' => $file->getSize()
            ]);

            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray();
            
            \Log::info('Total baris yang dibaca: ' . count($rows));
            \Log::info('Header:', ['data' => $rows[0]]);
            
            array_shift($rows); // Skip header

            $successCount = 0;
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) {
                    \Log::info('Baris kosong dilewati pada indeks ' . $index);
                    continue;
                }

                DB::beginTransaction();
                try {
                    // Debug raw data
                    \Log::info('Data mentah baris ' . ($index + 1) . ':', $row);
                    
                    $data = $this->prepareData($row);
                    if (!$this->validateData($data)) {
                        \Log::warning('Validasi gagal untuk baris ' . ($index + 1), [
                            'data' => $data
                        ]);
                        DB::rollBack();
                        continue;
                    }

                    // Debug prepared data before insert
                    \Log::info('Data siap untuk insert pada baris ' . ($index + 1) . ':', $data);

                    DB::table('tblcorporateloancabangdetail')->insert($data);
                    DB::commit();
                    $successCount++;
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error pada baris ' . ($index + 1) . ': ' . $e->getMessage(), [
                        'data_mentah' => $row,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if ($successCount > 0) {
                return redirect()->back()->with('success', "Berhasil import $successCount data");
            }

            throw new \Exception('Tidak ada data yang berhasil diimport');
        } catch (\Exception $e) {
            \Log::error('Import gagal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $data = $this->homeModel->fetchTblCorporateLoanCabangDetail();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Excel header
        $headers = [
            'IDTRX', 'ID_KTR_CABANG', 'CIF_BANK', 'NO_REKENING', 'STATUS',
            'NAMA_DEBITUR', 'MAKSIMAL_KREDIT', 'TANGGAL_REALISASI', 'SUKU_BUNGA',
            'JANGKA_WAKTU', 'TGL_JATUH_TEMPO', 'SIFAT_KREDIT', 'JENIS_KREDIT',
            'JNS_TRANSAKSI', 'TGL_TRANSAKSI', 'NILAI_PENARIKAN', 'NILAI_PENGEMBALIAN',
            'CBAL', 'CUTOFF_DATE', 'KELONGGARAN_TARIK', 'TGL_RESTRUCT',
            'TGL_RESTRUCT_REVIEW', 'KET_RESTRUCT', 'NOMINAL_ANGSURAN', 'STATUS_PSAK'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Set Excel data
        $rowNumber = 2;
        foreach ($data as $row) {
            $sheet->fromArray(array_values((array)$row), null, 'A' . $rowNumber);
            $rowNumber++;
        }

        // Set response headers for Excel download
        $filename = 'Table-CorporateLoanCabangDetail-report.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportPdf()
    {
        $data = $this->homeModel->fetchTblCorporateLoanCabangDetail();

        // Start HTML content for PDF
        $html = '<h3>Table Corporate Loan Cabang Detail Report</h3>';
        $html .= '<table border="1" width="100%" style="border-collapse: collapse;">';
        $html .= '
            <thead>
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
            <tbody>';

        foreach ($data as $row) {
            $html .= '
                <tr>
                    <td>' . $row->IDTRX . '</td>
                    <td>' . $row->ID_KTR_CABANG . '</td>
                    <td>' . $row->CIF_BANK . '</td>
                    <td>' . $row->NO_REKENING . '</td>
                    <td>' . $row->STATUS . '</td>
                    <td>' . $row->NAMA_DEBITUR . '</td>
                    <td>' . $row->MAKSIMAL_KREDIT . '</td>
                    <td>' . $row->TANGGAL_REALISASI . '</td>
                    <td>' . $row->SUKU_BUNGA . '</td>
                    <td>' . $row->JANGKA_WAKTU . '</td>
                    <td>' . $row->TGL_JATUH_TEMPO . '</td>
                    <td>' . $row->SIFAT_KREDIT . '</td>
                    <td>' . $row->JENIS_KREDIT . '</td>
                    <td>' . $row->JNS_TRANSAKSI . '</td>
                    <td>' . $row->TGL_TRANSAKSI . '</td>
                    <td>' . $row->NILAI_PENARIKAN . '</td>
                    <td>' . $row->NILAI_PENGEMBALIAN . '</td>
                    <td>' . $row->CBAL . '</td>
                    <td>' . $row->CUTOFF_DATE . '</td>
                    <td>' . $row->KELONGGARAN_TARIK . '</td>
                    <td>' . $row->TGL_RESTRUCT . '</td>
                    <td>' . $row->TGL_RESTRUCT_REVIEW . '</td>
                    <td>' . $row->KET_RESTRUCT . '</td>
                    <td>' . $row->NOMINAL_ANGSURAN . '</td>
                    <td>' . $row->STATUS_PSAK . '</td>
                </tr>';
        }

        $html .= '</tbody></table>';

        // Initialize Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Load HTML into Dompdf
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the PDF
        $dompdf->render();

        // Output the generated PDF to the browser
        $dompdf->stream("Table-CorporateLoanCabangDetail-report.pdf", ["Attachment" => true]);
        exit;
    }

    public function executeStoredProcedure(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
            // 'no_acc' => 'required|string',
            'pilihan' => 'required|integer'
        ]);

        // Ambil input
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $no_acc = $request->no_acc;
        $pilihan = $request->pilihan;

        $user = Auth::user();
        $id_pt = $user->id_pt;

        try {
            // Eksekusi stored procedure
            DB::statement('CALL public.ndcashflowcorporateloan_simpleinterest_final(?, ?, ?, ?)', [$bulan, $tahun, $pilihan, $id_pt]);

            return redirect()->back()->with('success', 'Stored procedure executed successfully.');
        } catch (QueryException $e) {
            // Memeriksa apakah kesalahan berkaitan dengan no_acc tidak ditemukan
            if (strpos($e->getMessage(), 'no_acc') !== false) {
                return redirect()->back()->with('error', "Nomor rekening {$no_acc} tidak ditemukan untuk bulan dan tahun yang ditentukan.");
            }

            // Mengembalikan kesalahan lainnya
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengeksekusi prosedur: ' . $e->getMessage());
        }
    }

    public function clear()
    {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $id_pt = $user->id_pt;
            
            if (!$id_pt) {
                Log::warning('ID PT tidak ditemukan untuk user:', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'ID PT tidak ditemukan');
            }

            Log::info('Attempting to clear corporate data for PT ID: ' . $id_pt);
            
            $deleted = DB::table('tblcorporateloancabangdetail')
                ->where('id_pt', $id_pt)
                ->delete();
            
            if ($deleted > 0) {
                DB::commit();
                Log::info('Successfully deleted ' . $deleted . ' corporate records');
                return redirect()->back()->with('success', 'Berhasil menghapus ' . $deleted . ' data');
            }
            
            DB::rollBack();
            Log::warning('No corporate data found to delete for PT ID: ' . $id_pt);
            return redirect()->back()->with('error', 'Tidak ada data yang dapat dihapus untuk PT ini');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear corporate data error: ' . $e->getMessage(), [
                'id_pt' => $id_pt ?? null,
                'error' => $e
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function convertDate($dateString) {
        try {
            // Jika string kosong atau null
            if (empty($dateString)) {
                return null;
            }
            
            // Hapus spasi di awal dan akhir string
            $dateString = trim($dateString);
            
            // Debug: cek format tanggal yang masuk
            \Log::info('Date string received: ' . $dateString);
            
            // Coba konversi string ke timestamp
            if (strtotime($dateString) !== false) {
                $timestamp = strtotime($dateString);
                // Konversi ke format Excel timestamp
                $excelTimestamp = Date::PHPToExcel($timestamp);
                return Date::excelToDateTimeObject($excelTimestamp);
            }
            
            // Jika masih gagal, return null
            return null;
            
        } catch (Exception $e) {
            \Log::error('Error converting date: ' . $e->getMessage());
            return null;
        }
    }

    private function cleanValue($value) {
        if (empty($value) || $value == 'NULL') {
            return null;
        }
        return trim($value);
    }

    private function formatDate($value) {
        if (empty($value) || $value == 'NULL') {
            return null;
        }

        try {
            // Coba parse tanggal dengan format yang diharapkan
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if ($date) {
                // return $date->format('Y-m-d H:i:s');
                return $date->format('Y-m-d');
            }
            
            // Jika format pertama gagal, coba format lain
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            if ($date) {
                // return $date->format('Y-m-d H:i:s');
                return $date->format('Y-m-d H:i:s');
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error format tanggal: ' . $e->getMessage(), [
                'input' => $value
            ]);
            return null;
        }
    }

    private function prepareData($row)
    {
        $user = Auth::user();
        $id_pt = $user->id_pt;

        try {
            $data = [
                'id_ktr_cabang' => strval($this->cleanValue($row[1])), // pastikan string
                'cif_bank' => strval($this->cleanValue($row[2])),
                'no_rekening' => strval($this->cleanValue($row[3])),
                'status' => intval($this->cleanValue($row[4])), // pastikan integer
                'nama_debitur' => strval($this->cleanValue($row[5])),
                'maksimal_kredit' => floatval($this->cleanNumber($row[6]) ?? 0),
                'tanggal_realisasi' => $this->formatDate($row[7]),
                'suku_bunga' => floatval($this->cleanNumber($row[8]) ?? 0),
                'jangka_waktu' => intval($this->cleanValue($row[9]) ?? 0),
                'tgl_jatuh_tempo' => $this->formatDate($row[10]),
                'sifat_kredit' => strval($this->cleanValue($row[11])),
                'jenis_kredit' => strval($this->cleanValue($row[12])),
                'jns_transaksi' => intval($this->cleanValue($row[13]) ?? 0),
                'tgl_transaksi' => $this->formatDate($row[14]),
                'nilai_penarikan' => floatval($this->cleanNumber($row[15]) ?? 0),
                'nilai_pengembalian' => floatval($this->cleanNumber($row[16]) ?? 0),
                'cbal' => floatval($this->cleanNumber($row[17]) ?? 0),
                'cutoff_date' => $this->formatDate($row[18]),
                'kelonggaran_tarik' => floatval($this->cleanNumber($row[19]) ?? 0),
                'tgl_restruct' => $this->formatDate($row[20]),
                'tgl_restruct_review' => $this->formatDate($row[21]),
                'ket_restruct' => strval($this->cleanValue($row[22])),
                'nominal_angsuran' => floatval($this->cleanNumber($row[23]) ?? 0),
                'status_psak' => intval($this->cleanValue($row[24]) ?? 0),
                'trial014' => null, // set null sesuai permintaan
                'id_pt' => $id_pt, // gunakan id_pt dari user yang login
            ];

            return $data;
        } catch (\Exception $e) {
            \Log::error('Error dalam prepareData: ' . $e->getMessage(), [
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function cleanNumber($value) {
        if (empty($value) || $value == 'NULL') {
            return 0;
        }
        
        // Hapus karakter non-numerik kecuali titik dan minus
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        return is_numeric($cleaned) ? $cleaned : 0;
    }
}
