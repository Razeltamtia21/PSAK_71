<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadEffective extends Model
{
    protected $table = 'tblmaster_tmp';
    public $timestamps = false;

    protected $fillable = [
        'no_acc',
        'no_branch',
        'deb_name',
        'status',
        'ln_type',
        'org_date',
        'org_date_dt',
        'term',
        'mtr_date',
        'mtr_date_dt',
        'org_bal',
        'rate',
        'cbal',
        'prebal',
        'bilprn',
        'pmtamt',
        'lrebd',
        'lrebd_dt',
        'nrebd',
        'nrebd_dt',
        'ln_grp',
        'GROUP',
        'bilint',
        'bisifa',
        'birest',
        'freldt',
        'freldt_dt',
        'resdt',
        'resdt_dt',
        'restdt',
        'restdt_dt',
        'prov',
        'trxcost',
        'gol'
    ];

    protected $casts = [
        'no_acc' => 'decimal:0',
        'no_branch' => 'decimal:0',
        'org_date' => 'decimal:0',
        'org_date_dt' => 'datetime',
        'term' => 'decimal:0',
        'mtr_date' => 'decimal:0',
        'mtr_date_dt' => 'datetime',
        'org_bal' => 'decimal:2',
        'rate' => 'decimal:14',
        'cbal' => 'decimal:2',
        'prebal' => 'decimal:2',
        'bilprn' => 'decimal:2',
        'pmtamt' => 'decimal:2',
        'lrebd' => 'decimal:0',
        'lrebd_dt' => 'datetime',
        'nrebd' => 'decimal:0',
        'nrebd_dt' => 'datetime',
        'ln_grp' => 'decimal:0',
        'bilint' => 'decimal:2',
        'bisifa' => 'decimal:0',
        'freldt' => 'decimal:0',
        'freldt_dt' => 'datetime',
        'resdt' => 'decimal:0',
        'resdt_dt' => 'datetime',
        'restdt' => 'decimal:0',
        'restdt_dt' => 'datetime',
        'gol' => 'integer'
    ];

    /**
     * Scope query untuk mencari berdasarkan no_acc
     */
    public function scopeByNoAcc($query, $noAcc, $idPt)
    {
        if (!is_numeric($noAcc) || $noAcc === '') {
            \Log::warning('Invalid no_acc value', ['no_acc' => $noAcc, 'id_pt' => $idPt]);
            throw new \Exception('Invalid or missing no_acc value');
        }
        if (!is_numeric($idPt)) {
            \Log::warning('Invalid id_pt value', ['no_acc' => $noAcc, 'id_pt' => $idPt]);
            throw new \Exception('Invalid or missing id_pt value');
        }
        return $query->where('no_acc', $noAcc)->where('id_pt', $idPt);
    }

    /**
     * Mengambil semua data
     */
    public static function fetchAll()
    {
        return static::query(); // Mengembalikan query builder, bukan koleksi
    }

    /**
     * Memeriksa duplikasi no_acc
     */
    public static function isDuplicate($noAcc)
    {
        return static::where('no_acc', $noAcc)->exists();
    }

    /**
     * Mengambil data berdasarkan no_acc
     */
    public static function findByNoAcc($noAcc, $idPt)
    {
        if (!is_numeric($noAcc) || $noAcc === '') {
            \Log::warning('Invalid no_acc value', ['no_acc' => $noAcc, 'id_pt' => $idPt]);
            throw new \Exception('Invalid or missing no_acc value');
        }
        if (!is_numeric($idPt)) {
            \Log::warning('Invalid id_pt value', ['no_acc' => $noAcc, 'id_pt' => $idPt]);
            throw new \Exception('Invalid or missing id_pt value');
        }
        return static::where('no_acc', $noAcc)->where('id_pt', $idPt)->first();
    }

    /**
     * Memperbarui data berdasarkan no_acc
     */
    public static function updateByNoAcc($noAcc, array $data)
    {
        return static::where('no_acc', $noAcc)->update($data);
    }

    /**
     * Menyisipkan batch data
     */
    public static function insertBatch(array $data)
    {
        return static::insert($data);
    }

    /**
     * Custom query untuk mengambil data dengan kondisi tertentu
     */
    public static function getFilteredData($filters = [])
    {
        $query = static::query();

        if (!empty($filters['no_acc'])) {
            $query->where('no_acc', $filters['no_acc']);
        }

        if (!empty($filters['deb_name'])) {
            $query->where('deb_name', 'like', '%' . $filters['deb_name'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Validasi data sebelum disimpan
     */
    public static function validateImportData($data)
    {
        $rules = [
            'no_acc' => 'required|numeric',
            'no_branch' => 'required|numeric',
            'deb_name' => 'required|string|max:50',
            'status' => 'required|string|max:1',
            'ln_type' => 'required|string|max:5',
            // tambahkan rules validasi lainnya sesuai kebutuhan
        ];

        return validator($data, $rules);
    }

    /**
     * Membersihkan cache setelah operasi database
     */
    public static function clearCache()
    {
        // Implementasi pembersihan cache jika menggunakan caching
        cache()->forget('tblmaster_tmp_data');
    }

    /**
     * Boot method untuk menambahkan events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            static::clearCache();
        });

        static::updated(function ($model) {
            static::clearCache();
        });

        static::deleted(function ($model) {
            static::clearCache();
        });
    }
}
