<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pt extends Model
{
    protected $table = 'tbl_pt';
    protected $primaryKey = 'pt_id';
    protected $fillable = ['nama_pt', 'alamat_pt', 'company_type'];
    public $incrementing = true; // set ke false jika pt_id varchar
    // protected $keyType = 'string'; // hapus comment kalau pt_id varchar
    public $timestamps = false;
}