<?php

namespace App\Filament\Tsn\Resources;

use App\Filament\Admin\Resources\DataSantriResource as ResourcesDataSantriResource;
use App\Filament\Tsn\Resources\DataSantriResource\Pages;
use App\Filament\Tsn\Resources\DataSantriResource\RelationManagers;
use App\Models\DataSantri;
use App\Models\KelasSantri;
use App\Models\SemesterBerjalan;
use App\Models\TahunBerjalan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DataSantriResource extends Resource
{
    protected static ?string $model = KelasSantri::class;

    // public static function canViewAny(): bool
    // {
    //     return auth()->user()->id == 1;
    // }

    protected static ?string $modelLabel = 'Data Santri';

    protected static ?string $pluralModelLabel = 'Data Santri';

    protected static ?string $navigationLabel = 'Data Santri';

    protected static ?int $navigationSort = 600000000;

    // protected static ?string $navigationIcon = 'heroicon-o-Qisms';

    // protected static ?string $cluster = ConfigLembaga::class;

    protected static ?string $navigationGroup = 'Data Santri';

    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {

        return ResourcesDataSantriResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return ResourcesDataSantriResource::table($table);
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
            'index' => Pages\ListDataSantris::route('/'),
            'create' => Pages\CreateDataSantri::route('/create'),
            'view' => Pages\ViewDataSantri::route('/{record}'),
            'edit' => Pages\EditDataSantri::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tahunberjalan = TahunBerjalan::where('is_active', 1)->first();

        if (Auth::user()->id === 1 or Auth::user()->id === 2) {
            return parent::getEloquentQuery()->where('tahun_berjalan_id', $tahunberjalan->id);
        } else {

            return parent::getEloquentQuery()->whereIn('qism_id', Auth::user()->mudirqism)->where('tahun_berjalan_id', $tahunberjalan->id);
        }
    }
}
