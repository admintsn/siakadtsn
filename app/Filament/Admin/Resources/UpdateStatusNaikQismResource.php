<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UpdateStatusNaikQismResource\Pages;
use App\Filament\Admin\Resources\UpdateStatusNaikQismResource\RelationManagers;
use App\Models\AcuanPsb;
use App\Models\Kelas;
use App\Models\KelasSantri;
use App\Models\QismDetail;
use App\Models\QismDetailHasKelas;
use App\Models\Santri;
use App\Models\StatusSantri;
use App\Models\TahunAjaran;
use App\Models\TahunAjaranAktif;
use App\Models\TahunBerjalan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UpdateStatusNaikQismResource extends Resource
{
    protected static ?string $model = KelasSantri::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->id == 1 || auth()->user()->id == 2;
    }

    protected static ?string $modelLabel = 'Update Santri Naik Qism';

    protected static ?string $pluralModelLabel = 'Update Santri Naik Qism';

    protected static ?string $navigationLabel = 'Update Santri Naik Qism';

    protected static ?int $navigationSort = 200000000;

    // protected static ?string $navigationIcon = 'heroicon-o-Qisms';

    // protected static ?string $cluster = Kesantrian::class;

    protected static ?string $navigationGroup = 'PSB';

    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('santri.id')
                    ->label('ID')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->sortable(),

                TextColumn::make('santri.nism')
                    ->label('NISM')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextInputColumn::make('santri.kartu_keluarga')
                    ->label('KK')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->visible(auth()->user()->id == 1)
                    ->sortable(),

                TextColumn::make('santri.statussantri.statSantri.stat_santri')
                    ->label('Status')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->visible(auth()->user()->id == 1)
                    ->sortable(),

                CheckboxColumn::make('is_mustamiah')
                    ->label('Mustamiah?')
                    ->alignCenter(),

                TextColumn::make('santri.jenisPendaftar.jenis_pendaftar')
                    ->label('Pendaftar')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->sortable(),

                TextColumn::make('santri.daftarnaikqism')
                    ->label('Status Pendaftaran')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.nama_lengkap')
                    ->label('Nama')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.nama_panggilan')
                    ->label('Panggilan')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.walisantri.ak_nama_lengkap')
                    ->label('Nama Ayah')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.jeniskelamin.jeniskelamin')
                    ->label('Jenis Kelamin')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->sortable(),

                TextColumn::make('qism_detail.abbr_qism_detail')
                    ->label('Qism')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                TextColumn::make('kelas.kelas')
                    ->label('Kelas')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.tahunBerjalan.tb')
                    ->label('ke Tahun Berjalan')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.tahunAjaran.abbr_ta')
                    ->label('ke Tahun Ajaran')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.qism.abbr_qism')
                    ->label('Ke Qism')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                TextColumn::make('santri.qism_detail.abbr_qism_detail')
                    ->label('Ke Qism Detail')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
            ])
            ->groups([
                Group::make('qism_detail.abbr_qism_detail')
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('qism_detail.abbr_qism_detail')
            ->defaultSort('nama_lengkap')
            ->recordUrl(null)
            ->searchOnBlur()
            ->filters([
                QueryBuilder::make()
                    ->constraintPickerColumns(1)
                    ->constraints([

                        // SelectConstraint::make('qism_id')
                        //     ->label('Qism')
                        //     ->options(Qism::whereIsActive(1)->pluck('abbr_qism', 'id'))
                        //     ->nullable(),

                        SelectConstraint::make('qism_detail_id')
                            ->label('Qism Detail')
                            ->options(QismDetail::whereIsActive(1)->pluck('abbr_qism_detail', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('kelas_id')
                            ->label('Kelas')
                            ->options(Kelas::whereIsActive(1)->pluck('kelas', 'id'))
                            ->multiple()
                            ->nullable(),

                        TextConstraint::make('nama_lengkap')
                            ->label('Nama Santri')
                            ->icon(false)
                            ->nullable(),

                        BooleanConstraint::make('is_mustamiah')
                            ->label('Mustamiah')
                            ->icon(false)
                            ->nullable(),

                        BooleanConstraint::make('is_active')
                            ->label('Status')
                            ->icon(false)
                            ->nullable(),

                        TextConstraint::make('created_by')
                            ->label('Created by')
                            ->icon(false)
                            ->nullable(),

                        TextConstraint::make('updated_by')
                            ->label('Updated by')
                            ->icon(false)
                            ->nullable(),

                        DateConstraint::make('created_at')
                            ->icon(false)
                            ->nullable(),

                        DateConstraint::make('updated_at')
                            ->icon(false)
                            ->nullable(),

                    ]),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('naikqism')
                    ->label(__('Naik Qism'))
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor('info')
                    ->modalHeading('Ubah Status Santri sebagai Pendaftar Naik Qism?')
                    ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(function ($record) {

                        $naikqism = QismDetailHasKelas::where('qism_id', $record->qism_id)
                            ->where('qism_detail_id', $record->qism_detail_id)
                            ->where('kelas_id', $record->kelas_id)->first();

                        $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                        $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                        if ($naikqism->terakhir == true) {

                            if ($record->is_mustamiah == true) {

                                $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                $tamaktif = TahunAjaranAktif::where('is_active', true)->where('qism_id', $record->qism_id)->first();

                                $tamsel = TahunAjaran::where('id', $tamaktif->tahun_ajaran_id)->first();


                                $santri = Santri::where('id', $record->santri_id)->first();
                                $santri->tahun_berjalan_id = $ts->id;
                                $santri->tahun_ajaran_id = $tamsel->tahun_ajaran_id;
                                $santri->qism_id = $record->qism_id;
                                $santri->qism_detail_id = $record->qism_detail_id;
                                $santri->kelas_id = $record->kelas_id;
                                $santri->jenis_pendaftar_id = 2;
                                $santri->qism = null;
                                $santri->qism_tujuan = null;
                                $santri->qism_detail = null;
                                $santri->qism_detail_tujuan = null;
                                $santri->kelas = null;
                                $santri->kelas_tujuan = null;
                                $santri->naikqism = null;
                                $santri->daftarnaikqism = 'Belum Mendaftar';
                                $santri->tahap = null;
                                $santri->status_tahap = null;
                                $santri->deskripsitahap = null;
                                $santri->jenispendaftar = null;
                                $santri->tahap_pendaftaran_id = null;
                                $santri->status_pendaftaran_id = null;
                                $santri->ps_kadm_status_id = null;
                                $santri->file_kk = null;
                                $santri->file_akte = null;
                                $santri->file_srs = null;
                                $santri->file_ijz = null;
                                $santri->file_kk = null;
                                $santri->file_skt = null;
                                $santri->file_skuasa = null;
                                $santri->file_cvd = null;
                                $santri->file_spkm = null;
                                $santri->file_pka = null;
                                $santri->file_ktmu = null;
                                $santri->file_ktmp = null;

                                $santri->save();

                                Notification::make()
                                    ->success()
                                    ->title('Status Ananda telah diupdate')
                                    ->icon('heroicon-o-check-circle')
                                    // ->persistent()
                                    ->color('Success')
                                    // ->actions([
                                    //     Action::make('view')
                                    //         ->button(),
                                    //     Action::make('undo')
                                    //         ->color('secondary'),
                                    // ])
                                    ->send();
                            } elseif ($record->is_mustamiah != true) {

                                if ($naikqism->qism_s != null) {

                                    $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                    $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                    $taaktif = TahunAjaranAktif::where('is_active', true)->where('qism_id', $naikqism->qism_s)->first();

                                    $tasel = TahunAjaran::where('id', $taaktif->tahun_ajaran_id)->first();

                                    $santri = Santri::where('id', $record->santri_id)->first();
                                    $santri->tahun_berjalan_id = $ts->id;
                                    $santri->tahun_ajaran_id = $tasel->tahun_ajaran_id;
                                    $santri->qism_id = $naikqism->qism_s;
                                    $santri->qism_detail_id = $naikqism->qism_detail_s;
                                    $santri->kelas_id = $naikqism->kelas_s;
                                    $santri->jenis_pendaftar_id = 2;
                                    $santri->qism = null;
                                    $santri->qism_tujuan = null;
                                    $santri->qism_detail = null;
                                    $santri->qism_detail_tujuan = null;
                                    $santri->kelas = null;
                                    $santri->kelas_tujuan = null;
                                    $santri->naikqism = null;
                                    $santri->daftarnaikqism = 'Belum Mendaftar';
                                    $santri->tahap = null;
                                    $santri->status_tahap = null;
                                    $santri->deskripsitahap = null;
                                    $santri->jenispendaftar = null;
                                    $santri->tahap_pendaftaran_id = null;
                                    $santri->status_pendaftaran_id = null;
                                    $santri->ps_kadm_status_id = null;
                                    $santri->file_kk = null;
                                    $santri->file_akte = null;
                                    $santri->file_srs = null;
                                    $santri->file_ijz = null;
                                    $santri->file_kk = null;
                                    $santri->file_skt = null;
                                    $santri->file_skuasa = null;
                                    $santri->file_cvd = null;
                                    $santri->file_spkm = null;
                                    $santri->file_pka = null;
                                    $santri->file_ktmu = null;
                                    $santri->file_ktmp = null;

                                    $santri->save();

                                    Notification::make()
                                        ->success()
                                        ->title('Status Ananda telah diupdate')
                                        ->icon('heroicon-o-check-circle')
                                        // ->persistent()
                                        ->color('Success')
                                        // ->actions([
                                        //     Action::make('view')
                                        //         ->button(),
                                        //     Action::make('undo')
                                        //         ->color('secondary'),
                                        // ])
                                        ->send();
                                } else {
                                    Notification::make()
                                        // ->success()
                                        ->title('Belum saatnya naik qism')
                                        // ->persistent()
                                        ->color('Warning')
                                        ->send();
                                }
                            }
                        } else {
                            Notification::make()
                                // ->success()
                                ->title('Belum saatnya naik qism')
                                // ->persistent()
                                ->color('Warning')
                                ->send();
                        }
                    }))->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('batalnaikqism')
                    ->label(__('Batal Naik Qism'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor('danger')
                    ->modalHeading('Ubah Status Santri sebagai Batal Naik Qism?')
                    ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(function ($record) {

                        $santri = Santri::where('id', $record->santri_id)->first();
                        $santri->tahun_berjalan_id = null;
                        $santri->tahun_ajaran_id = null;
                        $santri->qism_id = null;
                        $santri->qism_detail_id = null;
                        $santri->kelas_id = null;
                        $santri->jenis_pendaftar_id = null;
                        $santri->qism = null;
                        $santri->qism_tujuan = null;
                        $santri->qism_detail = null;
                        $santri->qism_detail_tujuan = null;
                        $santri->kelas = null;
                        $santri->kelas_tujuan = null;
                        $santri->naikqism = null;
                        $santri->daftarnaikqism = null;
                        $santri->tahap = null;
                        $santri->status_tahap = null;
                        $santri->deskripsitahap = null;
                        $santri->jenispendaftar = null;
                        $santri->tahap_pendaftaran_id = null;
                        $santri->status_pendaftaran_id = null;
                        $santri->ps_kadm_status_id = null;
                        $santri->file_kk = null;
                        $santri->file_akte = null;
                        $santri->file_srs = null;
                        $santri->file_ijz = null;
                        $santri->file_kk = null;
                        $santri->file_skt = null;
                        $santri->file_skuasa = null;
                        $santri->file_cvd = null;
                        $santri->file_spkm = null;
                        $santri->file_pka = null;
                        $santri->file_ktmu = null;
                        $santri->file_ktmp = null;

                        $santri->save();

                        Notification::make()
                            ->success()
                            ->title('Status Ananda telah diupdate')
                            ->icon('heroicon-o-check-circle')
                            // ->persistent()
                            ->color('Success')
                            // ->actions([
                            //     Action::make('view')
                            //         ->button(),
                            //     Action::make('undo')
                            //         ->color('secondary'),
                            // ])
                            ->send();
                    }))->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('aktif')
                    ->label(__('Aktif'))
                    ->color('success')
                    ->visible(fn(): bool => auth()->user()->id == 1)
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-check-circle')
                    // ->modalIconColor('success')
                    // ->modalHeading('Simpan data santri tinggal kelas?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $statussantri = StatusSantri::where('santri_id', $record->santri_id)->first();
                            $statussantri->stat_santri_id = 3;
                            $statussantri->keterangan_status_santri_id = null;
                            $statussantri->save();

                            $statususer = User::where('id', $record->santri->walisantri->user->id)->first();
                            $statususer->is_active = 1;
                            $statususer->save();

                            Notification::make()
                                ->success()
                                ->title('Status Ananda telah diupdate')
                                ->icon('heroicon-o-check-circle')
                                // ->persistent()
                                ->color('Success')
                                // ->actions([
                                //     Action::make('view')
                                //         ->button(),
                                //     Action::make('undo')
                                //         ->color('secondary'),
                                // ])
                                ->send();
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUpdateStatusNaikQisms::route('/'),
            'create' => Pages\CreateUpdateStatusNaikQism::route('/create'),
            'view' => Pages\ViewUpdateStatusNaikQism::route('/{record}'),
            'edit' => Pages\EditUpdateStatusNaikQism::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {

        // $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
        // $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

        $tbpsb = AcuanPsb::where('is_active', true)->where('jenis_pendaftar_id', 2)->first();

        return parent::getEloquentQuery()->where('tahun_berjalan_id', $tbpsb->tahun_berjalan_id)
            ->whereIn('qism_id', Auth::user()->mudirqism);
    }
}
