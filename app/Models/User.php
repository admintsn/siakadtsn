<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\log;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel as FilamentPanel;
use Filament\Tables\Columns\Layout\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

// use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'panelrole',
    ];

    // public function canAccessPanel(FilamentPanel $panel): bool
    // {

    //     switch (true) {

    //         case ($panel->getId() === 'admin'):
    //             if (auth()->user()->panelrole === 'admin') {
    //                 return true;
    //             } else {
    //                 return false;
    //             }

    //             break;

    //         case ($panel->getId() === 'tsn'):
    //             if (auth()->user()->panelrole === 'pengajar' || auth()->user()->panelrole === 'admin') {
    //                 return true;
    //             } else {
    //                 return false;
    //             }

    //             break;

    //         case ($panel->getId() === 'walisantri'):
    //             if (auth()->user()->panelrole === 'walisantri' || auth()->user()->panelrole === 'admin') {
    //                 return true;
    //             } else {
    //                 return false;
    //             }

    //             break;
    //     }


    //     // if (auth()->user()->panelrole === 'admin' && $panel->getId() === 'admin') {

    //     //     return true;
    //     // } elseif (auth()->user()->panelrole === 'pengajar' || auth()->user()->panelrole === 'admin' && $panel->getId() === 'tsn') {
    //     //     dd(auth()->user()->panelrole, $panel->getId());
    //     //     return true;
    //     // } elseif (auth()->user()->panelrole === 'walisantri' || auth()->user()->panelrole === 'admin'  && $panel->getId() === 'walisantri') {
    //     //     return true;
    //     // } {

    //     //     return false;
    //     // }
    // }

    public function canAccessPanel(FilamentPanel $panel): bool
    {

        // if ($panel->getId() === 'admin') {
        //     return auth()->user()->panelrole_id == 3;
        // }

        // return false;

        // if ($panel->getId() === 'tsn') {
        //     return auth()->user()->panelrole_id == 3;
        // }

        // return false;

        // if ($panel->getId() === 'admin') {
        //     return auth()->user()->panelrole_id == 1;
        // }

        // return true;

        // if ($panel->getId() === 'tsn') {
        //     return auth()->user()->panelrole_id == 2;
        // }

        // return true;

        // if ($panel->getId() === 'walisantri') {
        //     return auth()->user()->panelrole_id == 3;
        // }

        // return true;

        switch ($panel->getId()) {
            case 'admin':
                return auth()->user()->panelrole_id == 1;
            case 'tsn':
                return auth()->user()->panelrole_id == 2 || auth()->user()->panelrole_id == 1;
            default:
                return true;
        }
    }

    public function getRedirectRoute(): string
    {
        return match ((int)$this->panelrole_id) {
            1 => 'admin',
            2 => 'tsn',
            3 => '/',
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'mudirqism' => 'array',
        'role' => 'array',
    ];

    public function getRoleNamesAttribute(): string
    {
        return $this->roles->pluck('name')->join(',');
    }

    // public function walisantri()
    // {
    //     return $this->hasOne(Walisantri::class);
    // }

    // public function pengajar()
    // {
    //     return $this->hasOne(Pengajar::class);
    // }

    // public function pendaftar()
    // {
    //     return $this->hasOne(Pendaftar::class);
    // }

    public function panelrole()
    {
        return $this->belongsTo(Panelrole::class);
    }

    public function qism()
    {
        return $this->belongsTo(Panelrole::class, 'mudirqism');
    }

    public function staffAdmins()
    {
        return $this->hasMany(StaffAdmin::class);
    }

    public function walisantris()
    {
        return $this->hasMany(Walisantri::class);
    }

    public function santris()
    {
        return $this->hasMany(Santri::class);
    }

    use log;
}
