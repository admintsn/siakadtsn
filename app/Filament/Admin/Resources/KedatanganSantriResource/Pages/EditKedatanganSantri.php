<?php

namespace App\Filament\Admin\Resources\KedatanganSantriResource\Pages;

use App\Filament\Admin\Resources\KedatanganSantriResource;
use App\Models\PesanDaftar;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKedatanganSantri extends EditRecord
{
    protected static string $resource = KedatanganSantriResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['waktu_datang'] = $data['putra'] + $data['putri'];

        return $data;
    }

    // protected function afterSave($record): void
    // {
    //     // Runs after the form fields are saved to the database.
    //     $editpd = PesanDaftar::where('id', $record->id)->first();
    //     $editpd->waktu_datang = $record->putra + $record->putri;
    //     $editpd->save();

    //     Notification::make()
    //         ->success()
    //         ->title('Data berhasil diubah')
    //         ->color('success')
    //         ->send();
    // }
}
