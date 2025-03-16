<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AcuanPsbResource\Pages;
use App\Filament\Admin\Resources\AcuanPsbResource\RelationManagers;
use App\Filament\Exports\AcuanPsbExporter;
use App\Filament\Imports\AcuanPsbImporter;
use App\Models\AcuanPsb;
use App\Models\JenisSoal;
use App\Models\KategoriSoal;
use App\Models\Kelas;
use App\Models\KelasSantri;
use App\Models\Mahad;
use App\Models\Mapel;
use App\Models\MapelQism;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\Qism;
use App\Models\QismDetail;
use App\Models\QismDetailHasKelas;
use App\Models\Sem;
use App\Models\Semester;
use App\Models\SemesterBerjalan;
use App\Models\StaffAdmin;
use App\Models\TahunAjaran;
use App\Models\TahunAjaranAktif;
use App\Models\TahunBerjalan;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
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
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Unique;

class AcuanPsbResource extends Resource
{
    protected static ?string $model = AcuanPsb::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->id == 1 || auth()->user()->id == 2;
    }

    public static function canCreate(): bool
    {
        return auth()->user()->id == 1;
    }

    // public static function canEdit(Model $record): bool
    // {
    //     return auth()->user()->id == 1;
    // }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->id == 1;
        // return false;
    }

    protected static ?string $modelLabel = 'Acuan PSB';

    protected static ?string $pluralModelLabel = 'Acuan PSB';

    protected static ?string $navigationLabel = 'Acuan PSB';

    protected static ?int $navigationSort = 400000000;

    // protected static ?string $navigationIcon = 'heroicon-o-Qisms';

    // protected static ?string $cluster = Imtihan::class;

    protected static ?string $navigationGroup = 'PSB';

    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form

            ->schema(static::AcuanPsbFormSchema());
    }

    public static function AcuanPsbFormSchema(): array
    {
        return [];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ColumnGroup::make('Acuan PSB', [

                    SelectColumn::make('qism_id')
                        ->label('Qism')
                        ->options(Qism::all()->pluck('abbr_qism', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

                    SelectColumn::make('qism_detail_id')
                        ->label('Qism Detail')
                        ->options(QismDetail::all()->pluck('abbr_qism_detail', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

                    SelectColumn::make('kelas_id')
                        ->label('Kelas')
                        ->options(Kelas::all()->pluck('kelas', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

                    SelectColumn::make('tahun_berjalan_id')
                        ->label('Tahun Berjalan')
                        ->options(TahunBerjalan::all()->pluck('tb', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

                    SelectColumn::make('semester_berjalan_id')
                        ->label('Semester Berjalan')
                        ->options(SemesterBerjalan::all()->pluck('semester_berjalan', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

                    SelectColumn::make('tahun_ajaran_id')
                        ->label('Tahun Ajaran')
                        ->options(TahunAjaran::all()->pluck('abbr_ta', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

                    SelectColumn::make('semester_id')
                        ->label('Semester')
                        ->options(Sem::all()->pluck('semester', 'id'))
                        ->sortable()
                        ->extraAttributes([
                            'style' => 'min-width:250px'
                        ]),

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
            ->groups([
                Group::make('qismDetail.id')
                    ->titlePrefixedWithLabel(false)
            ])
            ->defaultGroup('qismDetail.id')
            ->recordUrl(null)
            ->searchOnBlur()
            ->extremePaginationLinks()
            ->defaultPaginationPageOption(5)
            ->filters([
                QueryBuilder::make()
                    ->constraintPickerColumns(1)
                    ->constraints([

                        SelectConstraint::make('qism_id')
                            ->label('Qism')
                            ->options(Qism::all()->pluck('abbr_qism', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('qism_detail_id')
                            ->label('Qism Detail')
                            ->options(QismDetail::all()->pluck('abbr_qism_detail', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('kelas_id')
                            ->label('Kelas')
                            ->options(Kelas::all()->pluck('kelas', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('tahun_berjalan_id')
                            ->label('Tahun Berjalan')
                            ->options(TahunBerjalan::all()->pluck('tb', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('semester_berjalan_id')
                            ->label('Semester Berjalan')
                            ->options(SemesterBerjalan::all()->pluck('semester_berjalan', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(TahunAjaran::all()->pluck('abbr_ta', 'id'))
                            ->multiple()
                            ->nullable(),

                        SelectConstraint::make('semester_id')
                            ->label('Semester')
                            ->options(Sem::all()->pluck('semester', 'id'))
                            ->multiple()
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

                ImportAction::make()
                    ->label('Import')
                    ->importer(AcuanPsbImporter::class)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

                Tables\Actions\BulkAction::make('soal')
                    ->label(__('Soal'))
                    ->color('success')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-check-circle')
                    // ->modalIconColor('success')
                    // ->modalHeading('Tandai Data sebagai Soal?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $data['is_soal'] = 1;
                            $record->update($data);

                            return $record;

                            Notification::make()
                                ->success()
                                ->title('Data telah diubah')
                                ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('resetsoal')
                    ->label(__('Reset Soal'))
                    ->color('gray')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-arrow-path')
                    // ->modalIconColor('gray')
                    // ->modalHeading(new HtmlString('Reset tanda Soal?'))
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $data['is_soal'] = 0;
                            $record->update($data);

                            return $record;

                            Notification::make()
                                ->success()
                                ->title('Status Ananda telah diupdate')
                                ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('nilai')
                    ->label(__('Nilai'))
                    ->color('success')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-check-circle')
                    // ->modalIconColor('success')
                    // ->modalHeading('Tandai Data sebagai Nilai?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $data['is_nilai'] = 1;
                            $record->update($data);

                            return $record;

                            Notification::make()
                                ->success()
                                ->title('Data telah diubah')
                                ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('resetnilai')
                    ->label(__('Reset Nilai'))
                    ->color('gray')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-arrow-path')
                    // ->modalIconColor('gray')
                    // ->modalHeading(new HtmlString('Reset tanda Nilai?'))
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $data['is_nilai'] = 0;
                            $record->update($data);

                            return $record;

                            Notification::make()
                                ->success()
                                ->title('Status Ananda telah diupdate')
                                ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('soalnilai')
                    ->label(__('Soal & Nilai'))
                    ->color('success')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-check-circle')
                    // ->modalIconColor('success')
                    // ->modalHeading('Tandai Data sebagai Soal?')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $data['is_soal'] = 1;
                            $data['is_nilai'] = 1;
                            $record->update($data);

                            return $record;

                            Notification::make()
                                ->success()
                                ->title('Data telah diubah')
                                ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('resetsoalnilai')
                    ->label(__('Reset Soal & Nilai'))
                    ->color('gray')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-arrow-path')
                    // ->modalIconColor('gray')
                    // ->modalHeading(new HtmlString('Reset tanda Soal?'))
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            $data['is_soal'] = 0;
                            $data['is_nilai'] = 0;
                            $record->update($data);

                            return $record;

                            Notification::make()
                                ->success()
                                ->title('Status Ananda telah diupdate')
                                ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))->deselectRecordsAfterCompletion(),

                ExportBulkAction::make()
                    ->label('Export')
                    ->exporter(AcuanPsbExporter::class),

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
            'index' => Pages\ListAcuanPsbs::route('/'),
            'create' => Pages\CreateAcuanPsb::route('/create'),
            'view' => Pages\ViewAcuanPsb::route('/{record}'),
            'edit' => Pages\EditAcuanPsb::route('/{record}/edit'),
        ];
    }
}
