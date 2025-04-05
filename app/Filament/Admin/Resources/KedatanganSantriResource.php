<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KedatanganSantriResource\Pages;
use App\Filament\Admin\Resources\KedatanganSantriResource\RelationManagers;
use App\Models\KedatanganSantri;
use App\Models\KelasSantri;
use App\Models\MenginapTidak;
use App\Models\PesanDaftar;
use App\Models\Santri;
use App\Models\TahunBerjalan;
use App\Models\WaktuDatangKembali;
use App\Models\Walisantri;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use LaraZeus\Quantity\Components\Quantity;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class KedatanganSantriResource extends Resource
{
    protected static ?string $model = PesanDaftar::class;

    // public static function canViewAny(): bool
    // {
    //     return auth()->user()->mudirqism !== null;
    // }

    protected static ?string $modelLabel = 'Data Tamu Kedatangan';

    protected static ?string $pluralModelLabel = 'Data Tamu Kedatangan';

    protected static ?string $navigationLabel = 'Data Tamu Kedatangan';

    protected static ?int $navigationSort = 200000000;

    // protected static ?string $navigationIcon = 'heroicon-o-Qisms';

    // protected static ?string $cluster = Kesantrian::class;

    protected static ?string $navigationGroup = 'PSB';

    // protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                    ->schema([

                        Placeholder::make('')
                            ->content(function ($record) {
                                $walisantri = Walisantri::where('id', $record->walisantri_id)->first();
                                // $santri = ModelsSantri::where('id', $record->santri_id)->first();
                                return (new HtmlString('<div><p class="text-3xl"><strong>' . $walisantri->ak_kep_kel_kk . '</strong></p></div>'));
                            }),

                        Grid::make(2)
                            ->schema([

                                Split::make([

                                    Fieldset::make('Jumlah Keluarga Datang')
                                        ->schema([

                                            Placeholder::make('')
                                                ->content(new HtmlString('<div><p class="text-lg">Jumlah ini kami perlukan untuk mempersiapkan tempat dan sajian bagi tamu</p></div>')),

                                            Quantity::make('putra')
                                                ->label('Jumlah Putra')
                                                ->numeric()
                                                ->hint('Termasuk Santri')
                                                ->hintColor('danger')
                                                ->required(),

                                            Quantity::make('putri')
                                                ->label('Jumlah Putri')
                                                ->numeric()
                                                ->hint('Termasuk Santriwati')
                                                ->hintColor('danger')
                                                ->required(),

                                        ])->columns(1),
                                ]),
                            ]),

                        Grid::make(2)
                            ->schema([

                                Split::make([

                                    Fieldset::make('Rencana Kedatangan')
                                        ->schema([

                                            ToggleButtons::make('menginap_tidak_id')
                                                ->label(new HtmlString('<div>Apakah berencana menginap di pondok?</br>Jika menginap di luar pondok (penginapan), maka pilih "Tidak Menginap di Pondok"</div>'))
                                                // ->hint('Pilih Waktu Pulang')
                                                ->inline()
                                                ->options(MenginapTidak::where('is_active', 1)->pluck('menginap_tidak', 'id'))
                                                ->required()
                                                ->live()
                                                ->afterstateupdated(function (Get $get, Set $set, $state) {
                                                    if ($state == 1) {

                                                        $set('tanggal_datang', null);
                                                        $set('w_datang', null);
                                                        $set('tanggal_kembali', null);
                                                        $set('w_kembali', null);
                                                        $set('jumlah_hari', null);
                                                        $set('menginap', null);
                                                        $set('status_menginap', 'Menginap di pondok');
                                                    } elseif ($state == 2) {
                                                        $set('tanggal_datang', null);
                                                        $set('w_datang', null);
                                                        $set('tanggal_kembali', $get('tanggal_datang'));
                                                        $set('w_kembali', null);
                                                        $set('jumlah_hari', null);
                                                        $set('menginap', null);
                                                        $set('status_menginap', 'Tidak menginap di pondok');
                                                    }
                                                }),

                                        ])->columns(1),
                                ]),
                            ]),

                        Placeholder::make('')
                            ->content(new HtmlString('<div><p class="text-lg">Silakan mengisi tanggal dan waktu di bawah ini sesuai dengan rencana kedatangan</p></div>'))
                            ->hidden(fn(Get $get) =>
                            $get('menginap_tidak_id') == null),

                        Grid::make(2)
                            ->schema([

                                Split::make([

                                    Fieldset::make(function (Get $get) {
                                        $mt = $get('menginap_tidak_id');

                                        if ($mt == 1) {
                                            return ('Menginap di pondok');
                                        } elseif ($mt == 2) {
                                            return ('Datang');
                                        }
                                    })
                                        ->schema([
                                            DatePicker::make('tanggal_datang')
                                                ->label(function (Get $get) {
                                                    $mt = $get('menginap_tidak_id');

                                                    if ($mt == 1) {
                                                        return ('Tanggal mulai menginap');
                                                    } elseif ($mt == 2) {
                                                        return ('Tanggal Datang');
                                                    }
                                                })
                                                // ->helperText('Format: bulan/hari/tahun')
                                                ->required()
                                                // ->format('Y-m-d')
                                                ->displayFormat('d M Y')
                                                ->minDate('2025-04-01')
                                                ->maxDate('2025-04-30')
                                                ->locale('id')
                                                ->closeOnDateSelection()
                                                ->live()
                                                ->native(false)
                                                // ->disabled(function (Get $get) {
                                                //     $mt = $get('menginap_tidak_id');

                                                //     if ($mt == 1) {
                                                //         return (false);
                                                //     } elseif ($mt == 2) {
                                                //         return (true);
                                                //     }
                                                // })
                                                // ->dehydrated()
                                                ->afterStateUpdated(function (Get $get, Set $set) {
                                                    $datang = Carbon::parse($get('tanggal_datang'));
                                                    $kembali = Carbon::parse($get('tanggal_kembali'));
                                                    $jumlahhari = $datang->diffInDays($kembali);

                                                    if ($get('menginap_tidak_id') == 2) {

                                                        if ($jumlahhari == 0) {
                                                            $set('jumlah_hari', $jumlahhari + 1);
                                                            $set('menginap', $jumlahhari);
                                                            $set('menginap_tidak_id', 2);
                                                            $set('status_menginap', 'Tidak menginap di pondok');
                                                            $set('w_datang', null);
                                                            $set('tanggal_kembali', $datang);
                                                            $set('w_kembali', null);
                                                        } elseif ($jumlahhari > 0) {
                                                            $set('jumlah_hari', $jumlahhari + 1);
                                                            $set('menginap', $jumlahhari);
                                                            $set('menginap_tidak_id', 1);
                                                            $set('status_menginap', 'Menginap di pondok');
                                                            $set('w_datang', null);
                                                            $set('tanggal_kembali', null);
                                                            $set('w_kembali', null);
                                                        }
                                                    }
                                                }),

                                            ToggleButtons::make('w_datang')
                                                ->label('Pilih Waktu Kedatangan')
                                                // ->hint('Pilih Waktu Kedatangan')
                                                ->inline()
                                                ->options(WaktuDatangKembali::where('is_active', 1)->pluck('waktu_datang_kembali', 'id'))
                                                ->required()
                                                ->live()
                                                ->disabled(function (Get $get) {
                                                    // $mt = $get('menginap_tidak_id');
                                                    $td = $get('tanggal_datang');

                                                    // if ($mt == 1) {
                                                    if ($td != null) {
                                                        return (false);
                                                    } elseif ($td == null) {
                                                        return (true);
                                                    }
                                                    // } elseif ($mt == 2) {
                                                    //     return (true);
                                                    // }
                                                }),
                                            // ->dehydrated(),

                                        ])->columns(1),


                                    Fieldset::make('Pulang')
                                        ->schema([
                                            DatePicker::make('tanggal_kembali')
                                                ->label('Tanggal Pulang')
                                                // ->helperText('Format: bulan/hari/tahun')
                                                ->required()
                                                ->date()
                                                // ->format('Y-m-d')
                                                ->displayFormat('d M Y')
                                                ->minDate(function (Get $get) {
                                                    if ($get('tanggal_datang') == null) {
                                                        return;
                                                    } elseif ($get('tanggal_datang') != null) {

                                                        if ($get('menginap_tidak_id') == 1) {

                                                            return (Carbon::parse($get('tanggal_datang'))->addDays(1));
                                                        } elseif ($get('menginap_tidak_id') == 2) {

                                                            return (Carbon::parse($get('tanggal_datang')));
                                                        }
                                                    }
                                                })
                                                ->maxDate(function (Get $get) {
                                                    if ($get('tanggal_datang') == null) {
                                                        return;
                                                    } elseif ($get('tanggal_datang') != null) {

                                                        if ($get('menginap_tidak_id') == 1) {

                                                            return ('2025-04-30');
                                                        } elseif ($get('menginap_tidak_id') == 2) {

                                                            return (Carbon::parse($get('tanggal_datang')));
                                                        }
                                                    }
                                                })
                                                ->after(function (Get $get) {
                                                    if ($get('menginap_tidak_id') == 1) {
                                                        return ('tanggal_datang');
                                                    }
                                                })
                                                ->locale('id')
                                                ->closeOnDateSelection()
                                                ->live()
                                                ->disabled(function (Get $get) {
                                                    // $mt = $get('menginap_tidak_id');
                                                    $wd = $get('w_datang');

                                                    // if ($mt == 1) {
                                                    if ($wd != null) {
                                                        return (false);
                                                    } elseif ($wd == null) {
                                                        return (true);
                                                    }
                                                    // } elseif ($mt == 2) {
                                                    //     return (true);
                                                    // }
                                                })
                                                ->native(false)
                                                // ->dehydrated()
                                                ->afterStateUpdated(function (Get $get, Set $set) {

                                                    $datang = Carbon::parse($get('tanggal_datang'));
                                                    $kembali = Carbon::parse($get('tanggal_kembali'));
                                                    $jumlahhari = $datang->diffInDays($kembali);

                                                    if ($jumlahhari == 0) {
                                                        $set('jumlah_hari', $jumlahhari + 1);
                                                        $set('menginap', $jumlahhari);
                                                        $set('menginap_tidak_id', 2);
                                                        $set('status_menginap', 'Tidak menginap di pondok');
                                                    } elseif ($jumlahhari != 0) {
                                                        $set('jumlah_hari', $jumlahhari + 1);
                                                        $set('menginap', $jumlahhari);
                                                        $set('menginap_tidak_id', 1);
                                                        $set('status_menginap', 'Menginap di pondok');
                                                    }
                                                }),

                                            ToggleButtons::make('w_kembali')
                                                ->label('Pilih Waktu Pulang')
                                                // ->hint('Pilih Waktu Pulang')
                                                ->inline()
                                                ->options(WaktuDatangKembali::where('is_active', 1)->pluck('waktu_datang_kembali', 'id'))
                                                ->required()
                                                ->live()
                                                ->disabled(function (Get $get) {
                                                    $tk = $get('tanggal_kembali');

                                                    if ($tk != null) {
                                                        return (false);
                                                    } elseif ($tk == null) {
                                                        return (true);
                                                    }
                                                })
                                                // ->dehydrated()
                                                ->afterStateUpdated(function (Get $get, Set $set) {

                                                    $datang = Carbon::parse($get('tanggal_datang'));
                                                    $kembali = Carbon::parse($get('tanggal_kembali'));
                                                    $jumlahhari = $datang->diffInDays($kembali);

                                                    if ($jumlahhari == 0) {
                                                        $set('jumlah_hari', $jumlahhari + 1);
                                                        $set('menginap', $jumlahhari);
                                                        $set('menginap_tidak_id', 2);
                                                        $set('status_menginap', 'Tidak menginap di pondok');
                                                    } elseif ($jumlahhari != 0) {
                                                        $set('jumlah_hari', $jumlahhari + 1);
                                                        $set('menginap', $jumlahhari);
                                                        $set('menginap_tidak_id', 1);
                                                        $set('status_menginap', 'Menginap di pondok');
                                                    }
                                                }),

                                        ])->columns(1),
                                ])->from('md'),

                            ])->hidden(fn(Get $get) =>
                            $get('menginap_tidak_id') == null),

                        Placeholder::make('')
                            ->content(new HtmlString('<div><p class="text-lg">Silakan isi kolom di bawah ini jika ada informasi lain yang ingin disampaikan ke panitia kedatangan</p></div>'))
                            ->hidden(fn(Get $get) =>
                            $get('menginap_tidak_id') == null),

                        Grid::make(2)
                            ->schema([

                                Textarea::make('informasi_lain')
                                    ->label('Informasi Lain')
                                    ->disabled(function (Get $get) {
                                        $wk = $get('jumlah_hari');

                                        if ($wk != null) {
                                            return (false);
                                        } elseif ($wk == null) {
                                            return (true);
                                        }
                                    }),

                            ])->hidden(fn(Get $get) =>
                            $get('menginap_tidak_id') == null),

                        Grid::make(2)
                            ->schema([

                                Split::make([

                                    Fieldset::make('Ringkasan')
                                        ->schema([

                                            // Grid::make(1)
                                            //     ->schema([

                                            //         TextInput::make('status_menginap')
                                            //             ->label('Status menginap')
                                            //             ->disabled(),

                                            //     ]),

                                            TextInput::make('jumlah_hari')
                                                ->label('Jumlah hari di pondok')
                                                ->disabled()
                                                ->dehydrated(),

                                            TextInput::make('menginap')
                                                ->label('Jumlah malam menginap di pondok')
                                                ->disabled()
                                                ->dehydrated(),

                                        ])->columns(2),
                                ]),
                            ])->hidden(fn(Get $get) =>
                            $get('menginap_tidak_id') == null),


                        Hidden::make('tahun_berjalan_id')
                            ->default(function () {
                                $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                return $tahunberjalanaktif->id;
                            }),

                        Hidden::make('walisantri_id')
                            ->default(function ($record) {
                                $walisantri = Walisantri::where('id', $record->walisantri_id)->first();

                                return $walisantri->id;
                            })

                    ])->compact()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ColumnGroup::make('Datang', [

                    TextColumn::make('tanggal_datang')
                        ->label('Tanggal Datang')
                        ->date()
                        ->alignEnd()
                        ->wrapHeader()
                        ->description(fn($record): string => "Tanggal Datang:", position: 'above')
                        ->sortable(query: function (Builder $query, string $direction): Builder {
                            return $query
                                ->orderBy('tanggal_datang', $direction)
                                ->orderBy('w_datang', $direction);
                        })
                        ->formatStateUsing(fn(Model $record, $state) => Carbon::parse($record->tanggal_datang)->locale('in')->dayName . ', ' .  Carbon::parse($state)->locale('in')->toFormattedDateString()),

                    TextColumn::make('waktuDatang.waktu_datang_kembali')
                        ->label('Waktu Datang')
                        ->wrapHeader()
                        ->description(fn($record): string => "Waktu Datang:", position: 'above')
                        ->default(new HtmlString('')),

                ]),

                ColumnGroup::make('Pulang', [

                    TextColumn::make('tanggal_kembali')
                        ->label('Tanggal Pulang')
                        ->date()
                        ->alignEnd()
                        ->wrapHeader()
                        ->description(fn($record): string => "Tanggal Pulang:", position: 'above')
                        ->formatStateUsing(fn(Model $record, $state) => Carbon::parse($record->tanggal_kembali)->locale('in')->dayName . ', ' .  Carbon::parse($state)->locale('in')->toFormattedDateString()),

                    TextColumn::make('waktuKembali.waktu_datang_kembali')
                        ->label('Waktu Pulang')
                        ->wrapHeader()
                        ->description(fn($record): string => "Waktu Pulang:", position: 'above')
                        ->default(new HtmlString('')),

                ]),

                ColumnGroup::make('Hari di pondok', [

                    TextColumn::make('jumlah_hari')
                        ->label('Jumlah Hari di Pondok')
                        ->wrapHeader()
                        ->description(fn($record): string => "Jumlah Hari:", position: 'above')
                        ->default(new HtmlString(''))
                        ->alignCenter(),

                    TextColumn::make('menginapTidak.menginap_tidak')
                        ->label('Status')
                        ->wrapHeader(),

                    TextColumn::make('jumlahmenginap')
                        ->label('Jumlah Malam Menginap di Pondok')
                        ->wrapHeader()
                        ->description(fn($record): string => "Jumlah Malam Menginap:", position: 'above')
                        ->state(function (PesanDaftar $record): float {
                            return $record->jumlah_hari - 1;
                        })
                        ->alignCenter(),

                ]),

                ColumnGroup::make('Jumlah tamu', [

                    TextColumn::make('putra')
                        ->label('Jumlah Tamu Putra')
                        ->description(fn($record): string => "Jumlah Tamu Putra:", position: 'above')
                        ->default(new HtmlString(''))
                        ->wrapHeader()
                        ->summarize(Sum::make()->label('Total Putra'))
                        ->alignCenter(),

                    TextColumn::make('putri')
                        ->label('Jumlah Tamu Putri')
                        ->description(fn($record): string => "Jumlah Tamu Putri:", position: 'above')
                        ->default(new HtmlString(''))
                        ->wrapHeader()
                        ->summarize(Sum::make()->label('Total Putri'))
                        ->alignCenter(),

                    TextColumn::make('waktu_datang')
                        ->label('Total')
                        ->description(fn($record): string => "Total:", position: 'above')
                        ->default(new HtmlString(''))
                        ->wrapHeader()
                        ->summarize(Sum::make()->label('Total Tamu'))
                        ->alignCenter(),

                ]),

                ColumnGroup::make('Informasi lain', [

                    TextColumn::make('informasi_lain')
                        ->label('Informasi Lain')
                        ->wrapHeader()
                        ->description(fn($record): string => "Informasi Lain:", position: 'above')
                        ->default(new HtmlString(''))
                        ->wrap(),

                ]),

                ColumnGroup::make('Data tamu', [

                    TextColumn::make('walisantri.ak_kep_kel_kk')
                        ->label('Walisantri')
                        ->wrapHeader(),

                    TextColumn::make('walisantri.ak_nama_kunyah')
                        ->label('Panggilan walisantri')
                        ->wrapHeader(),

                    TextColumn::make('walisantri.hp_komunikasi')
                        ->label('Hubungi Walisantri')
                        ->formatStateUsing(fn(string $state): string => __("󠀠"))
                        ->icon('heroicon-s-chat-bubble-left-right')
                        ->iconColor('info')
                        // ->circular()
                        ->alignCenter()
                        ->wrapHeader()
                        ->url(function ($record, $state) {

                            $walisantri = Walisantri::where('id', $record->walisantri_id)->first();

                            if ($walisantri->hp_komunikasi ?? null) {

                                $walisantri = Walisantri::where('id', $record->walisantri_id)->first();

                                return 'https://wa.me/62' . $walisantri->hp_komunikasi;
                            } elseif ($walisantri->hp_komunikasi ?? null) {
                                return '0';
                            }
                        })
                        ->badge()
                        ->color('info')
                        ->openUrlInNewTab(),

                    TextColumn::make('walisantri.santris.nama_lengkap')
                        ->label('Santri')
                        ->wrapHeader()
                        ->listWithLineBreaks()
                        ->bulleted(),

                    TextColumn::make('walisantri.santris.nama_panggilan')
                        ->label('Panggilan Santri')
                        ->wrapHeader()
                        ->listWithLineBreaks(),

                    TextColumn::make('walisantri.santris.kelassantri.qism_detail.abbr_qism_detail')
                        ->label('Qism')
                        ->wrapHeader()
                        ->listWithLineBreaks(),

                    TextColumn::make('walisantri.al_ak_kabupaten.kabupaten')
                        ->label('Asal Kabupaten')
                        ->wrapHeader(),

                    TextColumn::make('walisantri.al_ak_provinsi.provinsi')
                        ->label('Asal Provinsi')
                        ->wrapHeader(),
                ]),

            ])
            ->defaultSort(function (Builder $query, string $direction): Builder {
                return $query
                    ->orderBy('tanggal_datang', $direction)
                    ->orderBy('w_datang', $direction);
            })
            ->recordUrl(null)
            ->searchOnBlur()
            ->defaultPaginationPageOption('all')
            ->filters([
                SelectFilter::make('menginap_tidak_id')
                    ->label('Menginap atau tidak menginap')
                    ->options(MenginapTidak::where('is_active', true)->pluck('menginap_tidak', 'id')),

                Filter::make('tanggal_datang')
                    ->label('Filter Tanggal Datang')
                    ->form([
                        DatePicker::make('filter_tanggal_datang')
                            ->displayFormat('d M Y')
                            ->minDate('2025-04-01')
                            ->maxDate('2025-04-30')
                            ->locale('id')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['filter_tanggal_datang'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_datang', '=', $date),
                            );
                    }),

                Filter::make('tanggal_kembali')
                    ->label('Filter Tanggal Pulang')
                    ->form([
                        DatePicker::make('filter_tanggal_kembali')
                            ->displayFormat('d M Y')
                            ->minDate('2025-04-01')
                            ->maxDate('2025-04-30')
                            ->locale('id')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['filter_tanggal_kembali'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_kembali', '=', $date),
                            );
                    }),

                Filter::make('tanggal_datang')
                    ->label('Filter Range Tanggal Datang')
                    ->form([
                        DatePicker::make('filter_datang_dari')
                            ->displayFormat('d M Y')
                            ->minDate('2025-04-01')
                            ->maxDate('2025-04-30')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection(),
                        DatePicker::make('filter_datang_sampai')
                            ->displayFormat('d M Y')
                            ->minDate('2025-04-01')
                            ->maxDate('2025-04-30')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['filter_datang_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_datang', '>=', $date),
                            )
                            ->when(
                                $data['filter_datang_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_datang', '<=', $date),
                            );
                    }),

                Filter::make('tanggal_kembali')
                    ->label('Filter Range Tanggal Pulang')
                    ->form([
                        DatePicker::make('filter_kembali_dari')
                            ->displayFormat('d M Y')
                            ->minDate('2025-04-01')
                            ->maxDate('2025-04-30')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection(),
                        DatePicker::make('filter_kembali_sampai')
                            ->displayFormat('d M Y')
                            ->minDate('2025-04-01')
                            ->maxDate('2025-04-30')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['filter_kembali_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_kembali', '>=', $date),
                            )
                            ->when(
                                $data['filter_kembali_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_kembali', '<=', $date),
                            );
                    })


            ])
            ->headerActions([])
            ->actions([
                ActionGroup::make([
                    // Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(auth()->user()->id == 1),
                    // Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                ExportBulkAction::make(),

                Tables\Actions\BulkAction::make('hapusdatatamu')
                    ->label(__('Hapus Data Tamu'))
                    ->color('warning')
                    // ->requiresConfirmation()
                    // ->modalIcon('heroicon-o-exclamation-triangle')
                    // ->modalIconColor('danger')
                    // ->modalHeading('Ubah Status menjadi "Tidak diterima naik qism?"')
                    // ->modalDescription('Setelah klik tombol "Simpan", maka status akan berubah')
                    // ->modalSubmitActionLabel('Simpan')
                    ->action(fn(Collection $records, array $data) => $records->each(
                        function ($record) {

                            PesanDaftar::where('id', $record->id)
                                ->delete();

                            Notification::make()
                                ->success()
                                ->title('Data telah dihapus')
                                // ->persistent()
                                ->color('Success')
                                ->send();
                        }
                    ))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->checkIfRecordIsSelectableUsing(

                fn(Model $record): bool => auth()->user()->id == 1,
            );
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
            'index' => Pages\ListKedatanganSantris::route('/'),
            'create' => Pages\CreateKedatanganSantri::route('/create'),
            'view' => Pages\ViewKedatanganSantri::route('/{record}'),
            'edit' => Pages\EditKedatanganSantri::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {

        $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
        $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

        return parent::getEloquentQuery()->where('tahun_berjalan_id', $tahunberjalanaktif->id);
    }
}
