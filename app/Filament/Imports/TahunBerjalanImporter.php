<?php

namespace App\Filament\Imports;

use App\Models\TahunBerjalan;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TahunBerjalanImporter extends Importer
{
    protected static ?string $model = TahunBerjalan::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('tb')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('ts')
                ->rules(['max:255']),
            ImportColumn::make('is_active')
                ->rules(['max:50']),
            ImportColumn::make('created_by')
                ->rules(['max:255']),
            ImportColumn::make('updated_by')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?TahunBerjalan
    {
        // return TahunBerjalan::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new TahunBerjalan();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your tahun berjalan import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
