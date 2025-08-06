<?php

namespace App\Http\Controllers\upload\simple_interest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class OutstandingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $id_pt = $user->id_pt;
        $bulan = (int)$request->query('bulan', date('m'));
        $tahun = (int)$request->query('tahun', date('Y'));
        $perPage = $request->query('per_page', 10);

        // dd($bulan, $tahun);

        $tblmaster = DB::table('tblmaster_outstanding_corporateloan')
            ->where('id_pt', $id_pt)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->paginate($perPage);

        // dd($tblmaster);a

        return view('upload.simple_interest.layouts.outstanding', compact('tblmaster', 'bulan', 'tahun'));
    }

    public function clear(Request $request)
    {
        try {
            $user = Auth::user();
            $id_pt = $user->id_pt;
            $tahun = $request->input('tahun');
            $bulan = $request->input('bulan');

            DB::beginTransaction();
            
            Log::info('Attempting to clear data for PT ID: ' . $id_pt);
            
            $deleted = DB::table('tblmaster_outstanding_corporateloan')
                ->where('id_pt', $id_pt)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->delete();
            
            if ($deleted > 0) {
                DB::commit();
                Log::info('Successfully deleted ' . $deleted . ' records');
                return redirect()->back()->with('success', '✓ Berhasil menghapus ' . $deleted . ' data');
            }
            
            DB::rollBack();
            Log::warning('No data found to delete for PT ID: ' . $id_pt);
            return redirect()->back()->with('error', 'Tidak ada data yang dapat dihapus untuk kombinasi tahun dan bulan ini');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear data error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        try {
            $request->validate([
                'uploadFile' => 'required|file|mimes:xlsx,csv',
            ]);

            $user = Auth::user();
            $id_pt = $user->id_pt ?? 'pt001';
            $tahun = $request->input('tahun') ?? date('Y');
            $bulan = $request->input('bulan') ?? date('m');

            // Cek dan hapus data yang ada
            $existingData = DB::table('tblmaster_outstanding_corporateloan')
                ->where('id_pt', $id_pt)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->exists();

            if ($existingData) {
                DB::table('tblmaster_outstanding_corporateloan')
                    ->where('id_pt', $id_pt)
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->delete();
                
                Log::info('Data lama dihapus untuk PT: ' . $id_pt . ', Tahun: ' . $tahun . ', Bulan: ' . $bulan);
            }

            $file = $request->file('uploadFile');
            $extension = $file->getClientOriginalExtension();
            $reader = ($extension === 'csv') ? new Csv() : new Xlsx();
            
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $successCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue;
                
                DB::beginTransaction();

                $convertDate = function($date) {
                    if (empty($date)) return null;
                    return date('Y-m-d', strtotime($date));
                };

                // dd($row);

                try {
                        $data = [
                        'no_acc' => trim((string)$row[0]),
                        'no_branch' => (int)$row[1],
                        'tahun' => (int)$tahun,
                        'bulan' => (int)$bulan,
                        'deb_name' => trim((string)$row[2]),
                        'status' => substr(trim((string)$row[3]), 0, 10),
                        'ln_type' => substr(trim((string)$row[4]), 0, 10),
                        'org_date' => (int)$row[5],
                        'org_date_dt' => $convertDate($row[6]),
                        'term' => (int)$row[7],
                        'mtr_date' => (int)$row[8],
                        'mtr_date_dt' => $convertDate($row[9]),
                        'org_bal' => number_format((float)str_replace(['$', ','], '', $row[10]), 2, '.', ''),
                        'rate' => number_format((float)str_replace(['$', ','], '', $row[11]), 2, '.', ''),
                        'cbal' => number_format((float)str_replace(['$', ','], '', $row[12]), 2, '.', ''),
                        'prebal' => number_format((float)str_replace(['$', ','], '', $row[13]), 2, '.', ''),
                        'bilprn' => number_format((float)str_replace(['$', ','], '', $row[14]), 2, '.', ''),
                        'pmtamt' => number_format((float)str_replace(['$', ','], '', $row[15]), 2, '.', ''),
                        'lrebd' => number_format((float)str_replace(['$', ','], '', $row[16]), 2, '.', ''),
                        'lrebd_dt' => $convertDate($row[17]),
                        'nrebd' => (int)$row[18],
                        'nrebd_dt' => $convertDate($row[19]),
                        'ln_grp' => (int)$row[20],
                        'GROUP' => substr(trim((string)$row[21]), 0, 50),
                        'bilint' => number_format((float)str_replace(['$', ','], '', $row[22]), 2, '.', ''),
                        'bisifa' => number_format((float)str_replace(['$', ','], '', $row[23]), 2, '.', ''),
                        'birest' => substr(trim((string)$row[24]), 0, 10),
                        'freldt' => (int)$row[25],
                        'freldt_dt' => $convertDate($row[26]),
                        'resdt' => (int)$row[27],
                        'resdt_dt' => $convertDate($row[28]),
                        'restdt' => (int)$row[29],
                        'restdt_dt' => $convertDate($row[30]),
                        'prov' => number_format((float)str_replace(['$', ','], '', $row[31]), 2, '.', ''),
                        'trxcost' => number_format((float)str_replace(['$', ','], '', $row[32]), 2, '.', ''),
                        'gol' => (int)$row[33],
                        'id_pt' => $id_pt
                    ];

                    DB::table('tblmaster_outstanding_corporateloan')->insert($data);
                    DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                    Log::error('Import error at row ' . ($index + 1) . ': ' . $e->getMessage());
                }
            }

            if ($successCount > 0) {
                $message = "✓ Import $successCount data berhasil";
                if ($existingData) {
                    $message = "✓ Data lama berhasil dihapus\n" . $message;
                }
                return redirect()->back()->with('success', $message);
            }

            throw new \Exception('Tidak ada data yang berhasil diimport.');
            
        } catch (\Exception $e) {
            Log::error('Import gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function executeStoredProcedure(Request $request)
    {
        try {
            $request->validate([
                'tahun' => 'required|integer',
                'bulan' => 'required|integer|min:1|max:12',
            ]);

            $user = Auth::user();
            
            DB::select("CALL public.spoutbalcorporateloan(?, ?, ?)", [
                (int)$request->tahun,
                (int)$request->bulan,
                (int)$user->id_pt
            ]);

            return redirect()->back()->with('swal', [
                'title' => 'Berhasil!',
                'text' => 'Stored procedure berhasil dijalankan',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat menjalankan stored procedure: ' . $e->getMessage());
            return redirect()->back()->with('swal', [
                'title' => 'Error!',
                'text' => 'Gagal menjalankan stored procedure: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    private function cleanNumericValue($value) {
        if (is_string($value)) {
            // Hapus karakter non-numerik kecuali titik dan minus
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        return is_numeric($value) ? $value : null;
    }
}