<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Clusters\ConfigPsb;
use App\Filament\Admin\Resources\TahapPendaftaranResource\Pages;
use App\Filament\Admin\Resources\TahapPendaftaranResource\RelationManagers;
use App\Filament\Exports\TahapPendaftaranExporter;
use App\Filament\Imports\TahapPendaftaranImporter;
use App\Models\TahapPendaftaran;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TahapPendaftaranResource extends Resource
{
    protected static ?string $model = TahapPendaftaran::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->id == 1;
    }

    protected static ?string $modelLabel = 'Tahap Pendaftaran';

    protected static ?string $pluralModelLabel = 'Tahap Pendaftaran';

    protected static ?string $navigationLabel = 'Tahap Pendaftaran';

    protected static ?int $navigationSort = 840000050;

    // protected static ?string $navigationIcon = 'heroicon-o-Qisms';

    protected static ?string $cluster = ConfigPsb::class;

    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form

            ->schema(static::TahapPendaftaranFormSchema());
    }

    public static function TahapPendaftaranFormSchema(): array
    {
        return [

            Section::make('Tahap Pendaftaran')
                ->schema([

                    Grid::make(4)
                        ->schema([

                            TextInput::make('tahap_pendaftaran')
                                ->label('Tahap Pendaftaran')
                                ->required()
                                ->unique(TahapPendaftaran::class, ignoreRecord: true),

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

                ColumnGroup::make('Tahap Pendaftaran', [

                    TextColumn::make('tahap_pendaftaran')
                        ->label('Tahap Pendaftaran')
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
            ->filters([
                QueryBuilder::make()
                    ->constraintPickerColumns(1)
                    ->constraints([

                        TextConstraint::make('tahap_pendaftaran')
                            ->label('Tahap Pendaftaran')
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
                    ->importer(TahapPendaftaranImporter::class)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

                ExportBulkAction::make()
                    ->label('Export')
                    ->exporter(TahapPendaftaranExporter::class),

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
            'index' => Pages\ListTahapPendaftarans::route('/'),
            'create' => Pages\CreateTahapPendaftaran::route('/create'),
            'view' => Pages\ViewTahapPendaftaran::route('/{record}'),
            'edit' => Pages\EditTahapPendaftaran::route('/{record}/edit'),
        ];
    }
}
