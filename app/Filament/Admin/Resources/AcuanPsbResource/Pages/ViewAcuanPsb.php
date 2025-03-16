<?php

namespace App\Filament\Admin\Resources\AcuanPsbResource\Pages;

use App\Filament\Admin\Resources\AcuanPsbResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAcuanPsb extends ViewRecord
{
    protected static string $resource = AcuanPsbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
