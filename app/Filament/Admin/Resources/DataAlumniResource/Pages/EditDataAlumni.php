<?php

namespace App\Filament\Admin\Resources\DataAlumniResource\Pages;

use App\Filament\Admin\Resources\DataAlumniResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataAlumni extends EditRecord
{
    protected static string $resource = DataAlumniResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }
}
