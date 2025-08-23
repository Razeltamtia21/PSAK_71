<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InitialRecognitionEffective extends Model
{
    public static function getInitialRecognition($branch, $tahun, $bulan)
    {
        if (!is_numeric($branch) || !is_numeric($tahun) || !is_numeric($bulan)) {
            \Log::warning('Invalid parameters in getInitialRecognition', [
                'branch' => $branch,
                'tahun' => $tahun,
                'bulan' => $bulan
            ]);
            return collect([]);
        }
        try {
            return DB::select("
            SELECT DISTINCT 
                a.id,
                a.no_branch,
                a.no_acc,
                a.deb_name,
                c.LN_TYPE,
                TO_CHAR(a.org_date, 'DD/MM/YYYY') as OrgdtConv,
                a.term,
                c.pmtamt,
                c.\"GROUP\" as glgroup,
                c.RATE,
                TO_CHAR(a.mtr_date, 'DD/MM/YYYY') as MtrdtConv,
                a.org_bal,
                a.oldbal,
                b.baleir,
                a.adjsmnt,
                a.eirex,
                a.eircalc,
                a.eircalc_conv,
                a.eircalc_cost,
                a.eircalc_fee,
                b.outsamtconv,
                b.outsamtcost,
                b.outsamtfee,
                EOM(b.TglAngsuran) as TglAngsuranConv,
                m.jdname,
                d.coa
            FROM tblOBALEffective a, \"CABANG-\" m, tblMASTER_tmp c, tblGROUPCOALoan d, tblCFOBALEffective b
            WHERE a.no_branch::numeric = m.jdbr::numeric
            AND a.no_acc::numeric = c.no_acc::numeric
            AND c.\"GROUP\" = d.\"GROUP\"
            AND a.no_acc = b.no_acc
            AND DATE_PART('year', b.tglangsuran) = ?
            AND DATE_PART('month', b.tglangsuran) = ?
            AND (a.org_date) > '2012-01-01'
            AND b.BULANKE = 0
            AND a.NO_BRANCH::text = ?
        ", [$tahun, $bulan, $branch]);
        } catch (\Exception $e) {
            \Log::error('Error in getInitialRecognition: ' . $e->getMessage(), [
                'branch' => $branch,
                'tahun' => $tahun,
                'bulan' => $bulan
            ]);
            return collect([]);
        }
    }
}