<?php

namespace App\Filament\Tsn\Resources\KedatanganSantriResource\Pages;

use App\Filament\Tsn\Resources\KedatanganSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKedatanganSantri extends EditRecord
{
    protected static string $resource = KedatanganSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
