<?php

namespace App\Providers;

use App\Filament\MyLogoutResponse;
use App\Http\Responses\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\HeaderActionsPosition;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        $this->app->bind(LogoutResponseContract::class, MyLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->simple()
                ->labels([
                    'admin' => 'Admin',
                    'tsn' => 'TSN',
                    'walisantri' => 'Walisantri'
                ])
                ->visible(fn(): bool => auth()->user()->id == 1);
        });

        date_default_timezone_set('Asia/Jakarta');

        Table::configureUsing(function (Table $table): void {
            $table
                ->headerActions([], position: HeaderActionsPosition::Bottom)
                ->actions([], position: ActionsPosition::BeforeCells)
                ->filters([], layout: FiltersLayout::AboveContentCollapsible)
                ->deferFilters()
                ->filtersTriggerAction(
                    fn(Action $action) => $action
                        ->button()
                        ->label('Filter'),
                )
                ->emptyStateHeading('Belum ada data')
                ->emptyStateDescription('.')
                ->deferLoading()
                ->extremePaginationLinks();
        });
    }
}
