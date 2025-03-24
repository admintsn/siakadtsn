<?php

namespace App\Filament\Walisantri\Resources\DataSantriResource\Widgets;

use App\Models\KelasSantri;
use App\Models\Santri as ModelsSantri;
use App\Models\TahunBerjalan;
use App\Models\Walisantri;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use App\Models\Kesantrian\DataSantri;
use App\Models\QismDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use App\Models\Kelas;
use App\Models\KeteranganStatusSantri;
use App\Models\StatusSantri;
use App\Models\User;
use Filament\Forms\Components\Select;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kodepos;
use App\Models\MenginapTidak;
use App\Models\NismPerTahun;
use App\Models\Pendaftar;
use App\Models\PesanDaftar;
use App\Models\Provinsi;
use App\Models\Qism;
use App\Models\QismDetailHasKelas;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\WaktuDatangKembali;
use Carbon\Carbon;
use Closure;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Split as ComponentsSplit;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Str;
use stdClass;
use Filament\Tables\Grouping\Group as GroupingGroup;
use LaraZeus\Quantity\Components\Quantity;
use RalphJSmit\Filament\Components\Forms\Sidebar;

class FormulirKedatangan extends BaseWidget
{

    public static function canView(): bool
    {
        return auth()->user()->panelrole_id == 3;
    }

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {

        $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
        $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

        $ws = Walisantri::where('user_id', auth()->user()->id)->first();

        return $table
            ->heading('Formulir Tamu Kedatangan')
            // ->description('Silakan klik tombol "+ Isi Data Tamu" untuk mulai mengisi formulir')
            ->paginated(false)
            ->query(

                PesanDaftar::where('walisantri_id', $ws->id)->where('tahun_berjalan_id', $tahunberjalanaktif->id)
            )
            ->columns([
                Stack::make([
                    TextColumn::make('walisantri.ak_kep_kel_kk')
                        ->label('Nama')
                        ->size(TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Bold),

                    TextColumn::make('br1')
                        ->weight(FontWeight::Bold)
                        ->default(new HtmlString('</br>')),

                    TextColumn::make('menginapTidak.menginap_tidak')
                        ->label('Menginap Tidak')
                        ->size(TextColumn\TextColumnSize::Medium)
                        ->weight(FontWeight::Bold),

                    TextColumn::make('br12')
                        ->weight(FontWeight::Bold)
                        ->default(new HtmlString('</br>')),

                    TextColumn::make('tanggal_datang')
                        ->label('Tanggal Datang')
                        ->date()
                        ->description(fn($record): string => "Tanggal Datang:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('waktuDatang.waktu_datang_kembali')
                        ->label('Waktu Datang')
                        ->description(fn($record): string => "Waktu Datang:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('tanggal_kembali')
                        ->label('Tanggal Pulang')
                        ->date()
                        ->description(fn($record): string => "Tanggal Pulang:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('waktuKembali.waktu_datang_kembali')
                        ->label('Waktu Kembali')
                        ->description(fn($record): string => "Waktu Kembali:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('jumlah_hari')
                        ->label('Jumlah Hari')
                        ->description(fn($record): string => "Jumlah Hari:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('jumlahmenginap')
                        ->label('Jumlah Menginap')
                        ->description(fn($record): string => "Jumlah Malam Menginap:", position: 'above')
                        ->state(function (PesanDaftar $record): float {
                            return $record->jumlah_hari - 1;
                        }),

                    TextColumn::make('br2')
                        ->weight(FontWeight::Bold)
                        ->default(new HtmlString('</br>')),

                    TextColumn::make('putra')
                        ->label('Jumlah Tamu Putra')
                        ->description(fn($record): string => "Jumlah Tamu Putra:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('putri')
                        ->label('Jumlah Tamu Putri')
                        ->description(fn($record): string => "Jumlah Tamu Putri:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('total')
                        ->label('Total')
                        ->description(fn($record): string => "Total:", position: 'above')
                        ->state(function (PesanDaftar $record): float {
                            return $record->putra + $record->putri;
                        }),

                    TextColumn::make('br3')
                        ->weight(FontWeight::Bold)
                        ->default(new HtmlString('</br>')),

                    TextColumn::make('informasi_lain')
                        ->label('Informasi Lain')
                        ->description(fn($record): string => "Informasi Lain:", position: 'above')
                        ->default(new HtmlString('')),

                    TextColumn::make('br4')
                        ->weight(FontWeight::Bold)
                        ->default(new HtmlString('</br>')),

                    TextColumn::make('a')
                        ->default(new HtmlString('Apakah ingin mengubah data? Silakan klik tombol "Ubah Data Kedatangan" di bawah ini</br>')),

                ])
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->emptyStateHeading('Silakan klik tombol "+ Isi Data Tamu" untuk mulai mengisi formulir')
            ->emptyStateActions([
                Action::make('isidatatamu')
                    ->label('Isi Data Tamu')
                    ->icon('heroicon-m-plus')
                    ->button()
                    ->modalCloseButton(false)
                    ->modalHeading(' ')
                    ->modalWidth('full')
                    ->button()
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Batal'))
                    ->form([

                        Placeholder::make('')
                            ->content(function () {
                                $walisantri = Walisantri::where('user_id', auth()->user()->id)->first();
                                // $santri = ModelsSantri::where('id', $record->santri_id)->first();
                                return (new HtmlString('<div><p class="text-3xl"><strong>' . $walisantri->ak_kep_kel_kk . '</strong></p></div>'));
                            }),

                        Grid::make(2)
                            ->schema([

                                ComponentsSplit::make([

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

                                ComponentsSplit::make([

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

                                ComponentsSplit::make([

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

                                ComponentsSplit::make([

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
                            ->default(function () {
                                $walisantri = Walisantri::where('user_id', auth()->user()->id)->first();

                                return $walisantri->id;
                            })

                    ])
                    ->after(function ($data) {

                        $cekpd = PesanDaftar::where('tahun_berjalan_id', $data['tahun_berjalan_id'])->where('walisantri_id', $data['walisantri_id'])->count();

                        if ($cekpd == 0) {

                            $newpd = new PesanDaftar();

                            $newpd->putra = $data['putra'];
                            $newpd->putri = $data['putri'];
                            $newpd->waktu_datang = $data['putra'] + $data['putri'];
                            $newpd->menginap_tidak_id = $data['menginap_tidak_id'];
                            $newpd->tanggal_datang = $data['tanggal_datang'];
                            $newpd->w_datang = $data['w_datang'];
                            $newpd->tanggal_kembali = $data['tanggal_kembali'];
                            $newpd->w_kembali = $data['w_kembali'];
                            $newpd->informasi_lain = $data['informasi_lain'];
                            $newpd->jumlah_hari = $data['jumlah_hari'];
                            $newpd->menginap = $data['menginap'];
                            $newpd->tahun_berjalan_id = $data['tahun_berjalan_id'];
                            $newpd->walisantri_id = $data['walisantri_id'];

                            $newpd->save();

                            Notification::make()
                                ->success()
                                ->title('Alhamdulillah data tamu telah tersimpan')
                                ->color('success')
                                ->send();
                        } elseif ($cekpd != 0) {
                            Notification::make()
                                ->success()
                                ->title('Data tamu sudah ada')
                                ->color('danger')
                                ->send();
                        }
                    }),
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->label('Ubah Data Kedatangan')
                    ->modalCloseButton(false)
                    ->modalWidth('full')
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->button()
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Batal'))
                    ->form([

                        Placeholder::make('')
                            ->content(function () {
                                $walisantri = Walisantri::where('user_id', auth()->user()->id)->first();
                                // $santri = ModelsSantri::where('id', $record->santri_id)->first();
                                return (new HtmlString('<div><p class="text-3xl"><strong>' . $walisantri->ak_kep_kel_kk . '</strong></p></div>'));
                            }),

                        Grid::make(2)
                            ->schema([

                                ComponentsSplit::make([

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

                                ComponentsSplit::make([

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

                                ComponentsSplit::make([

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

                                ComponentsSplit::make([

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
                            ->default(function () {
                                $walisantri = Walisantri::where('user_id', auth()->user()->id)->first();

                                return $walisantri->id;
                            })

                    ])
                    ->after(function ($record) {

                        $editpd = PesanDaftar::where('id', $record->id)->first();
                        $editpd->waktu_datang = $record->putra + $record->putri;
                        $editpd->save();

                        Notification::make()
                            ->success()
                            ->title('Data berhasil diubah')
                            ->color('success')
                            ->send();
                        // }
                    }),

            ]);
    }
}
