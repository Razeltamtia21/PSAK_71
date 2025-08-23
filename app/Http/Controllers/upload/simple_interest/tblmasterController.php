<?php

namespace App\Http\Controllers\upload\simple_interest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UploadSimpleInterest; // Ensure this model is created
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class tblmasterController extends Controller
{
    protected $homeModel;

    public function __construct(UploadSimpleInterest $homeModel)
    {
        $this->homeModel = $homeModel;
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $id_pt = $user->id_pt;
            $no_acc = $request->input('no_acc');
            if ($no_acc && (!is_numeric($no_acc) || $no_acc === '')) {
                \Log::warning('Invalid no_acc value', ['no_acc' => $no_acc]);
                return redirect()->back()->with('error', 'Invalid or missing account number');
            }
            $data['title'] = 'Laravel - PHPSpreadsheet';
            $data['tblmaster'] = $no_acc
                ? $this->homeModel->getByNoAcc($no_acc) 
                : $this->homeModel->fetchTblmaster($id_pt);
            return view('upload.simple_interest.layouts.appmaster', $data);
        } catch (\Exception $e) {
            \Log::error('Error in index: ' . $e->getMessage(), ['no_acc' => $no_acc ?? null, 'id_pt' => $id_pt]);
            return redirect()->back()->with('error', 'Failed to fetch data: ' . $e->getMessage());
        }
    }

    public function validateData($data)
    {
        // Update nama field sesuai dengan data yang masuk
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
            'group',
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
        return true;
    }

    public function importExcel(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'uploadFile' => 'required|file|mimes:xlsx,csv,txt',
            ]);

            $user = Auth::user();

            $file = $request->file('uploadFile');
            Log::info('File uploaded:', ['name' => $file->getClientOriginalName()]);
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'csv') {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }

            // dd($user->id_pt);

            $spreadsheet = $reader->load($file->getRealPath());
            $sheet_data = $spreadsheet->getActiveSheet()->toArray();
            $array_data = [];
            $duplicate_data = []; // Menyimpan data duplikat
            $invalid_data = []; // Menyimpan data tidak valid

            for ($i = 1; $i < count($sheet_data); $i++) {
                $data = [
                    'no_acc' => $sheet_data[$i][0],
                    'no_branch' => (int) $sheet_data[$i][1],
                    'deb_name' => $sheet_data[$i][2],
                    'status' => $sheet_data[$i][3],
                    'ln_type' => $sheet_data[$i][4],
                    'org_date' => (int) $sheet_data[$i][5],
                    'org_date_dt' => $sheet_data[$i][6],
                    'term' => (int) $sheet_data[$i][7],
                    'mtr_date' => (int) $sheet_data[$i][8],
                    'mtr_date_dt' => $sheet_data[$i][9],
                    'org_bal' => (float) $sheet_data[$i][10],
                    'rate' => (float) $sheet_data[$i][11],
                    'cbal' => (float) $sheet_data[$i][12],
                    'prebal' => (float) $sheet_data[$i][13],
                    'bilprn' => (float) $sheet_data[$i][14],
                    'pmtamt' => (float) $sheet_data[$i][15],
                    'lrebd' => (int) $sheet_data[$i][16],
                    'lrebd_dt' => $sheet_data[$i][17],
                    'nrebd' => (int) $sheet_data[$i][18],
                    'nrebd_dt' => $sheet_data[$i][19],
                    'ln_grp' => (int) $sheet_data[$i][20],
                    'group' => $sheet_data[$i][21],
                    'bilint' => (float) $sheet_data[$i][22],
                    'bisifa' => (int) $sheet_data[$i][23],
                    'birest' => $sheet_data[$i][24],
                    'freldt' => (int) $sheet_data[$i][25],
                    'freldt_dt' => $sheet_data[$i][26],
                    'resdt' => (int) $sheet_data[$i][27],
                    'resdt_dt' => $sheet_data[$i][28],
                    'restdt' => (int) $sheet_data[$i][29],
                    'restdt_dt' => $sheet_data[$i][30],
                    'prov' => (float) $sheet_data[$i][31],
                    'trxcost' => (float) $sheet_data[$i][32],
                    'gol' => (int) $sheet_data[$i][33],
                    "id_pt" => $user->id_pt
                ];

                Log::info('Processing row ' . $i, $data);

                if ($this->validateData($data)) {
                    $array_data[] = $data;
                } else {
                    Log::warning('Invalid data at row ' . $i, $data);
                }
            }

            if (!empty($array_data)) {
                Log::info('Attempting to insert data', ['count' => count($array_data)]);
                $result = $this->homeModel->insertTransactionBatch($array_data);

                if ($result) {
                    DB::commit();
                    return redirect()->back()->with('success', 'Data berhasil diimpor');
                }
            }

            DB::rollBack();
            return redirect()->back()->with('error', 'Tidak ada data valid yang dapat diimpor');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function executeStoredProcedure(Request $request)
    {
        try {
            $request->validate([
                'tahun' => 'required|integer',
                'bulan' => 'required|integer|min:1|max:12',
                'pilihan' => 'required|in:365,360',
            ]);

            $user = Auth::user();
            $id_pt = $user->id_pt;

            $bulan = date('n'); // Mengambil bulan saat inii
            $tahun = $request->tahun;
            $noAcc = $request->no_acc;
            $period = $request->pilihan;

            Log::info('Executing stored procedure with params:', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'noAcc' => $noAcc,
                'period' => $period
            ]);

            // Eksekusi stored procedure menggunakan DB::statement
            DB::statement("CALL public.ndcashflowcorporateloan_simpleinterest_final(?, ?, ?, ?)", [
                $bulan,
                $tahun,
                $period,
                $id_pt
            ]);

            return redirect()->back()->with('success', 'Stored procedure berhasil dieksekusi');

        } catch (\Exception $e) {
            Log::error('Error executing stored procedure: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengeksekusi stored procedure: ' . $e->getMessage());
        }
    }

    public function clear(Request $request)
    {
        try {
            $user = Auth::user();

            // Ambil id_pt dari properti user, bukan method
            $id_pt = $user->id_pt; // Mengakses sebagai properti, bukan method


            if (!$id_pt) {
                Log::warning('ID PT tidak ditemukan untuk user:', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'ID PT tidak ditemukan');
            }

            // Log untuk debugging
            Log::info('Attempting to clear data for PT ID: ' . $id_pt);

            $tblmaster = collect();

            DB::commit();

            // Return view with empty data
            return view('upload.simple_interest.tblmaster', compact('tblmaster'))
                ->with('success', 'Data has been cleared from the view.');

            // Riyaci remark - Tidak boleh hapus data tblmaster_tmpcorporate
            // Hapus data menggunakan query builder
            //$deleted = DB::table('tblmaster_tmpcorporate')
            //    ->where('id_pt', $id_pt)
            //    ->delete();
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
}
