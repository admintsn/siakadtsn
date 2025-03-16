<?php

namespace App\Filament\Admin\Resources\AcuanPsbResource\Widgets;

use App\Models\QismDetail;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class DaftarQism extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {

        return $table
            ->query(
                QismDetail::where('is_active', 1)
            )
            ->defaultPaginationPageOption(5)
            ->columns([

                ColumnGroup::make('Qism Detail', [

                    TextColumn::make('qism.abbr_qism')
                        ->label('Qism')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('abbr_qism_detail')
                        ->label('Qism Detail')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('qism_detail')
                        ->label('Desc')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('kode_qism_detail')
                        ->label('Kode Qism Detail')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable()
                        ->alignCenter(),

                    TextColumn::make('jeniskelamin.jeniskelamin')
                        ->label('Jenis Kelamin')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('qismDetail.abbr_qism_detail')
                        ->label('Qism Detail Selanjutnya')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                ]),

                ColumnGroup::make('Status', [

                    CheckboxColumn::make('is_active')
                        ->label('Status')
                        ->sortable()
                        ->alignCenter(),

                ]),

                ColumnGroup::make('Logs', [

                    TextColumn::make('created_by')
                        ->label('Created by')
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('updated_by')
                        ->label('Updated by')
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                ]),
            ])
            ->recordUrl(null)
            ->searchOnBlur()
            ->bulkActions([

                Tables\Actions\BulkAction::make('acuanpsb')
                    ->label(__('Generate Acuan PSB'))
                    ->color('info')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-exclamation-triangle')
                    // ->modalIconColor('danger')
                    // ->modalHeading('Simpan data santri tinggal kelas?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                            $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                            $cekdatats = KelasSantri::where('tahun_berjalan_id', $ts->id)
                                ->where('santri_id', $record->santri_id)->count();

                            // dd($record->santri_id, $cekdatats);

                            if ($cekdatats == 0) {

                                $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                $datakelassantri = KelasSantri::where('tahun_berjalan_id', $tahunberjalanaktif->id)
                                    ->where('santri_id', $record->santri_id)->first();

                                $gettaaktif = TahunAjaranAktif::where('qism_id', $record->qism_id)->where('is_active', 1)->first();

                                $getta = TahunAjaran::where('id', $gettaaktif->tahun_ajaran_id)->first();

                                $getsemaktif = TahunAjaranAktif::where('qism_id', $record->qism_id)->where('is_active', 1)->first();

                                $getsem = Semester::where('qism_id', $record->qism_id)->where('sem_id', $getsemaktif->semester_id)->first();

                                $semberjalan = SemesterBerjalan::where('is_active', false)->first();

                                $kelassantri = new KelasSantri;

                                $kelassantri->santri_id = $record->santri_id;
                                $kelassantri->mahad_id = '1';
                                $kelassantri->tahun_berjalan_id = $ts->id;
                                $kelassantri->tahun_ajaran_id = $getta->tahun_ajaran_id;
                                $kelassantri->semester_id = $getsem->sem_sel;
                                $kelassantri->qism_id = $datakelassantri->qism_id;
                                $kelassantri->qism_detail_id = $datakelassantri->qism_detail_id;
                                $kelassantri->kelas_id = $datakelassantri->kelas_id;
                                $kelassantri->semester_berjalan_id = $semberjalan->id;
                                $kelassantri->save();

                                Notification::make()
                                    ->success()
                                    ->title('Status Ananda telah diupdate')
                                    ->persistent()
                                    ->color('Success')
                                    ->send();
                            } elseif ($cekdatats != 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Santri tidak dapat diubah status "tinggal kelas"')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->iconColor('danger')
                                    ->persistent()
                                    ->color('warning')
                                    ->send();
                            }
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),


            ]);
    }
}
