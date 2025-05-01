<?php

namespace App\Filament\Admin\Resources\DataAlumniResource\Pages;

use App\Filament\Admin\Resources\DataAlumniResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataAlumni extends ViewRecord
{
    protected static string $resource = DataAlumniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
