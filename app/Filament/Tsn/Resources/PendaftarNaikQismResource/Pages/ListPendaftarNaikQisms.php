<?php

namespace App\Filament\Tsn\Resources\PendaftarNaikQismResource\Pages;

use App\Filament\Tsn\Resources\PendaftarNaikQismResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPendaftarNaikQisms extends ListRecords
{
    protected static string $resource = PendaftarNaikQismResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'Mendaftar' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('daftarnaikqism', 'Mendaftar')),
            'Belum Mendaftar' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('daftarnaikqism', 'Belum Mendaftar')),
        ];
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->deselectAllTableRecords();
    }
}
