<?php

namespace App\Models;

use App\log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TahapPendaftaran extends Model
{
    public function santris()
    {
        return $this->hasMany(Santri::class);
    }

    public function acuanPsbs()
    {
        return $this->hasMany(AcuanPsb::class);
    }

    use log;
}
