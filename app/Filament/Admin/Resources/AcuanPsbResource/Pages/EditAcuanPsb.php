<?php

namespace App\Filament\Admin\Resources\AcuanPsbResource\Pages;

use App\EditTrait;
use App\Filament\Admin\Resources\AcuanPsbResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcuanPsb extends EditRecord
{
    protected static string $resource = AcuanPsbResource::class;

    use EditTrait;
}
