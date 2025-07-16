<?php

namespace App\Models;

use App\log;
use Illuminate\Database\Eloquent\Model;

class StatusDokumen extends Model
{
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class);
    }

    public function statusLokasiDokumen()
    {
        return $this->belongsTo(StatusLokasiDokumen::class);
    }

    use log;
}
