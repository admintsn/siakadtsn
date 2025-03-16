<?php

namespace App\Filament\Imports;

use App\Models\AcuanPsb;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AcuanPsbImporter extends Importer
{
    protected static ?string $model = AcuanPsb::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('jenis_pendaftar_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('tahap_pendaftaran_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('status_pendaftaran_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('daftarnaikqism')
                ->rules(['max:255']),
            ImportColumn::make('tahun_berjalan_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('angkatan_tahun')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('tahun_ajaran_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('qism_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('qism_detail_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('kelas_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('sem_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('semester_berjalan_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('created_by')
                ->rules(['max:255']),
            ImportColumn::make('updated_by')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?AcuanPsb
    {
        // return AcuanPsb::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new AcuanPsb();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your acuan psb import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
