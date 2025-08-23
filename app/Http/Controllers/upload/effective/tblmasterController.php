<?php

namespace App\Http\Controllers\upload\effective;

use App\Http\Controllers\Controller;
use App\Models\UploadEffective;
use App\Models\UploadEffectiveUpload;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Carbon\Carbon;

class TblmasterController extends Controller
{
    protected $uploadEffective;
    protected $uploadEffectiveUpload;
    public function __construct(UploadEffective $uploadEffective, UploadEffectiveUpload $uploadEffectiveUpload)
    {
        $this->uploadEffective = $uploadEffective;
        $this->uploadEffectiveUpload = $uploadEffectiveUpload;
    }

    public function index(Request $request)
    {
        $id_pt = Auth::user()->id_pt; // Dapatkan id_pt dari pengguna yang login
        // Ambil jumlah item per halaman dari query string, default 10
        $perPage = $request->input('per_page', 10);

        // Ambil data dengan pagination dan filter berdasarkan id_ptt
        $tblmaster = $this->uploadEffective
            ->where('id_pt', $id_pt)
            ->paginate($perPage);

        return view('upload.effective.layouts.appmaster', [
            'title' => 'Laravel - PHPSpreadsheet',
            'tblmaster' => $tblmaster // Pastikan ini adalah objek paginator
        ]);
    }

    protected function validateData($data)
    {
        Log::info('Memulai validasi data:', ['data' => $data]);

        // Validasi field wajib
        $required_fields = [
            'no_acc',
            'no_branch',
            'deb_name',
            'status',
            'ln_type',
            'org_date',
            'term',
            'mtr_date',
            'org_bal',
            'rate',
            'cbal',
            'prebal',
            'bilprn',
            'pmtamt',
            'lrebd',
            'nrebd',
            'ln_grp',
            'GROUP',
            'bilint',
            'bisifa',
            'birest',
            'freldt',
            'resdt',
            'restdt',
            'prov',
            'trxcost',
            'gol'
        ];

        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                Log::warning("Field '$field' tidak ditemukan", $data);
                return false;
            }

            // Izinkan nilai 0 atau "0"
            if ($data[$field] === null || $data[$field] === '') {
                Log::warning("Field '$field' kosong", ['value' => $data[$field]]);
                return false;
            }
        }

        // Validasi format tanggal
        $dateFields = ['org_date_dt', 'mtr_date_dt', 'lrebd_dt', 'nrebd_dt', 'freldt_dt', 'resdt_dt', 'restdt_dt'];
        foreach ($dateFields as $field) {
            if (!empty($data[$field]) && !strtotime($data[$field])) {
                Log::error("Format tanggal tidak valid untuk field '$field'", [
                    'nilai' => $data[$field]
                ]);
                return false;
            }
        }

        Log::info('Validasi berhasil');
        return true;
    }

    protected function formatData($row)
    {
        try {
            // Fungsi helper untuk format tanggal
            $formatDate = function ($date) {
                if (empty($date) || $date == "''" || $date == "''") {
                    return '1900-01-01';
                }

                try {
                    // Coba parse format dd/mm/yyyy atau dd-mm-yyyy
                    if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $date, $matches)) {
                        return sprintf('%s-%s-%s 00:00:00', $matches[3], $matches[2], $matches[1]);
                    }

                    // Coba parse format yyyy-mm-dd
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        return $date . ' 00:00:00';
                    }

                    $dateTime = new DateTime($date);
                    return $dateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    Log::error('Date parsing error:', ['date' => $date, 'error' => $e->getMessage()]);
                    return null;
                }
            };

            // Hapus spasi dari nilai numerik dan ganti koma dengan titik
            $cleanRow = array_map(function ($value) {
                if (is_string($value)) {
                    return trim(str_replace([',', ' '], ['', ''], $value));
                }
                return $value;
            }, $row);


            return [
                'no_acc' => (float) $cleanRow[0],
                'no_branch' => (float) $cleanRow[1],
                'deb_name' => (string) $cleanRow[2],
                'status' => (string) $cleanRow[3],
                'ln_type' => (string) $cleanRow[4],
                'org_date' => (float) $cleanRow[5],
                'org_date_dt' => !empty($cleanRow[6]) ? date('Y-m-d H:i:s', strtotime($cleanRow[6])) : null,
                'term' => (float) $cleanRow[7],
                'mtr_date' => (float) $cleanRow[8],
                'mtr_date_dt' => !empty($cleanRow[9]) ? date('Y-m-d H:i:s', strtotime($cleanRow[9])) : null,
                'org_bal' => (float) $cleanRow[10],
                'rate' => (float) $cleanRow[11],
                'cbal' => (float) $cleanRow[12],
                'prebal' => (float) $cleanRow[13],
                'bilprn' => (float) $cleanRow[14],
                'pmtamt' => (float) $cleanRow[15],
                'lrebd' => (float) $cleanRow[16],
                'lrebd_dt' => !empty($cleanRow[17]) ? date('Y-m-d H:i:s', strtotime($cleanRow[17])) : null,
                'nrebd' => (float) $cleanRow[18],
                'nrebd_dt' => !empty($cleanRow[19]) ? date('Y-m-d H:i:s', strtotime($cleanRow[19])) : null,
                'ln_grp' => (float) $cleanRow[20],
                'GROUP' => trim($cleanRow[21], "'"),
                'bilint' => (float) $cleanRow[22],
                'bisifa' => (float) $cleanRow[23],
                'birest' => (string) $cleanRow[24],
                'freldt' => (float) $cleanRow[25],
                'freldt_dt' => !empty($cleanRow[26]) && $cleanRow[26] != '1900-01-01' ? date('Y-m-d H:i:s', strtotime($cleanRow[26])) : null,
                'resdt' => (float) $cleanRow[27],
                'resdt_dt' => !empty($cleanRow[28]) && $cleanRow[28] != '1900-01-01' ? date('Y-m-d H:i:s', strtotime($cleanRow[28])) : null,
                'restdt' => (float) $cleanRow[29],
                'restdt_dt' => !empty($cleanRow[30]) && $cleanRow[30] != '1900-01-01' ? date('Y-m-d H:i:s', strtotime($cleanRow[30])) : null,
                'prov' => (float) $cleanRow[31],
                'trxcost' => (float) $cleanRow[32],
                'gol' => (int) $cleanRow[33]
            ];
        } catch (\Exception $e) {
            Log::error('Data formatting error: ' . $e->getMessage());
            Log::error('Row data: ' . json_encode($row));
            return null;
        }
    }


    public function importExcel(Request $request)
    {
        try {
            $request->validate([
                'uploadFile' => 'required|file|mimes:xlsx,csv,txt',
            ]);

            $user = Auth::user();
            $id_pt = $user->id_pt ?? '29';

            $file = $request->file('uploadFile');
            Log::info('File uploaded:', [
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]);

            // Deteksi format file berdasarkan ekstensi
            $extension = strtolower($file->getClientOriginalExtension());

            // Konfigurasi khusus untuk CSV
            if ($extension === 'csv') {
                $reader = new Csv();
                $reader->setInputEncoding('UTF-8');
                $reader->setDelimiter(',');
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            Log::info('Parsed rows:', ['row_count' => count($rows), 'rows' => $rows]);

            if (empty($rows)) {
                throw new \Exception('No rows found in the uploaded file.');
            }

            $successCount = 0;
            $errors = [];
            $duplicates = [];
            $expectedColumns = 33;

            foreach (array_slice($rows, 1) as $index => $row) {
                DB::beginTransaction();
                try {
                    //file harus sesuai dengan jumlah kolom
                    if (count($row) < $expectedColumns) {
                    $errors[] = "Baris " . ($index + 2) . ": Jumlah kolom tidak valid, ditemukan " . count($row) . ", diharapkan $expectedColumns";
                    Log::error('Invalid column count', ['row' => $row, 'count' => count($row)]);
                    DB::rollBack();
                    continue;
                }
                    //cek no_acc kosong
                    if (empty(trim((string) $row[0]))) {
                        $errors[] = "Baris " . ($index + 2) . ": No ACC kosong atau tidak valid";
                        Log::error('Invalid no_acc', ['row' => $row]);
                        DB::rollBack();
                        continue;
                    }
                    $existingRecord = DB::table('tblmaster_tmp')
                        ->where('no_acc', trim((string) $row[0]))
                        ->where('id_pt', $id_pt)
                        ->first();

                    // cek data duplikat
                    if ($existingRecord) {
                        $duplicates[] = "Baris " . ($index + 2) . ": No ACC '" . trim((string) $row[0]) . "' sudah ada untuk PT ini";
                        Log::warning('Duplicate found:', ['no_acc' => trim((string) $row[0]), 'id_pt' => $id_pt]);
                        DB::rollBack();
                        continue;
                    }
                    // convert tanggal 
                    $convertDate = function ($date) {
                        if (empty($date))
                            return null;
                        return date('Y-m-d H:i:s', strtotime($date));
                    };

                    $data = [
                        'no_acc' => trim((string) $row[0]),
                        'no_branch' => (int) $row[1],
                        'deb_name' => trim((string) $row[2]),
                        'status' => substr(trim((string) $row[3]), 0, 1),
                        'ln_type' => trim((string) $row[4]),
                        'org_date' => (int) $row[5],
                        'org_date_dt' => $convertDate($row[6]),
                        'term' => (int) $row[7],
                        'mtr_date' => (int) $row[8],
                        'mtr_date_dt' => $convertDate($row[9]),
                        'org_bal' => (float) str_replace(['$', ','], '', $row[10]),
                        'rate' => (float) str_replace(['$', ','], '', $row[11]),
                        'cbal' => (float) str_replace(['$', ','], '', $row[12]),
                        'prebal' => (float) str_replace(['$', ','], '', $row[13]),
                        'bilprn' => (float) str_replace(['$', ','], '', $row[14]),
                        'pmtamt' => (float) str_replace(['$', ','], '', $row[15]),
                        'lrebd' => (int) $row[16],
                        'lrebd_dt' => $convertDate($row[17]),
                        'nrebd' => (int) $row[18],
                        'nrebd_dt' => $convertDate($row[19]),
                        'ln_grp' => (int) $row[20],
                        'GROUP' => trim((string) $row[21]),
                        'bilint' => (float) str_replace(['$', ','], '', $row[22]),
                        'bisifa' => (float) str_replace(['$', ','], '', $row[23]),
                        'birest' => trim((string) $row[24]),
                        'freldt' => (int) $row[25],
                        'freldt_dt' => $convertDate($row[26]),
                        'resdt' => (int) $row[27],
                        'resdt_dt' => $convertDate($row[28]),
                        'restdt' => (int) $row[29],
                        'restdt_dt' => $convertDate($row[30]),
                        'prov' => (float) str_replace(['$', ','], '', $row[31]),
                        'trxcost' => (float) str_replace(['$', ','], '', $row[32]),
                        // hapus komen jika file ada column gol 'gol' => (int) $row[33],
                        'id_pt' => $id_pt
                    ];

                    foreach ($data as $key => $value) {
                        if (is_string($value)) {
                            $data[$key] = trim(str_replace('$', '', $value));
                        }
                        if ($value === '' || $value === null) {
                            if (in_array($key, ['org_bal', 'rate', 'cbal', 'prebal', 'bilprn', 'pmtamt', 'bilint', 'bisifa', 'prov', 'trxcost'])) {
                                $data[$key] = 0.0;
                                // tambah 'gol' jika file ada column gol
                            } elseif (in_array($key, ['no_branch', 'term', 'ln_grp'])) {
                                $data[$key] = 0;
                            }
                        }
                    }

                    Log::info('Attempting to insert row ' . ($index + 2), ['data' => $data]);
                    DB::table('tblmaster_tmp')->insert($data);
                    DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                    Log::error('Error pada baris ' . ($index + 2), [
                        'error' => $e->getMessage(),
                        'data' => $row,
                        'formatted_data' => $data ?? null
                    ]);
                }
            }
            // Prepare response message
            $message = [];
            if ($successCount > 0) {
                $message[] = "Berhasil import $successCount data";
            }
            if (!empty($duplicates)) {
                $message[] = "Data duplikat: " . implode("; ", $duplicates);
            }
            if (!empty($errors)) {
                $message[] = "Error: " . implode("; ", $errors);
            }

            if (empty($message)) {
                throw new \Exception('Tidak ada data yang berhasil diimport. File mungkin kosong atau semua baris duplikat.');
            }

            return redirect()->back()->with('warning', implode(". ", $message));
        } catch (\Exception $e) {
            Log::error('Import gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    protected function generateImportMessage($valid, $duplicates, $invalid, $existing)
    {
        $messages = [];
        $hasSuccess = $valid > 0; // Menandai apakah ada catatan yang berhasil diimporr

        if ($valid > 0) {
            $messages[] = "$valid catatan berhasil diimpor";
        }
        if ($duplicates > 0) {
            $messages[] = "$duplicates catatan duplikat ";
        }
        if ($invalid > 0) {
            $messages[] = "$invalid catatan tidak valid ";
        }
        if ($existing > 0) {
            $messages[] = "$existing catatan sudah ada ";
        }

        // Kembalikan array dengan pesan dan status sukses
        return [
            'success' => $hasSuccess,
            'message' => empty($messages) ? 'Tidak ada catatan yang diimpor' : implode(', ', $messages),
        ];
    }


    public function executeStoredProcedure(Request $request)
    {
        try {
            $request->validate([
                'bulan' => 'required|integer',
                'tahun' => 'required|integer'
            ]);

            DB::beginTransaction();

            $user = Auth::user();
            $id_pt = $user->id_pt;

            // Get all records from upload table for the current PT
            $uploadData = DB::table('tblmaster_tmp')
                ->where('id_pt', $id_pt)
                ->get();

            if ($uploadData->isEmpty()) {
                return redirect()->back()->withErrors(['message' => 'Tidak ada data yang tersedia untuk diproses.']);
            }

            // Move all records to tmp table first
            // foreach ($uploadData as $record) {
            //     DB::table('tblmaster_tmp')->insert((array)$record);
            // }

            // // Delete moved records from upload table
            // DB::table('tblmaster_tmp_upload')
            //     ->where('id_pt', $id_pt)
            //     ->delete();

            // Now process each record with the stored procedure
            $processedCount = 0;
            $errors = [];

            foreach ($uploadData as $record) {
                try {
                    $result = DB::select("SELECT * FROM public.ndcalculateeffectivetrigger_final(?, ?, ?, ?)", [
                        $request->bulan,
                        $request->tahun,
                        $record->no_acc,
                        $id_pt
                    ]);
                    $processedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing account {$record->no_acc}: {$e->getMessage()}";
                    Log::error('Error processing account:', [
                        'no_acc' => $record->no_acc,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Prepare response message
            $message = "Berhasil memproses $processedCount data";
            if (!empty($errors)) {
                $message .= ". Beberapa error terjadi: " . implode("; ", $errors);
            }

            return redirect()->back()->with('swal', [
                'title' => 'Berhasil!',
                'text' => $message,
                'icon' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error executing procedure:', [
                'error' => $e->getMessage(),
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'id_pt' => $id_pt ?? null
            ]);

            return redirect()->back()->with('swal', [
                'title' => 'Error!',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function clear(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $id_pt = $user->id_pt;

            if (!$id_pt) {
                Log::warning('ID PT tidak ditemukan untuk user:', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'ID PT tidak ditemukan');
            }

            Log::info('Attempting to clear data for PT ID: ' . $id_pt);

            // Riyaci remark - Tidak boleh hapus data tblmaster_tmpcorporate
            //$deleted = DB::table('tblmaster_tmp')
            //->where('id_pt', $id_pt)
            //->delete();
            //
            //if ($deleted > 0) {
            //    DB::commit();
            //    Log::info('Successfully deleted ' . $deleted . ' records');
            //    return redirect()->back()->with('success', 'Berhasil menghapus ' . $deleted . ' data');
            //}
            //
            //DB::rollBack();
            //Log::warning('No data found to delete for PT ID: ' . $id_pt);
            //return redirect()->back()->with('error', 'Tidak ada data yang dapat dihapus untuk PT ini');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear data error: ' . $e->getMessage(), [
                'id_pt' => $id_pt ?? null,
                'error' => $e
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function prepareData($row)
    {
        try {
            \Log::info('Raw CSV row:', $row);

            $id_pt = Auth::user()->id_pt ?? 'pt001';

            $data = [
                'no_acc' => $this->cleanValue($row[0]),
                'no_branch' => $this->cleanValue($row[1]),
                'deb_name' => $this->cleanValue($row[2]),
                'status' => $this->cleanValue($row[3]),
                'ln_type' => $this->cleanValue($row[4]),
                'org_date' => $this->formatDate($row[5]),
                'org_date_dt' => $this->formatDate($row[6]),
                'term' => intval($this->cleanValue($row[7]) ?? 0),
                'mtr_date' => $this->formatDate($row[8]),
                'mtr_date_dt' => $this->formatDate($row[9]),
                'org_bal' => $this->cleanNumber($row[10]) ?? 0,
                'rate' => $this->cleanNumber($row[11]) ?? 0,
                'cbal' => $this->cleanNumber($row[12]) ?? 0,
                'prbal' => $this->cleanNumber($row[13]) ?? 0,
                'bilpmt' => $this->cleanNumber($row[14]) ?? 0,
                'pmtamt' => $this->cleanNumber($row[15]) ?? 0,
                'frstd_dt' => $this->formatDate($row[16]),
                'lnstd_dt' => $this->formatDate($row[17]),
                'ln_grp' => $this->cleanValue($row[18]) ?: '0',
                'bsf1a' => $this->cleanValue($row[19]) ?: '0',
                'bsf1b' => $this->cleanValue($row[20]) ?: '0',
                'brest' => $this->cleanValue($row[21]) ?: '0',
                'fnstd_dt' => $this->formatDate($row[22]),
                'rstdt' => $this->formatDate($row[23]),
                'rstdt_dt' => $this->formatDate($row[24]),
                'trial014' => '1',
                'id_pt' => $id_pt,
                'freldt' => $this->cleanValue($row[26]),
                'freldt_dt' => $this->formatDate($row[27]),
                'resdt' => $this->cleanValue($row[28]),
                'resdt_dt' => $this->formatDate($row[29]),
                'restdt' => $this->cleanValue($row[30]),
                'restdt_dt' => $this->formatDate($row[31]),
                'prov' => $this->cleanNumber($row[32]) ?? 0,
                'trxcost' => $this->cleanNumber($row[33]) ?? 0,
                'gol' => (int) $this->cleanValue($row[34]),
                'GROUP' => $this->cleanValue($row[22]) ?: '0'
            ];

            // Tambahkan logging untuk debug
            \Log::info('Raw date values:', [
                'freldt_dt_raw' => $row[27],
                'resdt_dt_raw' => $row[29],
                'restdt_dt_raw' => $row[31]
            ]);

            \Log::info('Prepared data:', $data);
            return $data;

        } catch (\Exception $e) {
            \Log::error('Error in prepareData:', [
                'message' => $e->getMessage(),
                'row' => $row
            ]);
            throw $e;
        }
    }

    private function formatDate($dateValue)
    {
        try {
            if (empty($dateValue)) {
                return null;
            }

            // Debug log
            \Log::info('Format date input:', ['value' => $dateValue]);

            // Jika nilai adalah angka Excel (serial date)
            if (is_numeric($dateValue)) {
                return Date::excelToDateTimeObject($dateValue);
            }

            // Jika format tanggal adalah string (yyyy-mm-dd atau dd/mm/yyyy)
            if (is_string($dateValue)) {
                $dateValue = trim($dateValue);

                // Coba parse format dd/mm/yyyy
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                    $parts = explode('/', $dateValue);
                    return new DateTime("{$parts[2]}-{$parts[1]}-{$parts[0]}");
                }

                // Coba parse format yyyy-mm-dd
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
                    return new DateTime($dateValue);
                }
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Error formatting date:', [
                'input' => $dateValue,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function cleanValue($value)
    {
        if ($value === null || $value === '' || $value === "''" || $value === '""') {
            return null;
        }
        // Hapus kutip dan spasi di awal/akhir
        $value = trim($value, "' ");
        // Hapus karakter non-printable
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        return $value;
    }

    private function cleanNumber($value)
    {
        if ($value === null || $value === '' || $value === "''" || $value === '""') {
            return null;
        }

        // Hapus karakter khusus dan spasi
        $value = trim($value);
        $value = str_replace([',', ' '], ['', ''], $value);

        // Pastikan hanya angka, titik desimal dan minus yang tersisa
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? floatval($value) : null;
    }
}
