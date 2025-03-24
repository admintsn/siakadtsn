<?php

namespace App\Filament\Admin\Resources\AcuanPsbResource\Pages;

use App\Filament\Admin\Resources\AcuanPsbResource;
use App\Filament\Admin\Resources\AcuanPsbResource\Widgets\DaftarQism;
use App\ListTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcuanPsbs extends ListRecords
{
    protected static string $resource = AcuanPsbResource::class;

    use ListTrait;

    protected function getHeaderWidgets(): array
    {
        return [
            DaftarQism::class,
        ];
    }
}
