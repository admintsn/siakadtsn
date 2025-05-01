<?php

namespace App\Filament\Admin\Resources\DataAlumniResource\Pages;

use App\Filament\Admin\Resources\DataAlumniResource;
use App\ListTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataAlumnis extends ListRecords
{
    protected static string $resource = DataAlumniResource::class;

    use ListTrait;
}
