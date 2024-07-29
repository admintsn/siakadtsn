<?php

namespace App\Filament\Pages;


use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{

    public function getColumns(): int | string | array
    {
        return 2;
    }

    protected static ?string $title = 'SiakadTSN';


    protected ?string $heading = "";
    protected ?string $subheading = "SiakadTSN";

    // protected static ?string $navigationLabel = '';

    use BaseDashboard\Concerns\HasFiltersForm;
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }
}
