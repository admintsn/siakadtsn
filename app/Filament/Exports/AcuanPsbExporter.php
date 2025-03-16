<?php

namespace App\Filament\Exports;

use App\Models\AcuanPsb;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AcuanPsbExporter extends Exporter
{
    protected static ?string $model = AcuanPsb::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('jenis_pendaftar_id'),
            ExportColumn::make('tahap_pendaftaran_id'),
            ExportColumn::make('status_pendaftaran_id'),
            ExportColumn::make('daftarnaikqism'),
            ExportColumn::make('tahun_berjalan_id'),
            ExportColumn::make('angkatan_tahun'),
            ExportColumn::make('tahun_ajaran_id'),
            ExportColumn::make('qism_id'),
            ExportColumn::make('qism_detail_id'),
            ExportColumn::make('kelas_id'),
            ExportColumn::make('sem_id'),
            ExportColumn::make('semester_berjalan_id'),
            ExportColumn::make('is_active'),
            ExportColumn::make('created_by'),
            ExportColumn::make('updated_by'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your acuan psb export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
