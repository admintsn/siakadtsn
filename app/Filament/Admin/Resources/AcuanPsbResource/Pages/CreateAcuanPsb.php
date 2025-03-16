<?php

namespace App\Filament\Admin\Resources\AcuanPsbResource\Pages;

use App\CreateTrait;
use App\Filament\Admin\Resources\AcuanPsbResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAcuanPsb extends CreateRecord
{
    protected static string $resource = AcuanPsbResource::class;

    use CreateTrait;
}
