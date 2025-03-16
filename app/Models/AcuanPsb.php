<?php

namespace App\Models;

use App\log;
use Illuminate\Database\Eloquent\Model;

class AcuanPsb extends Model
{
    public function qism()
    {
        return $this->belongsTo(Qism::class);
    }

    public function qismDetail()
    {
        return $this->belongsTo(QismDetail::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jenisPendaftar()
    {
        return $this->belongsTo(JenisPendaftar::class);
    }

    public function tahunBerjalan()
    {
        return $this->belongsTo(TahunBerjalan::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function statusPendaftaran()
    {
        return $this->belongsTo(StatusPendaftaran::class);
    }

    public function tahapPendaftaran()
    {
        return $this->belongsTo(TahapPendaftaran::class);
    }

    public function semesterBerjalan()
    {
        return $this->belongsTo(SemesterBerjalan::class);
    }

    public function sem2()
    {
        return $this->belongsTo(Sem::class, 'sem_id');
    }

    use log;
}
