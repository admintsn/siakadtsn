<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait log
{

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public static function boot()
    {
        parent::boot();
        $user = Auth::user();

        // if ($user === null) {
        //     return;
        // } else {

        static::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->username;
            $model->updated_by = $user->username;
        });
        static::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->username;
        });
        // }
    }
}
