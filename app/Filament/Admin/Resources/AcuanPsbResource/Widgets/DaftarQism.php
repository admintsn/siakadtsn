<?php

namespace App\Filament\Admin\Resources\AcuanPsbResource\Widgets;

use App\Models\AcuanPsb;
use App\Models\KelasSantri;
use App\Models\NismPerTahun;
use App\Models\QismDetail;
use App\Models\QismDetailHasKelas;
use App\Models\Semester;
use App\Models\SemesterBerjalan;
use App\Models\TahunAjaran;
use App\Models\TahunAjaranAktif;
use App\Models\TahunBerjalan;
use Carbon\Carbon;
use Filament\Notifications\Notification;
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
            ->defaultPaginationPageOption(10)
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

                Tables\Actions\BulkAction::make('acuanpsbbaru')
                    ->label(__('Generate Acuan PSB Baru'))
                    ->color('info')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-exclamation-triangle')
                    // ->modalIconColor('danger')
                    // ->modalHeading('Simpan data santri tinggal kelas?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            // dd($record);

                            // $naikqism = QismDetailHasKelas::where('qism_id', $record->qism_id)
                            //     ->where('qism_detail_id', $record->qism_detail_id)
                            //     ->where('kelas_id', $record->kelas_id)->first();

                            $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                            $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                            $cekdatats = AcuanPsb::where('jenis_pendaftar_id', 1)
                                ->where('tahun_berjalan_id', $ts->id)
                                ->where('qism_id', $record->qism_id)
                                ->where('qism_detail_id', $record->id)
                                ->count();

                            if ($cekdatats == 0) {

                                $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                // $datakelassantri = KelasSantri::where('tahun_berjalan_id', $tahunberjalanaktif->id)
                                //     ->where('santri_id', $record->santri_id)->first();

                                $kelas = QismDetailHasKelas::where('qism_id', $record->qism_id)
                                    ->where('qism_detail_id', $record->id)->first();

                                $gettaaktif = TahunAjaranAktif::where('qism_id', $record->qism_id)->where('is_active', 1)->first();

                                $getta = TahunAjaran::where('id', $gettaaktif->tahun_ajaran_id)->first();

                                $getsemaktif = TahunAjaranAktif::where('qism_id', $record->qism_id)->where('is_active', 1)->first();

                                $getsem = Semester::where('qism_id', $record->qism_id)->where('sem_id', $getsemaktif->semester_id)->first();

                                $semberjalan = SemesterBerjalan::where('is_active', false)->first();

                                $tahun = Carbon::now()->year;

                                $getnismstart = NismPerTahun::where('tahun', $tahun)->first();
                                $nismstart = $getnismstart->nismstart;
                                $angktahun = substr($nismstart, 0, 2);

                                $acuanpsbbaru = new AcuanPsb;
                                $acuanpsbbaru->jenis_pendaftar_id = 1;
                                $acuanpsbbaru->tahap_pendaftaran_id = 1;
                                $acuanpsbbaru->status_pendaftaran_id = null;
                                $acuanpsbbaru->daftarnaikqism = null;
                                $acuanpsbbaru->tahun_berjalan_id = $ts->id;
                                $acuanpsbbaru->angkatan_tahun = $angktahun;
                                $acuanpsbbaru->tahun_ajaran_id = $getta->tahun_ajaran_id;
                                $acuanpsbbaru->qism_id = $record->qism_id;
                                $acuanpsbbaru->qism_detail_id = $record->id;
                                $acuanpsbbaru->kelas_id = null;
                                $acuanpsbbaru->sem_id = $getsem->sem_sel;
                                $acuanpsbbaru->semester_berjalan_id = $semberjalan->id;
                                $acuanpsbbaru->is_active = 1;
                                $acuanpsbbaru->save();

                                Notification::make()
                                    ->success()
                                    ->title('Data Acuan PSB generated')
                                    ->color('Success')
                                    ->send();
                            } elseif ($cekdatats != 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Data Acuan PSB sudah ada')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->iconColor('danger')
                                    ->color('warning')
                                    ->send();
                            }
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('acuanpsblama')
                    ->label(__('Generate Acuan PSB Lama'))
                    ->color('info')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-exclamation-triangle')
                    // ->modalIconColor('danger')
                    // ->modalHeading('Simpan data santri tinggal kelas?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            // dd($record);

                            $naikqism = QismDetailHasKelas::where('qism_id', $record->qism_id)
                                ->where('qism_detail_id', $record->id)
                                ->where('terakhir', 1)->first();

                            $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                            $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                            $cekdatats = AcuanPsb::where('jenis_pendaftar_id', 2)
                                ->where('tahun_berjalan_id', $ts->id)
                                ->where('qism_id', $record->qism_id)
                                ->where('qism_detail_id', $record->id)
                                ->count();

                            if ($cekdatats == 0) {

                                $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                // $datakelassantri = KelasSantri::where('tahun_berjalan_id', $tahunberjalanaktif->id)
                                //     ->where('santri_id', $record->santri_id)->first();

                                $kelas = QismDetailHasKelas::where('qism_id', $record->qism_id)
                                    ->where('qism_detail_id', $record->id)->first();

                                $gettaaktif = TahunAjaranAktif::where('qism_id', $record->qism_id)->where('is_active', 1)->first();

                                $getta = TahunAjaran::where('id', $gettaaktif->tahun_ajaran_id)->first();

                                $getsemaktif = TahunAjaranAktif::where('qism_id', $record->qism_id)->where('is_active', 1)->first();

                                $getsem = Semester::where('qism_id', $record->qism_id)->where('sem_id', $getsemaktif->semester_id)->first();

                                $semberjalan = SemesterBerjalan::where('is_active', false)->first();

                                $tahun = Carbon::now()->year;

                                $getnismstart = NismPerTahun::where('tahun', $tahun)->first();
                                $nismstart = $getnismstart->nismstart;
                                $angktahun = substr($nismstart, 0, 2);

                                // dd($naikqism);

                                $taaktif = TahunAjaranAktif::where('is_active', true)->where('qism_id', $naikqism->qism_s)->first();

                                $tasel = TahunAjaran::where('id', $taaktif->tahun_ajaran_id)->first();

                                $acuanpsblama = new AcuanPsb;
                                $acuanpsblama->jenis_pendaftar_id = 2;
                                $acuanpsblama->tahap_pendaftaran_id = 1;
                                $acuanpsblama->status_pendaftaran_id = null;
                                $acuanpsblama->daftarnaikqism = 'Belum Mendaftar';
                                $acuanpsblama->tahun_berjalan_id = $ts->id;
                                $acuanpsblama->angkatan_tahun = null;
                                $acuanpsblama->qism_id = $record->qism_id;
                                $acuanpsblama->qism_detail_id = $record->id;
                                $acuanpsblama->kelas_id = null;
                                $acuanpsblama->qism_s = $naikqism->qism_s;
                                $acuanpsblama->qism_detail_s = $naikqism->qism_detail_s;
                                $acuanpsblama->kelas_s = $naikqism->kelas_s;
                                $acuanpsblama->tahun_ajaran_id = $tasel->tahun_ajaran_id;
                                $acuanpsblama->sem_id = $getsem->sem_sel;
                                $acuanpsblama->semester_berjalan_id = $semberjalan->id;
                                $acuanpsblama->is_active = 1;
                                $acuanpsblama->save();

                                Notification::make()
                                    ->success()
                                    ->title('Data Acuan PSB generated')
                                    ->color('Success')
                                    ->send();
                            } elseif ($cekdatats != 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Data Acuan PSB sudah ada')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->iconColor('danger')
                                    ->color('warning')
                                    ->send();
                            }
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),

            ]);
    }
}
