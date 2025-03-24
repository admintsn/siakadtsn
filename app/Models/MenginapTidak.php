<?php

namespace App\Models;

use App\log;
use Illuminate\Database\Eloquent\Model;

class MenginapTidak extends Model
{
    public function walisantris()
    {
        return $this->hasMany(Walisantri::class);
    }

    public function pesanDaftars()
    {
        return $this->hasMany(PesanDaftar::class);
    }

    use log;
}
