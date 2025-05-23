<?php

namespace App\Filament\Imports;

use App\Models\PendaftarNaikQism;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PendaftarNaikQismImporter extends Importer
{
    protected static ?string $model = PendaftarNaikQism::class;

    public static function getColumns(): array
    {
        return [
            //
        ];
    }

    public function resolveRecord(): ?PendaftarNaikQism
    {
        // return PendaftarNaikQism::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new PendaftarNaikQism();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your pendaftar naik qism import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
