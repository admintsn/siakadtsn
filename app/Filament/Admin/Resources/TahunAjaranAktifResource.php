<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Clusters\ConfigLembaga;
use App\Filament\Admin\Resources\TahunAjaranAktifResource\Pages;
use App\Filament\Admin\Resources\TahunAjaranAktifResource\RelationManagers;
use App\Filament\Exports\TahunAjaranAktifExporter;
use App\Filament\Imports\TahunAjaranAktifImporter;
use App\Models\Kelas;
use App\Models\NismPerTahun;
use App\Models\Qism;
use App\Models\QismDetail;
use App\Models\QismDetailHasKelas;
use App\Models\Sem;
use App\Models\Semester;
use App\Models\SemesterBerjalan;
use App\Models\TahunAjaran;
use App\Models\TahunAjaranAktif;
use App\Models\TahunBerjalan;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class TahunAjaranAktifResource extends Resource
{
    protected static ?string $model = TahunAjaranAktif::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->id == 1;
    }

    protected static ?string $modelLabel = 'Tahun Ajaran Aktif';

    protected static ?string $pluralModelLabel = 'Tahun Ajaran Aktif';

    protected static ?string $navigationLabel = 'Tahun Ajaran Aktif';

    protected static ?int $navigationSort = 800000350;

    // protected static ?string $navigationIcon = 'heroicon-o-Qisms';

    protected static ?string $cluster = ConfigLembaga::class;

    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form

            ->schema(static::TahunAjaranAktifFormSchema());
    }

    public static function TahunAjaranAktifFormSchema(): array
    {
        return [

            Section::make('Tahun Ajaran Aktif')
                ->schema([

                    Grid::make(4)
                        ->schema([

                            ToggleButtons::make('qism_id')
                                ->label('Qism')
                                ->inline()
                                ->options(Qism::whereIsActive(1)->pluck('abbr_qism', 'id'))
                                ->live()
                                ->required(),

                        ]),

                    Grid::make(4)
                        ->schema([

                            Select::make('tahun_ajaran_id')
                                ->label('Tahun Ajaran')
                                ->options(TahunAjaran::whereIsActive(1)->pluck('abbr_ta', 'id'))
                                ->required(),

                        ]),

                    Grid::make(4)
                        ->schema([

                            ToggleButtons::make('semester_id')
                                ->label('Semester')
                                ->inline()
                                ->options(function (Get $get) {

                                    $qism = $get('qism_id');

                                    return (Semester::whereIsActive(1)->where('qism_id', $qism)->pluck('sem_id', 'sem_id'));
                                })
                                ->required(),

                        ]),

                ])
                ->compact(),

            Section::make('Rapor Ijazah')
                ->schema([

                    Grid::make(4)
                        ->schema([

                            ToggleButtons::make('is_rapor')
                                ->label('Rapor?')
                                ->boolean()
                                ->grouped(),

                            ToggleButtons::make('is_ijazah')
                                ->label('Ijazah?')
                                ->boolean()
                                ->grouped(),

                        ]),
                ])
                ->compact(),

            Section::make('Status')
                ->schema([

                    Grid::make(2)
                        ->schema([

                            ToggleButtons::make('is_active')
                                ->label('Active?')
                                ->boolean()
                                ->grouped()
                                ->default(true),

                        ]),
                ])->collapsible()
                ->compact(),

        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->copyable()
                    ->copyableState(function ($state) {
                        return ($state);
                    })
                    ->copyMessage('Tersalin')
                    ->sortable(),

                ColumnGroup::make('Tahun Ajaran Aktif', [

                    TextColumn::make('tahunBerjalan.tb')
                        ->label('Tahun Berjalan')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('qism.abbr_qism')
                        ->label('Qism')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('tahunAjaran.abbr_ta')
                        ->label('Tahun Ajaran')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                    TextColumn::make('sem.abbr_semester')
                        ->label('Semester')
                        ->searchable(isIndividual: true, isGlobal: false)
                        ->copyable()
                        ->copyableState(function ($state) {
                            return ($state);
                        })
                        ->copyMessage('Tersalin')
                        ->sortable(),

                ]),

                ColumnGroup::make('Rapor Ijazah', [

                    CheckboxColumn::make('is_rapor')
                        ->label('Rapor?')
                        ->sortable()
                        ->alignCenter(),

                    CheckboxColumn::make('is_ijazah')
                        ->label('Ijazah?')
                        ->sortable()
                        ->alignCenter(),

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
            ->filters([
                QueryBuilder::make()
                    ->constraintPickerColumns(1)
                    ->constraints([

                        SelectConstraint::make('qism_id')
                            ->label('Qism')
                            ->options(Qism::whereIsActive(1)->pluck('abbr_qism', 'id'))
                            ->nullable(),

                        SelectConstraint::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(TahunAjaran::whereIsActive(1)->pluck('abbr_ta', 'id'))
                            ->nullable(),

                        SelectConstraint::make('semester_id')
                            ->label('Semester')
                            ->options(Sem::whereIsActive(1)->pluck('abbr_semester', 'id'))
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),

                ImportAction::make()
                    ->label('Import')
                    ->importer(TahunAjaranAktifImporter::class)
            ])
            ->actions([
                ActionGroup::make([
                    ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make(),
                    ])->dropdown(false),
                    ReplicateAction::make()
                        ->form(

                            static::TahunAjaranAktifFormSchema()
                        )
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

                ExportBulkAction::make()
                    ->label('Export')
                    ->exporter(TahunAjaranAktifExporter::class),

                Tables\Actions\BulkAction::make('generatetahunajaranaktif')
                    ->label(__('Generate Tahun Ajaran Aktif'))
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

                            $cekdatats = TahunAjaranAktif::where('tahun_berjalan_id', $ts->id)
                                ->where('qism_id', $record->qism_id)
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

                                $TahunAjaranAktifbaru = new TahunAjaranAktif;
                                $TahunAjaranAktifbaru->qism_id = $record->qism_id;
                                $TahunAjaranAktifbaru->tahun_berjalan_id = $ts->id;
                                $TahunAjaranAktifbaru->tahun_ajaran_id = $getta->tahun_ajaran_id;
                                $TahunAjaranAktifbaru->semester_id = $getsem->sem_sel;
                                $TahunAjaranAktifbaru->is_active = 1;
                                $TahunAjaranAktifbaru->save();

                                $data['is_active'] = 0;
                                $record->update($data);

                                Notification::make()
                                    ->success()
                                    ->title('Data Tahun Ajaran Aktif generated')
                                    ->color('Success')
                                    ->send();
                            } elseif ($cekdatats != 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Data Tahun Ajaran Aktif sudah ada')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTahunAjaranAktifs::route('/'),
            'create' => Pages\CreateTahunAjaranAktif::route('/create'),
            'view' => Pages\ViewTahunAjaranAktif::route('/{record}'),
            'edit' => Pages\EditTahunAjaranAktif::route('/{record}/edit'),
        ];
    }
}
