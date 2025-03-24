<?php

namespace App\Filament\Walisantri\Resources\DataSantriResource\Widgets;

use App\Models\Cita;
use App\Models\Hafalan;
use App\Models\Hobi;
use App\Models\HubunganWali;
use App\Models\Jarakpp;
use App\Models\Jeniskelamin;
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
use App\Models\KebutuhanDisabilitas;
use App\Models\KebutuhanKhusus;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kewarganegaraan;
use App\Models\Kodepos;
use App\Models\MembiayaiSekolah;
use App\Models\MendaftarKeinginan;
use App\Models\NismPerTahun;
use App\Models\PekerjaanUtamaWalisantri;
use App\Models\Pendaftar;
use App\Models\PendidikanTerakhirWalisantri;
use App\Models\PenghasilanWalisantri;
use App\Models\Provinsi;
use App\Models\Qism;
use App\Models\QismDetailHasKelas;
use App\Models\Semester;
use App\Models\Statuskepemilikanrumah;
use App\Models\StatusTempatTinggal;
use App\Models\StatusWalisantri;
use App\Models\TahunAjaran;
use App\Models\Transpp;
use App\Models\Waktutempuh;
use App\Models\YaTidak;
use Carbon\Carbon;
use Closure;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
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
use Illuminate\Support\Str;
use stdClass;
use Filament\Tables\Grouping\Group as GroupingGroup;
use Schmeits\FilamentCharacterCounter\Forms\Components\TextInput as ComponentsTextInput;

class Santri extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {

        $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
        $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

        $santris = ModelsSantri::where('kartu_keluarga', Auth::user()->username)->pluck('id');

        return $table
            ->heading('Daftar Santri')
            ->description('Silakan menghubungi admin untuk perubahan data')
            ->paginated(false)
            ->query(

                KelasSantri::where('tahun_berjalan_id', $tahunberjalanaktif->id)->whereIn('santri_id', $santris)->whereHas('statussantri', function ($query) {
                    $query->where('stat_santri_id', 3);
                })
            )
            ->columns([
                Stack::make([
                    TextColumn::make('santri.nama_lengkap')
                        ->label('Nama')
                        ->size(TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Bold),

                    TextColumn::make('qism_detail.qism_detail')
                        ->label('Qism')
                        ->weight(FontWeight::Bold),

                    TextColumn::make('kelas.kelas')
                        ->label('Kelas')
                        ->weight(FontWeight::Bold),

                    TextColumn::make('tanggalupdate')
                        ->weight(FontWeight::Bold)
                        ->default(new HtmlString('</br>')),

                    TextColumn::make('walisantri.ws_emis4')
                        ->label('Status Data Walisantri')
                        ->default('Belum Lengkap')
                        ->size(TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Bold)
                        ->description(fn($record): string => "Status Data Walisantri:", position: 'above')
                        ->formatStateUsing(function (Model $record) {
                            $wsemis4 = Walisantri::where('user_id', Auth::user()->id)->first();
                            // dd($pendaftar->ps_kadm_status);
                            if ($wsemis4->ws_emis4 === null) {
                                return ('Belum lengkap');
                            } elseif ($wsemis4->ws_emis4 !== null) {
                                return ('Lengkap');
                            }
                        })
                        ->badge()
                        ->color(function (Model $record) {
                            $wsemis4 = Walisantri::where('user_id', Auth::user()->id)->first();
                            // dd($pendaftar->ps_kadm_status);
                            if ($wsemis4->ws_emis4 === null) {
                                return ('danger');
                            } elseif ($wsemis4->ws_emis4 !== null) {
                                return ('success');
                            }
                        }),

                    TextColumn::make('santri.s_emis4')
                        ->label('Status Data Santri')
                        ->default('Belum Lengkap')
                        ->size(TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Bold)
                        ->description(fn($record): string => "Status Data Santri:", position: 'above')
                        ->formatStateUsing(function (Model $record) {
                            $semis4 = ModelsSantri::where('id', $record->santri_id)->first();
                            // dd($pendaftar->ps_kadm_status);
                            if ($semis4->s_emis4 === null) {
                                return ('Belum lengkap');
                            } elseif ($semis4->s_emis4 !== null) {
                                return ('Lengkap');
                            }
                        })
                        ->badge()
                        ->color(function (Model $record) {
                            $semis4 = ModelsSantri::where('id', $record->santri_id)->first();
                            // dd($pendaftar->ps_kadm_status);
                            if ($semis4->s_emis4 === null) {
                                return ('danger');
                            } elseif ($semis4->s_emis4 !== null) {
                                return ('success');
                            }
                        }),

                    // TextColumn::make('a')
                    //     ->default(new HtmlString('</br>Silakan melengkapi data Walisantri dan Santri dengan klik tombol di bawah ini')),

                ])
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 2,
            ])
            ->actions([

                Tables\Actions\ViewAction::make()
                    ->label('Lihat Data')
                    ->modalCloseButton(false)
                    ->modalHeading(' ')
                    // ->modalDescription(new HtmlString('<div class="">
                    //                                         <p>Butuh bantuan?</p>
                    //                                         <p>Silakan mengubungi admin di bawah ini:</p>

                    //                                         <table class="table w-fit">
                    //                     <!-- head -->
                    //                     <thead>
                    //                         <tr class="border-tsn-header">
                    //                             <th class="text-tsn-header text-xs" colspan="2"></th>
                    //                         </tr>
                    //                     </thead>
                    //                     <tbody>
                    //                         <!-- row 1 -->
                    //                         <tr>
                    //                             <th><a href="https://wa.me/6282210862400"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                    //                             </svg>
                    //                             </a></th>
                    //                             <td class="text-xs"><a href="https://wa.me/6282210862400">WA Admin Putra (Abu Hammaam)</a></td>
                    //                         </tr>
                    //                         <tr>
                    //                             <th><a href="https://wa.me/6285236459012"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                    //                             </svg>
                    //                             </a></th>
                    //                             <td class="text-xs"><a href="https://wa.me/6285236459012">WA Admin Putra (Abu Fathimah Hendi)</a></td>
                    //                         </tr>
                    //                         <tr>
                    //                             <th><a href="https://wa.me/6281333838691"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                    //                             </svg>
                    //                             </a></th>
                    //                             <td class="text-xs"><a href="https://wa.me/6281333838691">WA Admin Putra (Akh Irfan)</a></td>
                    //                         </tr>
                    //                         <tr>
                    //                             <th><a href="https://wa.me/628175765767"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                    //                             </svg>
                    //                             </a></th>
                    //                             <td class="text-xs"><a href="https://wa.me/628175765767">WA Admin Putri</a></td>
                    //                         </tr>


                    //                     </tbody>
                    //                     </table>

                    //                                     </div>'))
                    ->modalWidth('full')
                    // ->stickyModalHeader()
                    ->button()
                    ->closeModalByClickingAway(false)
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Tutup'))
                    ->form([

                        Section::make()
                            ->schema([

                                Placeholder::make('')

                                    ->content(function (Model $record) {
                                        $santri = ModelsSantri::where('id', $record->santri_id)->first();
                                        return (new HtmlString('<div><p class="text-3xl"><strong>' . $santri->nama_lengkap . '</strong></p></div>'));
                                    }),

                                Placeholder::make('')
                                    ->content(function (Model $record) {
                                        $santri = ModelsSantri::where('id', $record->santri_id)->first();
                                        $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                        $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();


                                        $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                                        $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                                        $cekdatats = KelasSantri::where('tahun_berjalan_id', $tahunberjalanaktif->id)
                                            ->where('santri_id', $record->santri_id)->first();

                                        $abbrqism = Qism::where('id', $cekdatats->qism_id)->first();

                                        $abbrkelas = Kelas::where('id', $cekdatats->kelas_id)->first();




                                        return (new HtmlString('<div class="">
                                                <table class="table w-fit">
                            <!-- head -->
                            <thead>
                                <tr class="border-tsn-header">
                                    <th class="text-tsn-header text-xl" colspan="3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- row 1 -->
                                <tr>
                                    <th class="text-xl">Qism</th>
                                    <td class="text-xl">:</td>
                                    <td class="text-xl">' . $abbrqism->qism . '</td>
                                </tr>
                                <tr>
                                    <th class="text-xl">Kelas</th>
                                    <td class="text-xl">:</td>
                                    <td class="text-xl">' . $abbrkelas->kelas . '</td>
                                </tr>
                                <tr>
                                    <th class="text-xl">NISM</th>
                                    <td class="text-xl">:</td>
                                    <td class="text-xl">510035210133' . $santri->nism . '</td>
                                </tr>
            
            
            
                            </tbody>
                            </table>
            
                                            </div>'));
                                    }),

                            ]),

                        Tabs::make('Tabs')
                            ->tabs([

                                Tabs\Tab::make('Walisantri')
                                    ->schema([

                                        Group::make()
                                            ->relationship('walisantri')
                                            ->schema([
                                                //AYAH KANDUNG
                                                Section::make('A. AYAH KANDUNG')
                                                    ->schema([

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ak_nama_lengkap_sama_id')
                                                                    ->label('Apakah Nama sama dengan Nama Kepala Keluarga?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    // ->hidden(fn (Get $get) =>
                                                                    // $get('ak_status_id') != 1)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ak_nama_lengkap_sama_id') == 1) {
                                                                            $set('ak_nama_lengkap', $get('nama_kpl_kel_santri'));
                                                                            $set('ik_nama_lengkap_sama_id_id', 2);
                                                                            $set('ik_nama_lengkap', null);
                                                                            $set('w_nama_lengkap_sama_id_id', 2);
                                                                            $set('w_nama_lengkap', null);
                                                                        } else {
                                                                            $set('ak_nama_lengkap', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                                TextInput::make('ak_nama_lengkap')
                                                                    ->label('Nama Lengkap')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ak_nama_lengkap_sama') == 1)
                                                                    ->dehydrated(),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                            <p class="text-lg">A.01 STATUS AYAH KANDUNG</p>
                                                        </div>')),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ak_status_id')
                                                                    ->label('Status')
                                                                    // ->placeholder('Pilih Status')
                                                                    ->options(StatusWalisantri::whereIsActive(1)->pluck('status_walisantri', 'id'))
                                                                    ->required()
                                                                    ->inline()
                                                                    ->live()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ak_status_id') == 1) {
                                                                            $set('ak_kewarganegaraan_id', 1);
                                                                        }
                                                                    }),
                                                                // ->native(false),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('ak_nama_kunyah')
                                                                    ->label('Nama Hijroh/Islami/Panggilan')
                                                                    ->required(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                ToggleButtons::make('ak_kewarganegaraan_id')
                                                                    ->label('Kewarganegaraan')
                                                                    // ->placeholder('Pilih Kewarganegaraan')
                                                                    ->inline()
                                                                    ->default(1)
                                                                    ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                                                                    ->required()
                                                                    ->live(),
                                                                // ->native(false),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_kewarganegaraan_id') != 1 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([

                                                                ComponentsTextInput::make('ak_nik')
                                                                    ->label('NIK')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->regex('/^[0-9]*$/')
                                                                    ->length(16)
                                                                    ->maxLength(16)
                                                                    ->required(),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_kewarganegaraan_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('ak_asal_negara')
                                                                    ->label('Asal Negara')
                                                                    ->required(),


                                                                TextInput::make('ak_kitas')
                                                                    ->label('KITAS')
                                                                    ->hint('Nomor Izin Tinggal (KITAS)')
                                                                    ->hintColor('danger')
                                                                    ->required(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('ak_tempat_lahir')
                                                                    ->label('Tempat Lahir')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required(),


                                                                DatePicker::make('ak_tanggal_lahir')
                                                                    ->label('Tanggal Lahir')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->format('dd/mm/yyyy')
                                                                    ->displayFormat('d M Y')
                                                                    ->maxDate(now())
                                                                    // ->native(false)
                                                                    ->closeOnDateSelection(),
                                                            ]),

                                                        Grid::make(6)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                Select::make('ak_pend_terakhir_id')
                                                                    ->label('Pendidikan Terakhir')
                                                                    ->placeholder('Pilih Pendidikan Terakhir')
                                                                    ->options(PendidikanTerakhirWalisantri::whereIsActive(1)->pluck('pendidikan_terakhir_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),

                                                                Select::make('ak_pekerjaan_utama_id')
                                                                    ->label('Pekerjaan Utama')
                                                                    ->placeholder('Pilih Pekerjaan Utama')
                                                                    ->options(PekerjaanUtamaWalisantri::whereIsActive(1)->pluck('pekerjaan_utama_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),

                                                                Select::make('ak_pghsln_rt_id')
                                                                    ->label('Penghasilan Rata-Rata')
                                                                    ->placeholder('Pilih Penghasilan Rata-Rata')
                                                                    ->options(PenghasilanWalisantri::whereIsActive(1)->pluck('penghasilan_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                ToggleButtons::make('ak_tdk_hp_id')
                                                                    ->label('Apakah memiliki nomor handphone?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ak_tdk_hp_id') == 2) {
                                                                            $set('ak_nomor_handphone_sama_id', null);
                                                                            $set('ak_nomor_handphone', null);
                                                                        }
                                                                    }),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_tdk_hp_id') != 1 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([


                                                                ToggleButtons::make('ak_nomor_handphone_sama_id')
                                                                    ->label('Apakah nomor handphone sama dengan Pendaftar?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ak_nomor_handphone_sama_id') == 1) {
                                                                            $set('ak_nomor_handphone', $get('hp_komunikasi'));
                                                                            $set('ik_nomor_handphone_sama_id', 2);
                                                                            $set('ik_nomor_handphone', null);
                                                                            $set('w_nomor_handphone_sama_id', 2);
                                                                            $set('w_nomor_handphone', null);
                                                                        } else {
                                                                            $set('ak_nomor_handphone', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                                TextInput::make('ak_nomor_handphone')
                                                                    ->label('No. Handphone')
                                                                    ->helperText('Contoh: 82187782223')
                                                                    ->prefix('+62')
                                                                    ->tel()
                                                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ak_nomor_handphone_sama_id') == 1)
                                                                    ->dehydrated(),
                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                 <p class="text-lg">Kajian yang diikuti</p>
                                             </div>')),
                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                Textarea::make('ak_ustadz_kajian')
                                                                    ->label('Ustadz yang mengisi kajian')
                                                                    ->required(),

                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                TextArea::make('ak_tempat_kajian')
                                                                    ->label('Tempat kajian yang diikuti')
                                                                    ->required(),

                                                            ]),

                                                        // KARTU KELUARGA AYAH KANDUNG
                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([
                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                            <p class="text-lg">A.02 KARTU KELUARGA</p>
                                            <p class="text-lg">AYAH KANDUNG</p>
                                               </div>')),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                ToggleButtons::make('ak_kk_sama_pendaftar_id')
                                                                    ->label('Apakah KK dan Nama Kepala Keluarga sama dengan Pendaftar?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ak_kk_sama_pendaftar_id') == 1) {
                                                                            $set('ak_no_kk', $get('kartu_keluarga_santri'));
                                                                            $set('ak_kep_kel_kk', $get('nama_kpl_kel_santri'));
                                                                            $set('ik_kk_sama_pendaftar_id', 2);
                                                                            $set('ik_no_kk', null);
                                                                            $set('ik_kep_kel_kk', null);
                                                                            $set('w_kk_sama_pendaftar_id', 2);
                                                                            $set('w_no_kk', null);
                                                                            $set('w_kep_kel_kk', null);
                                                                        } else {
                                                                            $set('ak_no_kk', null);
                                                                            $set('ak_kep_kel_kk', null);
                                                                        }
                                                                    })->columnSpanFull(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                ComponentsTextInput::make('ak_no_kk')
                                                                    ->label('No. KK Ayah Kandung')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->length(16)
                                                                    ->maxLength(16)
                                                                    ->required()
                                                                    ->regex('/^[0-9]*$/')
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ak_kk_sama_pendaftar_id') == 1)
                                                                    ->dehydrated(),

                                                                TextInput::make('ak_kep_kel_kk')
                                                                    ->label('Nama Kepala Keluarga')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ak_kk_sama_pendaftar_id') == 1)
                                                                    ->dehydrated(),
                                                            ]),

                                                        // ALAMAT AYAH KANDUNG
                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([
                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                            <p class="text-lg">A.03 TEMPAT TINGGAL DOMISILI</p>
                                                            <p class="text-lg">AYAH KANDUNG</p>
                                                        </div>')),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ak_status_id') != 1)
                                                            ->schema([

                                                                ToggleButtons::make('al_ak_tgldi_ln_id')
                                                                    ->label('Apakah tinggal di luar negeri?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 1)
                                                            ->schema([

                                                                Textarea::make('al_ak_almt_ln')
                                                                    ->label('Alamat Luar Negeri')
                                                                    ->required(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([

                                                                Select::make('al_ak_stts_rmh_id')
                                                                    ->label('Status Kepemilikan Rumah')
                                                                    ->placeholder('Pilih Status Kepemilikan Rumah')
                                                                    ->options(Statuskepemilikanrumah::whereIsActive(1)->pluck('status_kepemilikan_rumah', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([

                                                                Select::make('al_ak_provinsi_id')
                                                                    ->label('Provinsi')
                                                                    ->placeholder('Pilih Provinsi')
                                                                    ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->afterStateUpdated(function (Set $set) {
                                                                        $set('al_ak_kabupaten_id', null);
                                                                        $set('al_ak_kecamatan_id', null);
                                                                        $set('al_ak_kelurahan_id', null);
                                                                        $set('al_ak_kodepos', null);
                                                                    }),

                                                                Select::make('al_ak_kabupaten_id')
                                                                    ->label('Kabupaten')
                                                                    ->placeholder('Pilih Kabupaten')
                                                                    ->options(fn(Get $get): Collection => Kabupaten::query()
                                                                        ->where('provinsi_id', $get('al_ak_provinsi_id'))
                                                                        ->pluck('kabupaten', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([

                                                                Select::make('al_ak_kecamatan_id')
                                                                    ->label('Kecamatan')
                                                                    ->placeholder('Pilih Kecamatan')
                                                                    ->options(fn(Get $get): Collection => Kecamatan::query()
                                                                        ->where('kabupaten_id', $get('al_ak_kabupaten_id'))
                                                                        ->pluck('kecamatan', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false),

                                                                Select::make('al_ak_kelurahan_id')
                                                                    ->label('Kelurahan')
                                                                    ->placeholder('Pilih Kelurahan')
                                                                    ->options(fn(Get $get): Collection => Kelurahan::query()
                                                                        ->where('kecamatan_id', $get('al_ak_kecamatan_id'))
                                                                        ->pluck('kelurahan', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                                                                        if (($get('al_ak_kodepos') ?? '') !== Str::slug($old)) {
                                                                            return;
                                                                        }

                                                                        $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                                                                        $state = $kodepos;

                                                                        foreach ($state as $state) {
                                                                            $set('al_ak_kodepos', Str::substr($state, 12, 5));
                                                                        }
                                                                    }),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('al_ak_kodepos')
                                                                    ->label('Kodepos')
                                                                    ->disabled()
                                                                    ->required()
                                                                    ->dehydrated(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([


                                                                TextInput::make('al_ak_rt')
                                                                    ->label('RT')
                                                                    ->helperText('Isi 0 jika tidak ada RT/RW')
                                                                    ->required()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_ak_kodepos') == null)
                                                                    ->numeric(),

                                                                TextInput::make('al_ak_rw')
                                                                    ->label('RW')
                                                                    ->helperText('Isi 0 jika tidak ada RT/RW')
                                                                    ->required()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_ak_kodepos') == null)
                                                                    ->numeric(),
                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_ak_tgldi_ln_id') != 2 ||
                                                                $get('ak_status_id') != 1)
                                                            ->schema([
                                                                Textarea::make('al_ak_alamat')
                                                                    ->label('Alamat')
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_ak_kodepos') == null)
                                                                    ->required(),
                                                            ]),

                                                    ])->compact(),


                                                // //IBU KANDUNG
                                                Section::make('B. IBU KANDUNG')
                                                    ->schema([

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ik_nama_lengkap_sama_id')
                                                                    ->label('Apakah Nama sama dengan Nama Kepala Keluarga?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ak_nama_lengkap_sama_id') != 2)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ik_nama_lengkap_sama_id') == 1) {
                                                                            $set('ik_nama_lengkap', $get('nama_kpl_kel_santri'));
                                                                            $set('w_nama_lengkap_sama_id', 2);
                                                                            $set('w_nama_lengkap', null);
                                                                        } else {
                                                                            $set('ik_nama_lengkap', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                                TextInput::make('ik_nama_lengkap')
                                                                    ->label('Nama Lengkap')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ik_nama_lengkap_sama_id') == 1)
                                                                    ->dehydrated(),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                            <p class="text-lg">B.01 STATUS IBU KANDUNG</p>
                                                        </div>')),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ik_status_id')
                                                                    ->label('Status')
                                                                    // ->placeholder('Pilih Status')
                                                                    ->options(StatusWalisantri::whereIsActive(1)->pluck('status_walisantri', 'id'))
                                                                    ->required()
                                                                    ->inline()
                                                                    ->live()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ik_status_id') == 1) {
                                                                            $set('ik_kewarganegaraan_id', 1);
                                                                        }
                                                                    }),
                                                                // ->native(false),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('ik_nama_kunyah')
                                                                    ->label('Nama Hijroh/Islami/Panggilan')
                                                                    ->required(),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                ToggleButtons::make('ik_kewarganegaraan_id')
                                                                    ->label('Kewarganegaraan')
                                                                    // ->placeholder('Pilih Kewarganegaraan')
                                                                    ->inline()
                                                                    ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                                                                    ->default(1),
                                                                // ->native(false)

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_kewarganegaraan_id') != 1 ||
                                                                $get('ik_status_id') != 1)
                                                            ->schema([

                                                                ComponentsTextInput::make('ik_nik')
                                                                    ->label('NIK')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->regex('/^[0-9]*$/')
                                                                    ->length(16)
                                                                    ->maxLength(16)
                                                                    ->required(),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_kewarganegaraan_id') != 2 ||
                                                                $get('ik_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('ik_asal_negara')
                                                                    ->label('Asal Negara')
                                                                    ->required(),

                                                                TextInput::make('ik_kitas')
                                                                    ->label('KITAS')
                                                                    ->hint('Nomor Izin Tinggal (KITAS)')
                                                                    ->hintColor('danger')
                                                                    ->required(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                TextInput::make('ik_tempat_lahir')
                                                                    ->label('Tempat Lahir')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required(),

                                                                DatePicker::make('ik_tanggal_lahir')
                                                                    ->label('Tanggal Lahir')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->format('dd/mm/yyyy')
                                                                    ->displayFormat('d M Y')
                                                                    ->maxDate(now())
                                                                    // ->native(false)
                                                                    ->closeOnDateSelection(),
                                                            ]),

                                                        Grid::make(6)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                Select::make('ik_pend_terakhir_id')
                                                                    ->label('Pendidikan Terakhir')
                                                                    ->placeholder('Pilih Pendidikan Terakhir')
                                                                    ->options(PendidikanTerakhirWalisantri::whereIsActive(1)->pluck('pendidikan_terakhir_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),

                                                                Select::make('ik_pekerjaan_utama_id')
                                                                    ->label('Pekerjaan Utama')
                                                                    ->placeholder('Pilih Pekerjaan Utama')
                                                                    ->options(PekerjaanUtamaWalisantri::whereIsActive(1)->pluck('pekerjaan_utama_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),

                                                                Select::make('ik_pghsln_rt_id')
                                                                    ->label('Penghasilan Rata-Rata')
                                                                    ->placeholder('Pilih Penghasilan Rata-Rata')
                                                                    ->options(PenghasilanWalisantri::whereIsActive(1)->pluck('penghasilan_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false),
                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                ToggleButtons::make('ik_tdk_hp_id')
                                                                    ->label('Apakah memiliki nomor handphone?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ik_nomor_handphone_sama_id')
                                                                    ->label('Apakah nomor handphone sama dengan Pendaftar?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))

                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_tdk_hp_id') != 1 ||
                                                                        $get('ak_nomor_handphone_sama_id') != 2 ||
                                                                        $get('ik_status_id') != 1)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ik_nomor_handphone_sama_id') == 1) {
                                                                            $set('ik_nomor_handphone', $get('hp_komunikasi'));
                                                                            $set('w_nomor_handphone', null);
                                                                        } else {
                                                                            $set('ik_nomor_handphone', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                                TextInput::make('ik_nomor_handphone')
                                                                    ->label('No. Handphone')
                                                                    ->helperText('Contoh: 82187782223')
                                                                    ->prefix('+62')
                                                                    ->tel()
                                                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ik_nomor_handphone_sama_id') == 1)
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_tdk_hp_id') != 1 ||
                                                                        $get('ik_status_id') != 1),
                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                 <p class="text-lg">Kajian yang diikuti</p>
                                             </div>')),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ik_kajian_sama_ak_id')
                                                                    ->label('Apakah kajian yang diikuti sama dengan Ayah?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_status_id') != 1)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ik_kajian_sama_ak_id') == 1) {
                                                                            $set('ik_ustadz_kajian', $get('ak_ustadz_kajian'));
                                                                            $set('ik_tempat_kajian', $get('ak_tempat_kajian'));
                                                                        } else {
                                                                            $set('ik_ustadz_kajian', null);
                                                                            $set('ik_tempat_kajian', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                Textarea::make('ik_ustadz_kajian')
                                                                    ->label('Ustadz yang mengisi kajian')
                                                                    ->required(),

                                                            ]),

                                                        Grid::make(2)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1)
                                                            ->schema([

                                                                TextArea::make('ik_tempat_kajian')
                                                                    ->label('Tempat kajian yang diikuti')
                                                                    ->required(),

                                                            ]),

                                                        // KARTU KELUARGA IBU KANDUNG
                                                        Grid::make(2)
                                                            ->schema([
                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                <p class="text-lg">B.02 KARTU KELUARGA</p>
                                                <p class="text-lg">IBU KANDUNG</p>
                                                </div>')),

                                                            ])

                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_status_id') != 1),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('ik_kk_sama_ak_id')
                                                                    ->label('Apakah KK Ibu Kandung sama dengan KK Ayah Kandung?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(function (Get $get) {

                                                                        if ($get('ak_status_id') != 1) {

                                                                            return ([
                                                                                2 => 'Tidak',
                                                                            ]);
                                                                        } else {
                                                                            return ([
                                                                                1 => 'Ya',
                                                                                2 => 'Tidak',
                                                                            ]);
                                                                        }
                                                                    })
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                                        $sama = $get('ik_kk_sama_ak_id');
                                                                        $set('al_ik_sama_ak_id', $sama);

                                                                        if ($get('ik_kk_sama_ak_id') == 1) {
                                                                            $set('al_ik_sama_ak_id', 1);
                                                                        }
                                                                    })
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_status_id') != 1),

                                                                ToggleButtons::make('al_ik_sama_ak_id')
                                                                    ->label('Alamat sama dengan Ayah Kandung')
                                                                    ->helperText('Untuk mengubah alamat, silakan mengubah status KK Ibu kandung')
                                                                    ->disabled()
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_status_id') != 1),

                                                                ToggleButtons::make('ik_kk_sama_pendaftar_id')
                                                                    ->label('Apakah KK dan Nama Kepala Keluarga sama dengan Pendaftar?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('ak_kk_sama_pendaftar_id') != 2 ||
                                                                        $get('ik_status_id') != 1)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('ik_kk_sama_pendaftar_id') == 1) {
                                                                            $set('ik_no_kk', $get('kartu_keluarga_santri'));
                                                                            $set('ik_kep_kel_kk', $get('nama_kpl_kel_santri'));
                                                                            $set('w_kk_sama_pendaftar_id', 2);
                                                                            $set('w_no_kk', null);
                                                                            $set('w_kep_kel_kk', null);
                                                                        } else {
                                                                            $set('ik_no_kk', null);
                                                                            $set('ik_kep_kel_kk', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                            ]),

                                                        Grid::make(4)
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ik_kk_sama_ak_id') != 2 ||
                                                                $get('ik_status_id') != 1)
                                                            ->schema([

                                                                ComponentsTextInput::make('ik_no_kk')
                                                                    ->label('No. KK Ibu Kandung')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->length(16)
                                                                    ->maxLength(16)
                                                                    ->regex('/^[0-9]*$/')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ik_kk_sama_pendaftar_id') == 1)
                                                                    ->dehydrated(),

                                                                TextInput::make('ik_kep_kel_kk')
                                                                    ->label('Nama Kepala Keluarga')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('ik_kk_sama_pendaftar_id') == 1)
                                                                    ->dehydrated(),

                                                            ]),


                                                        // ALAMAT IBU KANDUNG
                                                        Grid::make(2)
                                                            ->schema([
                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                            <p class="text-lg">B.03 TEMPAT TINGGAL DOMISILI</p>
                                                            <p class="text-lg">IBU KANDUNG</p>
                                                        </div>')),
                                                            ])->hidden(fn(Get $get) =>
                                                            $get('ik_kk_sama_ak_id') == null ||
                                                                $get('ik_kk_sama_ak_id') != 2 ||
                                                                $get('ik_status_id') != 1),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('al_ik_tgldi_ln_id')
                                                                    ->label('Apakah tinggal di luar negeri?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Textarea::make('al_ik_almt_ln')
                                                                    ->label('Alamat Luar Negeri')
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 1 ||
                                                                        $get('ik_status_id') != 1),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('al_ik_stts_rmh_id')
                                                                    ->label('Status Kepemilikan Rumah')
                                                                    ->placeholder('Pilih Status Kepemilikan Rumah')
                                                                    ->options(Statuskepemilikanrumah::whereIsActive(1)->pluck('status_kepemilikan_rumah', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('al_ik_provinsi_id')
                                                                    ->label('Provinsi')
                                                                    ->placeholder('Pilih Provinsi')
                                                                    ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1)
                                                                    ->afterStateUpdated(function (Set $set) {
                                                                        $set('al_ik_kabupaten_id', null);
                                                                        $set('al_ik_kecamatan_id', null);
                                                                        $set('al_ik_kelurahan_id', null);
                                                                        $set('al_ik_kodepos', null);
                                                                    }),

                                                                Select::make('al_ik_kabupaten_id')
                                                                    ->label('Kabupaten')
                                                                    ->placeholder('Pilih Kabupaten')
                                                                    ->options(fn(Get $get): Collection => Kabupaten::query()
                                                                        ->where('provinsi_id', $get('al_ik_provinsi_id'))
                                                                        ->pluck('kabupaten', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('al_ik_kecamatan_id')
                                                                    ->label('Kecamatan')
                                                                    ->placeholder('Pilih Kecamatan')
                                                                    ->options(fn(Get $get): Collection => Kecamatan::query()
                                                                        ->where('kabupaten_id', $get('al_ik_kabupaten_id'))
                                                                        ->pluck('kecamatan', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                                Select::make('al_ik_kelurahan_id')
                                                                    ->label('Kelurahan')
                                                                    ->placeholder('Pilih Kelurahan')
                                                                    ->options(fn(Get $get): Collection => Kelurahan::query()
                                                                        ->where('kecamatan_id', $get('al_ik_kecamatan_id'))
                                                                        ->pluck('kelurahan', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1)
                                                                    ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                                                                        if (($get('al_ik_kodepos') ?? '') !== Str::slug($old)) {
                                                                            return;
                                                                        }

                                                                        $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                                                                        $state = $kodepos;

                                                                        foreach ($state as $state) {
                                                                            $set('al_ik_kodepos', Str::substr($state, 12, 5));
                                                                        }
                                                                    }),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                TextInput::make('al_ik_kodepos')
                                                                    ->label('Kodepos')
                                                                    ->disabled()
                                                                    ->required()
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([


                                                                TextInput::make('al_ik_rt')
                                                                    ->label('RT')
                                                                    ->helperText('Isi 0 jika tidak ada RT/RW')
                                                                    ->required()
                                                                    ->numeric()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_ik_kodepos') == null)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                                TextInput::make('al_ik_rw')
                                                                    ->label('RW')
                                                                    ->helperText('Isi 0 jika tidak ada RT/RW')
                                                                    ->required()
                                                                    ->numeric()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_ik_kodepos') == null)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Textarea::make('al_ik_alamat')
                                                                    ->label('Alamat')
                                                                    ->required()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_ik_kodepos') == null)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ik_kk_sama_ak_id') != 2 ||
                                                                        $get('al_ik_tgldi_ln_id') != 2 ||
                                                                        $get('ik_status_id') != 1),

                                                            ]),

                                                    ])->compact(),

                                                // WALI

                                                Section::make('C. WALI')
                                                    ->schema([

                                                        Grid::make(2)
                                                            ->schema([

                                                                ToggleButtons::make('w_status_id')
                                                                    ->label('Status')
                                                                    // ->placeholder('Pilih Status')
                                                                    ->inline()
                                                                    ->options(function (Get $get) {

                                                                        if (($get('ak_status_id') == 1 && $get('ik_status_id') == 1)) {
                                                                            return ([
                                                                                1 => 'Sama dengan ayah kandung',
                                                                                2 => 'Sama dengan ibu kandung',
                                                                                3 => 'Lainnya'
                                                                            ]);
                                                                        } elseif (($get('ak_status_id') == 1 && $get('ik_status_id') !== 1)) {
                                                                            return ([
                                                                                1 => 'Sama dengan ayah kandung',
                                                                                3 => 'Lainnya'
                                                                            ]);
                                                                        } elseif (($get('ak_status_id') !== 1 && $get('ik_status_id') == 1)) {
                                                                            return ([
                                                                                2 => 'Sama dengan ibu kandung',
                                                                                3 => 'Lainnya'
                                                                            ]);
                                                                        } elseif (($get('ak_status_id') !== 1 && $get('ik_status_id') !== 1)) {
                                                                            return ([
                                                                                3 => 'Lainnya'
                                                                            ]);
                                                                        }
                                                                    })
                                                                    ->required()
                                                                    ->live()
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('w_status_id') == 3) {
                                                                            $set('w_kewarganegaraan_id', 1);
                                                                        }
                                                                    }),
                                                                // ->native(false),

                                                            ]),

                                                        Grid::make(2)

                                                            ->hidden(fn(Get $get) =>
                                                            $get('w_status_id') != 3)
                                                            ->schema([

                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                            <p class="text-lg">C.01 STATUS WALI</p>
                                                        </div>')),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('w_hubungan_id')
                                                                    ->label('Hubungan wali dengan calon santri')
                                                                    ->placeholder('Pilih Hubungan')
                                                                    ->options(HubunganWali::whereIsActive(1)->pluck('hubungan_wali', 'id'))
                                                                    ->required()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('w_nama_lengkap_sama_id')
                                                                    ->label('Apakah Nama sama dengan Nama Kepala Keluarga?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3 ||
                                                                        $get('ak_nama_lengkap_sama_id') != 2 ||
                                                                        $get('ik_nama_lengkap_sama_id') != 2)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('w_nama_lengkap_sama_id') == 1) {
                                                                            $set('w_nama_lengkap', $get('nama_kpl_kel_santri'));
                                                                        } else {
                                                                            $set('w_nama_lengkap', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                                TextInput::make('w_nama_lengkap')
                                                                    ->label('Nama Lengkap')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('w_nama_lengkap_sama_id') == 1)
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                TextInput::make('w_nama_kunyah')
                                                                    ->label('Nama Hijroh/Islami/Panggilan')
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('w_kewarganegaraan_id')
                                                                    ->label('Kewarganegaraan')
                                                                    // ->placeholder('Pilih Kewarganegaraan')
                                                                    ->inline()
                                                                    ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                                                                    ->default(1)
                                                                    ->live()
                                                                    // ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ComponentsTextInput::make('w_nik')
                                                                    ->label('NIK')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->regex('/^[0-9]*$/')
                                                                    ->length(16)
                                                                    ->maxLength(16)
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_kewarganegaraan_id') != 1 ||
                                                                        $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                TextInput::make('w_asal_negara')
                                                                    ->label('Asal Negara')
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_kewarganegaraan_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                                TextInput::make('w_kitas')
                                                                    ->label('KITAS')
                                                                    ->hint('Nomor Izin Tinggal (KITAS)')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_kewarganegaraan_id') != 2 ||
                                                                        $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                TextInput::make('w_tempat_lahir')
                                                                    ->label('Tempat Lahir')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                                DatePicker::make('w_tanggal_lahir')
                                                                    ->label('Tanggal Lahir')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->maxDate(now())
                                                                    ->required()
                                                                    // ->format('dd/mm/yyyy')
                                                                    ->displayFormat('d M Y')
                                                                    // ->native(false)
                                                                    ->closeOnDateSelection()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(6)
                                                            ->schema([

                                                                Select::make('w_pend_terakhir_id')
                                                                    ->label('Pendidikan Terakhir')
                                                                    ->placeholder('Pilih Pendidikan Terakhir')
                                                                    ->options(PendidikanTerakhirWalisantri::whereIsActive(1)->pluck('pendidikan_terakhir_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                                Select::make('w_pekerjaan_utama_id')
                                                                    ->label('Pekerjaan Utama')
                                                                    ->placeholder('Pilih Pekerjaan Utama')
                                                                    ->options(PekerjaanUtamaWalisantri::whereIsActive(1)->pluck('pekerjaan_utama_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                                Select::make('w_pghsln_rt_id')
                                                                    ->label('Penghasilan Rata-Rata')
                                                                    ->placeholder('Pilih Penghasilan Rata-Rata')
                                                                    ->options(PenghasilanWalisantri::whereIsActive(1)->pluck('penghasilan_walisantri', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('w_tdk_hp_id')
                                                                    ->label('Apakah memiliki nomor handphone?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('w_nomor_handphone_sama_id')
                                                                    ->label('Apakah nomor handphone sama dengan Pendaftar?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_tdk_hp_id') != 1 ||
                                                                        $get('ak_nomor_handphone_sama_id') != 2 ||
                                                                        $get('ik_nomor_handphone_sama_id') != 2 ||
                                                                        $get('w_status_id') != 3)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('w_nomor_handphone_sama_id') == 1) {
                                                                            $set('w_nomor_handphone', $get('hp_komunikasi'));
                                                                        } else {
                                                                            $set('w_nomor_handphone', null);
                                                                        }
                                                                    })->columnSpanFull(),

                                                                TextInput::make('w_nomor_handphone')
                                                                    ->label('No. Handphone')
                                                                    ->helperText('Contoh: 82187782223')
                                                                    // ->mask('82187782223')
                                                                    ->prefix('+62')
                                                                    ->tel()
                                                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('w_nomor_handphone_sama_id') == 1)
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_tdk_hp_id') != 1 ||
                                                                        $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                 <p class="text-lg">Kajian yang diikuti</p>
                                             </div>'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Textarea::make('w_ustadz_kajian')
                                                                    ->label('Ustadz yang mengisi kajian')
                                                                    ->required()
                                                                    // ->default('4232')
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                TextArea::make('w_tempat_kajian')
                                                                    ->label('Tempat kajian yang diikuti')
                                                                    ->required()
                                                                    // ->default('4232')
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        // KARTU KELUARGA WALI
                                                        Grid::make(2)
                                                            ->schema([
                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                            <p class="text-lg">C.02 KARTU KELUARGA</p>
                                            <p class="text-lg">WALI</p>
                                         </div>'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('w_kk_sama_pendaftar_id')
                                                                    ->label('Apakah KK dan Nama Kepala Keluarga sama dengan Pendaftar?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('ak_kk_sama_pendaftar_id') != 2 ||
                                                                        $get('ik_kk_sama_pendaftar_id') != 2 ||
                                                                        $get('w_status_id') != 3)
                                                                    ->afterStateUpdated(function (Get $get, Set $set) {

                                                                        if ($get('w_kk_sama_pendaftar_id') == 1) {
                                                                            $set('w_no_kk', $get('kartu_keluarga_santri'));
                                                                            $set('w_kep_kel_kk', $get('nama_kpl_kel_santri'));
                                                                        } else {
                                                                            $set('w_no_kk', null);
                                                                            $set('w_kep_kel_kk', null);
                                                                        }
                                                                    })->columnSpanFull(),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ComponentsTextInput::make('w_no_kk')
                                                                    ->label('No. KK Wali')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->length(16)
                                                                    ->maxLength(16)
                                                                    ->required()
                                                                    ->regex('/^[0-9]*$/')
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('w_kk_sama_pendaftar_id') == 1)
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                                TextInput::make('w_kep_kel_kk')
                                                                    ->label('Nama Kepala Keluarga')
                                                                    ->hint('Isi sesuai dengan KK')
                                                                    ->hintColor('danger')
                                                                    ->required()
                                                                    // ->disabled(fn (Get $get) =>
                                                                    // $get('w_kk_sama_pendaftar_id') == 1)
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),
                                                            ]),


                                                        // ALAMAT WALI
                                                        Grid::make(2)
                                                            ->schema([
                                                                Placeholder::make('')
                                                                    ->content(new HtmlString('<div class="border-b">
                                                            <p class="text-lg">C.03 TEMPAT TINGGAL DOMISILI</p>
                                                            <p class="text-lg">WALI</p>
                                                        </div>'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                ToggleButtons::make('al_w_tgldi_ln_id')
                                                                    ->label('Apakah tinggal di luar negeri?')
                                                                    ->live()
                                                                    ->inline()
                                                                    ->grouped()
                                                                    ->boolean()
                                                                    ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Textarea::make('al_w_almt_ln')
                                                                    ->label('Alamat Luar Negeri')
                                                                    ->required()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 1),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('al_w_stts_rmh_id')
                                                                    ->label('Status Kepemilikan Rumah')
                                                                    ->placeholder('Pilih Status Kepemilikan Rumah')
                                                                    ->options(Statuskepemilikanrumah::whereIsActive(1)->pluck('status_kepemilikan_rumah', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('al_w_provinsi_id')
                                                                    ->label('Provinsi')
                                                                    ->placeholder('Pilih Provinsi')
                                                                    ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3)
                                                                    ->afterStateUpdated(function (Set $set) {
                                                                        $set('al_w_kabupaten_id', null);
                                                                        $set('al_w_kecamatan_id', null);
                                                                        $set('al_w_kelurahan_id', null);
                                                                        $set('al_w_kodepos', null);
                                                                    }),

                                                                Select::make('al_w_kabupaten_id')
                                                                    ->label('Kabupaten')
                                                                    ->placeholder('Pilih Kabupaten')
                                                                    ->options(fn(Get $get): Collection => Kabupaten::query()
                                                                        ->where('provinsi_id', $get('al_w_provinsi_id'))
                                                                        ->pluck('kabupaten', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                Select::make('al_w_kecamatan_id')
                                                                    ->label('Kecamatan')
                                                                    ->placeholder('Pilih Kecamatan')
                                                                    ->options(fn(Get $get): Collection => Kecamatan::query()
                                                                        ->where('kabupaten_id', $get('al_w_kabupaten_id'))
                                                                        ->pluck('kecamatan', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                                Select::make('al_w_kelurahan_id')
                                                                    ->label('Kelurahan')
                                                                    ->placeholder('Pilih Kelurahan')
                                                                    ->options(fn(Get $get): Collection => Kelurahan::query()
                                                                        ->where('kecamatan_id', $get('al_w_kecamatan_id'))
                                                                        ->pluck('kelurahan', 'id'))
                                                                    // ->searchable()
                                                                    ->required()
                                                                    ->live()
                                                                    ->native(false)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3)
                                                                    ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                                                                        if (($get('al_w_kodepos') ?? '') !== Str::slug($old)) {
                                                                            return;
                                                                        }

                                                                        $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                                                                        $state = $kodepos;

                                                                        foreach ($state as $state) {
                                                                            $set('al_w_kodepos', Str::substr($state, 12, 5));
                                                                        }
                                                                    }),

                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([

                                                                TextInput::make('al_w_kodepos')
                                                                    ->label('Kodepos')
                                                                    ->disabled()
                                                                    ->required()
                                                                    ->dehydrated()
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),
                                                            ]),

                                                        Grid::make(4)
                                                            ->schema([


                                                                TextInput::make('al_w_rt')
                                                                    ->label('RT')
                                                                    ->helperText('Isi 0 jika tidak ada RT/RW')
                                                                    ->required()
                                                                    ->numeric()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_w_kodepos') == null)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                                TextInput::make('al_w_rw')
                                                                    ->label('RW')
                                                                    ->helperText('Isi 0 jika tidak ada RT/RW')
                                                                    ->required()
                                                                    ->numeric()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_w_kodepos') == null)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([

                                                                Textarea::make('al_w_alamat')
                                                                    ->label('Alamat')
                                                                    ->required()
                                                                    ->disabled(fn(Get $get) =>
                                                                    $get('al_w_kodepos') == null)
                                                                    ->hidden(fn(Get $get) =>
                                                                    $get('al_w_tgldi_ln_id') != 2 ||
                                                                        $get('w_status_id') != 3),

                                                            ]),



                                                    ])->compact()

                                            ])
                                    ]),
                                // end of Walisantri Tab

                                Tabs\Tab::make('Santri')
                                    ->schema([

                                        Group::make()
                                            ->relationship('santri')
                                            ->schema([
                                                //SANTRI
                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b">
                                                                <p class="text-2xl">SANTRI</p>
                                                            </div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('nama_lengkap')
                                                            ->label('Nama Lengkap')
                                                            ->hint('Isi sesuai dengan KK')
                                                            ->hintColor('danger')
                                                            //->default('asfasdad')
                                                            ->required(),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('kartu_keluarga')
                                                            ->label('Nomor KK Calon Santri')
                                                            ->length(16)
                                                            ->required()
                                                            // ->disabled(fn (Get $get) =>
                                                            // $get('kartu_keluarga_sama') !== 'KK Sendiri')
                                                            ->dehydrated(),

                                                        TextInput::make('nama_kpl_kel')
                                                            ->label('Nama Kepala Keluarga')
                                                            ->required()
                                                            // ->disabled(fn (Get $get) =>
                                                            // $get('kartu_keluarga_sama') !== 'KK Sendiri')
                                                            ->dehydrated(),
                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        ToggleButtons::make('kewarganegaraan_id')
                                                            ->label('Kewarganegaraan')
                                                            ->inline()
                                                            ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                                                            ->default(1)
                                                            ->live(),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        ComponentsTextInput::make('nik')
                                                            ->label('NIK')
                                                            ->hint('Isi sesuai dengan KK')
                                                            ->hintColor('danger')
                                                            ->regex('/^[0-9]*$/')
                                                            ->length(16)
                                                            ->maxLength(16)
                                                            ->required()
                                                            ->unique(Santri::class, 'nik')
                                                            //->default('3295131306822002')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('kewarganegaraan_id') != 1),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('asal_negara')
                                                            ->label('Asal Negara Calon Santri')
                                                            ->required()
                                                            //->default('asfasdad')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('kewarganegaraan_id') != 2),

                                                        TextInput::make('kitas')
                                                            ->label('KITAS Calon Santri')
                                                            ->hint('Nomor Izin Tinggal (KITAS)')
                                                            ->hintColor('danger')
                                                            ->required()
                                                            //->default('3295131306822002')
                                                            ->unique(Santri::class, 'kitas')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('kewarganegaraan_id') != 2),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('nama_panggilan')
                                                            ->label('Nama Hijroh/Islami/Panggilan')
                                                            //->default('asfasdad')
                                                            ->required(),

                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b">
                                                            </div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        ToggleButtons::make('jeniskelamin_id')
                                                            ->label('Jenis Kelamin')
                                                            ->inline()
                                                            ->options(Jeniskelamin::whereIsActive(1)->pluck('jeniskelamin', 'id'))
                                                            ->required(),

                                                    ]),

                                                Grid::make(6)
                                                    ->schema([

                                                        TextInput::make('tempat_lahir')
                                                            ->label('Tempat Lahir')
                                                            ->hint('Isi sesuai dengan KK')
                                                            ->hintColor('danger')
                                                            //->default('asfasdad')
                                                            ->required(),

                                                        DatePicker::make('tanggal_lahir')
                                                            ->label('Tanggal Lahir')
                                                            ->hint('Isi sesuai dengan KK')
                                                            ->hintColor('danger')
                                                            //->default('20010101')
                                                            ->required()
                                                            ->displayFormat('d M Y')
                                                            ->native(false)
                                                            ->live()
                                                            ->closeOnDateSelection()
                                                            ->afterStateUpdated(function (Set $set, $state) {
                                                                $set('umur', Carbon::parse($state)->age);
                                                            }),

                                                        TextInput::make('umur')
                                                            ->label('Umur')
                                                            ->disabled()
                                                            ->dehydrated()
                                                            ->required(),

                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('anak_ke')
                                                            ->label('Anak ke-')
                                                            ->required()
                                                            //->default('3')
                                                            ->rules([
                                                                fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                                                                    $anakke = $get('anak_ke');
                                                                    $psjumlahsaudara = $get('jumlah_saudara');
                                                                    $jumlahsaudara = $psjumlahsaudara + 1;

                                                                    if ($anakke > $jumlahsaudara) {
                                                                        $fail("Anak ke tidak bisa lebih dari jumlah saudara + 1");
                                                                    }
                                                                },
                                                            ]),

                                                        TextInput::make('jumlah_saudara')
                                                            ->label('Jumlah saudara')
                                                            //->default('5')
                                                            ->required(),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('agama')
                                                            ->label('Agama')
                                                            ->default('Islam')
                                                            ->disabled()
                                                            ->required()
                                                            ->dehydrated(),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        Select::make('cita_cita_id')
                                                            ->label('Cita-cita')
                                                            ->placeholder('Pilih Cita-cita')
                                                            ->options(Cita::whereIsActive(1)->pluck('cita', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            ->live()
                                                            ->native(false),

                                                        TextInput::make('cita_cita_lainnya')
                                                            ->label('Cita-cita Lainnya')
                                                            ->required()
                                                            //->default('asfasdad')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('cita_cita_id') != 10),
                                                    ]),

                                                Grid::make(4)
                                                    ->schema([
                                                        Select::make('hobi_id')
                                                            ->label('Hobi')
                                                            ->placeholder('Pilih Hobi')
                                                            ->options(Hobi::whereIsActive(1)->pluck('hobi', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('Lainnya')
                                                            ->live()
                                                            ->native(false),

                                                        TextInput::make('hobi_lainnya')
                                                            ->label('Hobi Lainnya')
                                                            ->required()
                                                            //->default('asfasdad')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('hobi_id') != 6),

                                                    ]),


                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(4)
                                                    ->schema([
                                                        Select::make('keb_khus_id')
                                                            ->label('Kebutuhan Khusus')
                                                            ->placeholder('Pilih Kebutuhan Khusus')
                                                            ->options(KebutuhanKhusus::whereIsActive(1)->pluck('kebutuhan_khusus', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('Lainnya')
                                                            ->live()
                                                            ->native(false),

                                                        TextInput::make('keb_khus_lainnya')
                                                            ->label('Kebutuhan Khusus Lainnya')
                                                            ->required()
                                                            //->default('asfasdad')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('keb_khus_id') != 6),
                                                    ]),

                                                Grid::make(4)
                                                    ->schema([
                                                        Select::make('keb_dis_id')
                                                            ->label('Kebutuhan Disabilitas')
                                                            ->placeholder('Pilih Kebutuhan Disabilitas')
                                                            ->options(KebutuhanDisabilitas::whereIsActive(1)->pluck('kebutuhan_disabilitas', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('Lainnya')
                                                            ->live()
                                                            ->native(false),

                                                        TextInput::make('keb_dis_lainnya')
                                                            ->label('Kebutuhan Disabilitas Lainnya')
                                                            ->required()
                                                            //->default('asfasdad')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('keb_dis_id') != 8),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        ToggleButtons::make('tdk_hp_id')
                                                            ->label('Apakah memiliki nomor handphone?')
                                                            ->live()
                                                            ->inline()
                                                            ->grouped()
                                                            ->boolean()
                                                            ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id')),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('nomor_handphone')
                                                            ->label('No. Handphone')
                                                            ->helperText('Contoh: 82187782223')
                                                            // ->mask('82187782223')
                                                            ->prefix('+62')
                                                            ->tel()
                                                            //->default('82187782223')
                                                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                                            ->required()
                                                            ->hidden(fn(Get $get) =>
                                                            $get('tdk_hp_id') != 1),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('email')
                                                            ->label('Email')
                                                            //->default('mail@mail.com')
                                                            ->email(),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(2)
                                                    ->schema([

                                                        ToggleButtons::make('ps_mendaftar_keinginan_id')
                                                            ->label('Mendaftar atas kenginginan')
                                                            ->inline()
                                                            ->options(MendaftarKeinginan::whereIsActive(1)->pluck('mendaftar_keinginan', 'id'))
                                                            ->live(),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('ps_mendaftar_keinginan_lainnya')
                                                            ->label('Lainnya')
                                                            ->required()
                                                            //->default('asdasf')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('ps_mendaftar_keinginan_id') != 4),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Hidden::make('aktivitaspend_id')
                                                    ->default(9),

                                                Grid::make(2)
                                                    ->schema([

                                                        ToggleButtons::make('bya_sklh_id')
                                                            ->label('Yang membiayai sekolah')
                                                            ->inline()
                                                            ->options(MembiayaiSekolah::whereIsActive(1)->pluck('membiayai_sekolah', 'id'))
                                                            ->live(),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('bya_sklh_lainnya')
                                                            ->label('Yang membiayai sekolah lainnya')
                                                            ->required()
                                                            //->default('asfasdad')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('bya_sklh_id') != 4),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(4)
                                                    ->schema([

                                                        ToggleButtons::make('belum_nisn_id')
                                                            ->label('Apakah memiliki NISN?')
                                                            ->helperText(new HtmlString('<strong>NISN</strong> adalah Nomor Induk Siswa Nasional'))
                                                            ->live()
                                                            ->inline()
                                                            ->grouped()
                                                            ->boolean()
                                                            ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id')),

                                                        TextInput::make('nisn')
                                                            ->label('Nomor NISN')
                                                            ->required()
                                                            //->default('2421324')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('belum_nisn_id') != 1),
                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        ToggleButtons::make('nomor_kip_memiliki_id')
                                                            ->label('Apakah memiliki KIP?')
                                                            ->helperText(new HtmlString('<strong>KIP</strong> adalah Kartu Indonesia Pintar'))
                                                            ->live()
                                                            ->inline()
                                                            ->grouped()
                                                            ->boolean()
                                                            ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id')),

                                                        TextInput::make('nomor_kip')
                                                            ->label('Nomor KIP')
                                                            ->required()
                                                            //->default('32524324')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('nomor_kip_memiliki_id') != 1),
                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                Grid::make(2)
                                                    ->schema([

                                                        Textarea::make('ps_peng_pend_agama')
                                                            ->label('Pengalaman pendidikan agama')
                                                            ->required(),

                                                    ]),

                                                Grid::make(2)
                                                    ->schema([

                                                        Textarea::make('ps_peng_pend_formal')
                                                            ->label('Pengalaman pendidikan formal')
                                                            ->required(),
                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        Select::make('hafalan_id')
                                                            ->label('Hafalan')
                                                            ->placeholder('Pilih jumlah hafalan dalam juz')
                                                            ->options(Hafalan::whereIsActive(1)->pluck('hafalan', 'id'))
                                                            ->required()
                                                            ->suffix('juz')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('qism_id') == 1)
                                                            ->native(false),

                                                    ]),

                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b"></div>')),

                                                // ALAMAT SANTRI
                                                Placeholder::make('')
                                                    ->content(new HtmlString('<div class="border-b">
                                                                <p class="text-lg">TEMPAT TINGGAL DOMISILI</p>
                                                                <p class="text-lg">SANTRI</p>
                                                            </div>')),

                                                Grid::make(2)
                                                    ->schema([

                                                        ToggleButtons::make('al_s_status_mukim_id')
                                                            ->label('Apakah mukim di Pondok?')
                                                            ->helperText(new HtmlString('Pilih <strong>Tidak Mukim</strong> khusus bagi pendaftar <strong>Tarbiyatul Aulaad</strong> dan <strong>Pra Tahfidz kelas 1-4</strong>'))
                                                            ->live()
                                                            ->inline()
                                                            ->required()
                                                            ->default(function (Get $get) {

                                                                $qism = $get('qism_id');

                                                                $kelas = $get('kelas_id');

                                                                if ($qism == 1) {

                                                                    return 2;
                                                                } elseif ($qism == 2 && $kelas == 1) {

                                                                    return 2;
                                                                } elseif ($qism == 2 && $kelas == 1) {

                                                                    return 2;
                                                                } elseif ($qism == 2 && $kelas == 2) {

                                                                    return 2;
                                                                } elseif ($qism == 2 && $kelas == 3) {

                                                                    return 2;
                                                                } elseif ($qism == 2 && $kelas == 4) {

                                                                    return 2;
                                                                } else {
                                                                    return 1;
                                                                }
                                                            })
                                                            ->options(function (Get $get) {

                                                                $qism = $get('qism_id');

                                                                $kelas = $get('kelas_id');

                                                                if ($qism == 1) {

                                                                    return ([
                                                                        2 => 'Tidak Mukim'
                                                                    ]);
                                                                } elseif ($qism == 2 && $kelas == 1) {

                                                                    return ([
                                                                        2 => 'Tidak Mukim',
                                                                    ]);
                                                                } elseif ($qism == 2 && $kelas == 1) {

                                                                    return ([
                                                                        2 => 'Tidak Mukim',
                                                                    ]);
                                                                } elseif ($qism == 2 && $kelas == 2) {

                                                                    return ([
                                                                        2 => 'Tidak Mukim',
                                                                    ]);
                                                                } elseif ($qism == 2 && $kelas == 3) {

                                                                    return ([
                                                                        2 => 'Tidak Mukim',
                                                                    ]);
                                                                } elseif ($qism == 2 && $kelas == 4) {

                                                                    return ([
                                                                        2 => 'Tidak Mukim',
                                                                    ]);
                                                                } else {
                                                                    return ([

                                                                        1 => 'Mukim',
                                                                    ]);
                                                                }
                                                            })
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                if ($get('al_s_status_mukim_id') == 1) {

                                                                    $set('al_s_stts_tptgl_id', 10);
                                                                } elseif ($get('al_s_status_mukim_id') == 2) {

                                                                    $set('al_s_stts_tptgl_id', null);
                                                                }
                                                            }),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        Select::make('al_s_stts_tptgl_id')
                                                            ->label('Status tempat tinggal')
                                                            ->placeholder('Status tempat tinggal')
                                                            ->options(function (Get $get) {
                                                                if ($get('al_s_status_mukim_id') == 2) {
                                                                    return (StatusTempatTinggal::whereIsActive(1)->pluck('status_tempat_tinggal', 'id'));
                                                                }
                                                            })
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('Kontrak/Kost')
                                                            ->hidden(fn(Get $get) =>
                                                            $get('al_s_status_mukim_id') == 1)
                                                            ->live()
                                                            ->native(false)
                                                            ->dehydrated(),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        Select::make('al_s_provinsi_id')
                                                            ->label('Provinsi')
                                                            ->placeholder('Pilih Provinsi')
                                                            ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                                            // ->searchable()
                                                            //->default('35')
                                                            ->required()
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            )
                                                            ->afterStateUpdated(function (Set $set) {
                                                                $set('al_s_kabupaten_id', null);
                                                                $set('al_s_kecamatan_id', null);
                                                                $set('al_s_kelurahan_id', null);
                                                                $set('al_s_kodepos', null);
                                                            }),

                                                        Select::make('al_s_kabupaten_id')
                                                            ->label('Kabupaten')
                                                            ->placeholder('Pilih Kabupaten')
                                                            ->options(fn(Get $get): Collection => Kabupaten::query()
                                                                ->where('provinsi_id', $get('al_s_provinsi_id'))
                                                                ->pluck('kabupaten', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('232')
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        Select::make('al_s_kecamatan_id')
                                                            ->label('Kecamatan')
                                                            ->placeholder('Pilih Kecamatan')
                                                            ->options(fn(Get $get): Collection => Kecamatan::query()
                                                                ->where('kabupaten_id', $get('al_s_kabupaten_id'))
                                                                ->pluck('kecamatan', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('3617')
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                        Select::make('al_s_kelurahan_id')
                                                            ->label('Kelurahan')
                                                            ->placeholder('Pilih Kelurahan')
                                                            ->options(fn(Get $get): Collection => Kelurahan::query()
                                                                ->where('kecamatan_id', $get('al_s_kecamatan_id'))
                                                                ->pluck('kelurahan', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('45322')
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            )
                                                            ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                                                                $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                                                                $state = $kodepos;

                                                                foreach ($state as $state) {
                                                                    $set('al_s_kodepos', Str::substr($state, 12, 5));
                                                                }
                                                            }),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        TextInput::make('al_s_kodepos')
                                                            ->label('Kodepos')
                                                            ->disabled()
                                                            ->required()
                                                            ->dehydrated()
                                                            //->default('63264')
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([


                                                        TextInput::make('al_s_rt')
                                                            ->label('RT')
                                                            ->helperText('Isi 0 jika tidak ada RT/RW')
                                                            ->required()
                                                            ->numeric()
                                                            ->disabled(fn(Get $get) =>
                                                            $get('al_s_kodepos') == null)
                                                            //->default('2')
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                        TextInput::make('al_s_rw')
                                                            ->label('RW')
                                                            ->helperText('Isi 0 jika tidak ada RT/RW')
                                                            ->required()
                                                            ->numeric()
                                                            ->disabled(fn(Get $get) =>
                                                            $get('al_s_kodepos') == null)
                                                            //->default('2')
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                    ]),

                                                Grid::make(2)
                                                    ->schema([

                                                        Textarea::make('al_s_alamat')
                                                            ->label('Alamat')
                                                            ->required()
                                                            ->disabled(fn(Get $get) =>
                                                            $get('al_s_kodepos') == null)
                                                            //->default('sdfsdasdada')
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 1 ||
                                                                    $get('al_s_stts_tptgl_id') == 2 ||
                                                                    $get('al_s_stts_tptgl_id') == 3 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([
                                                        Select::make('al_s_jarak_id')
                                                            ->label('Jarak tempat tinggal ke Pondok Pesantren')
                                                            ->options(Jarakpp::whereIsActive(1)->pluck('jarak_kepp', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('Kurang dari 5 km')
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                        Select::make('al_s_transportasi_id')
                                                            ->label('Transportasi ke Pondok Pesantren')
                                                            ->options(Transpp::whereIsActive(1)->pluck('transportasi_kepp', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('Ojek')
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                    ]),

                                                Grid::make(4)
                                                    ->schema([

                                                        Select::make('al_s_waktu_tempuh_id')
                                                            ->label('Waktu tempuh ke Pondok Pesantren')
                                                            ->options(Waktutempuh::whereIsActive(1)->pluck('waktu_tempuh', 'id'))
                                                            // ->searchable()
                                                            ->required()
                                                            //->default('10 - 19 menit')
                                                            ->live()
                                                            ->native(false)
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),

                                                        TextInput::make('al_s_koordinat')
                                                            ->label('Titik koordinat tempat tinggal')
                                                            //->default('sfasdadasdads')
                                                            ->hidden(
                                                                fn(Get $get) =>
                                                                $get('al_s_status_mukim_id') != 2 ||
                                                                    $get('al_s_stts_tptgl_id') == null
                                                            ),
                                                    ]),
                                            ]),
                                    ]),
                                // end of Santri Tab





                            ])->columnSpanFull()

                    ])

                // Tables\Actions\EditAction::make()
                //     ->label('Edit Data')
                //     ->modalCloseButton(false)
                //     ->modalHeading(' ')
                //     // ->modalDescription(new HtmlString('<div class="">
                //     //                                         <p>Butuh bantuan?</p>
                //     //                                         <p>Silakan mengubungi admin di bawah ini:</p>

                //     //                                         <table class="table w-fit">
                //     //                     <!-- head -->
                //     //                     <thead>
                //     //                         <tr class="border-tsn-header">
                //     //                             <th class="text-tsn-header text-xs" colspan="2"></th>
                //     //                         </tr>
                //     //                     </thead>
                //     //                     <tbody>
                //     //                         <!-- row 1 -->
                //     //                         <tr>
                //     //                             <th><a href="https://wa.me/6282210862400"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                //     //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                //     //                             </svg>
                //     //                             </a></th>
                //     //                             <td class="text-xs"><a href="https://wa.me/6282210862400">WA Admin Putra (Abu Hammaam)</a></td>
                //     //                         </tr>
                //     //                         <tr>
                //     //                             <th><a href="https://wa.me/6285236459012"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                //     //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                //     //                             </svg>
                //     //                             </a></th>
                //     //                             <td class="text-xs"><a href="https://wa.me/6285236459012">WA Admin Putra (Abu Fathimah Hendi)</a></td>
                //     //                         </tr>
                //     //                         <tr>
                //     //                             <th><a href="https://wa.me/6281333838691"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                //     //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                //     //                             </svg>
                //     //                             </a></th>
                //     //                             <td class="text-xs"><a href="https://wa.me/6281333838691">WA Admin Putra (Akh Irfan)</a></td>
                //     //                         </tr>
                //     //                         <tr>
                //     //                             <th><a href="https://wa.me/628175765767"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                //     //                             <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                //     //                             </svg>
                //     //                             </a></th>
                //     //                             <td class="text-xs"><a href="https://wa.me/628175765767">WA Admin Putri</a></td>
                //     //                         </tr>


                //     //                     </tbody>
                //     //                     </table>

                //     //                                     </div>'))
                //     ->modalWidth('full')
                //     // ->stickyModalHeader()
                //     ->button()
                //     ->closeModalByClickingAway(false)
                //     ->modalSubmitActionLabel('Simpan')
                //     ->modalCancelAction(fn(StaticAction $action) => $action->label('Batal'))
                //     ->form([

                //         Section::make()
                //             ->schema([

                //                 Placeholder::make('')

                //                     ->content(function (Model $record) {
                //                         $santri = ModelsSantri::where('id', $record->santri_id)->first();
                //                         return (new HtmlString('<div><p class="text-3xl"><strong>' . $santri->nama_lengkap . '</strong></p></div>'));
                //                     }),

                //                 Placeholder::make('')
                //                     ->content(function (Model $record) {
                //                         $santri = ModelsSantri::where('id', $record->santri_id)->first();
                //                         $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                //                         $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                //                         $cekdatats = KelasSantri::where('tahun_berjalan_id', $ts->id)
                //                             ->where('santri_id', $record->santri_id)->count();

                //                         if ($cekdatats !== 0) {
                //                             $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                //                             $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                //                             $cekdatats = KelasSantri::where('tahun_berjalan_id', $ts->id)
                //                                 ->where('santri_id', $record->santri_id)->first();

                //                             $abbrqism = Qism::where('id', $cekdatats->qism_id)->first();

                //                             $abbrkelas = Kelas::where('id', $cekdatats->kelas_id)->first();


                //                             return (new HtmlString('<div class="">
                //                                 <table class="table w-fit">
                //             <!-- head -->
                //             <thead>
                //                 <tr class="border-tsn-header">
                //                     <th class="text-tsn-header text-xl" colspan="3"></th>
                //                 </tr>
                //             </thead>
                //             <tbody>
                //                 <!-- row 1 -->
                //                 <tr>
                //                     <th class="text-xl">Qism</th>
                //                     <td class="text-xl">:</td>
                //                     <td class="text-xl">' . $abbrqism->qism . '</td>
                //                 </tr>
                //                 <tr>
                //                     <th class="text-xl">Kelas</th>
                //                     <td class="text-xl">:</td>
                //                     <td class="text-xl">' . $abbrkelas->kelas . '</td>
                //                 </tr>
                //                 <tr>
                //                     <th class="text-xl">NISM</th>
                //                     <td class="text-xl">:</td>
                //                     <td class="text-xl">' . $santri->nism . '</td>
                //                 </tr>



                //             </tbody>
                //             </table>

                //                             </div>'));
                //                         } elseif ($cekdatats == 0) {
                //                             $tahunberjalanaktif = TahunBerjalan::where('is_active', 1)->first();
                //                             $ts = TahunBerjalan::where('tb', $tahunberjalanaktif->ts)->first();

                //                             $cekdatats = KelasSantri::where('tahun_berjalan_id', $tahunberjalanaktif->id)
                //                                 ->where('santri_id', $record->santri_id)->first();

                //                             $abbrqism = Qism::where('id', $cekdatats->qism_id)->first();

                //                             $abbrkelas = Kelas::where('id', $cekdatats->kelas_id)->first();




                //                             return (new HtmlString('<div class="">
                //                                 <table class="table w-fit">
                //             <!-- head -->
                //             <thead>
                //                 <tr class="border-tsn-header">
                //                     <th class="text-tsn-header text-xl" colspan="3"></th>
                //                 </tr>
                //             </thead>
                //             <tbody>
                //                 <!-- row 1 -->
                //                 <tr>
                //                     <th class="text-xl">Qism</th>
                //                     <td class="text-xl">:</td>
                //                     <td class="text-xl">' . $abbrqism->qism . '</td>
                //                 </tr>
                //                 <tr>
                //                     <th class="text-xl">Kelas</th>
                //                     <td class="text-xl">:</td>
                //                     <td class="text-xl">' . $abbrkelas->kelas . '</td>
                //                 </tr>
                //                 <tr>
                //                     <th class="text-xl">NISM</th>
                //                     <td class="text-xl">:</td>
                //                     <td class="text-xl">510035210133' . $santri->nism . '</td>
                //                 </tr>



                //             </tbody>
                //             </table>

                //                             </div>'));
                //                         }
                //                     }),

                //             ]),

                //         Tabs::make('Tabs')
                //             ->tabs([

                //                 Tabs\Tab::make('Walisantri')
                //                     ->schema([

                //                         Group::make()
                //                             ->relationship('walisantri')
                //                             ->schema([
                //                                 //AYAH KANDUNG
                //                                 Section::make('A. AYAH KANDUNG')
                //                                     ->schema([

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ak_nama_lengkap_sama_id')
                //                                                     ->label('Apakah Nama sama dengan Nama Kepala Keluarga?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     // ->hidden(fn (Get $get) =>
                //                                                     // $get('ak_status_id') != 1)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ak_nama_lengkap_sama_id') == 1) {
                //                                                             $set('ak_nama_lengkap', $get('nama_kpl_kel_santri'));
                //                                                             $set('ik_nama_lengkap_sama_id_id', 2);
                //                                                             $set('ik_nama_lengkap', null);
                //                                                             $set('w_nama_lengkap_sama_id_id', 2);
                //                                                             $set('w_nama_lengkap', null);
                //                                                         } else {
                //                                                             $set('ak_nama_lengkap', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                                 TextInput::make('ak_nama_lengkap')
                //                                                     ->label('Nama Lengkap')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ak_nama_lengkap_sama') == 1)
                //                                                     ->dehydrated(),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                             <p class="text-lg">A.01 STATUS AYAH KANDUNG</p>
                //                                         </div>')),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ak_status_id')
                //                                                     ->label('Status')
                //                                                     // ->placeholder('Pilih Status')
                //                                                     ->options(StatusWalisantri::whereIsActive(1)->pluck('status_walisantri', 'id'))
                //                                                     ->required()
                //                                                     ->inline()
                //                                                     ->live()
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ak_status_id') == 1) {
                //                                                             $set('ak_kewarganegaraan_id', 1);
                //                                                         }
                //                                                     }),
                //                                                 // ->native(false),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('ak_nama_kunyah')
                //                                                     ->label('Nama Hijroh/Islami/Panggilan')
                //                                                     ->required(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ak_kewarganegaraan_id')
                //                                                     ->label('Kewarganegaraan')
                //                                                     // ->placeholder('Pilih Kewarganegaraan')
                //                                                     ->inline()
                //                                                     ->default(1)
                //                                                     ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                //                                                     ->required()
                //                                                     ->live(),
                //                                                 // ->native(false),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_kewarganegaraan_id') != 1 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 ComponentsTextInput::make('ak_nik')
                //                                                     ->label('NIK')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->regex('/^[0-9]*$/')
                //                                                     ->length(16)
                //                                                     ->maxLength(16)
                //                                                     ->required(),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_kewarganegaraan_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('ak_asal_negara')
                //                                                     ->label('Asal Negara')
                //                                                     ->required(),


                //                                                 TextInput::make('ak_kitas')
                //                                                     ->label('KITAS')
                //                                                     ->hint('Nomor Izin Tinggal (KITAS)')
                //                                                     ->hintColor('danger')
                //                                                     ->required(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('ak_tempat_lahir')
                //                                                     ->label('Tempat Lahir')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required(),


                //                                                 DatePicker::make('ak_tanggal_lahir')
                //                                                     ->label('Tanggal Lahir')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->format('dd/mm/yyyy')
                //                                                     ->displayFormat('d M Y')
                //                                                     ->maxDate(now())
                //                                                     // ->native(false)
                //                                                     ->closeOnDateSelection(),
                //                                             ]),

                //                                         Grid::make(6)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 Select::make('ak_pend_terakhir_id')
                //                                                     ->label('Pendidikan Terakhir')
                //                                                     ->placeholder('Pilih Pendidikan Terakhir')
                //                                                     ->options(PendidikanTerakhirWalisantri::whereIsActive(1)->pluck('pendidikan_terakhir_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),

                //                                                 Select::make('ak_pekerjaan_utama_id')
                //                                                     ->label('Pekerjaan Utama')
                //                                                     ->placeholder('Pilih Pekerjaan Utama')
                //                                                     ->options(PekerjaanUtamaWalisantri::whereIsActive(1)->pluck('pekerjaan_utama_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),

                //                                                 Select::make('ak_pghsln_rt_id')
                //                                                     ->label('Penghasilan Rata-Rata')
                //                                                     ->placeholder('Pilih Penghasilan Rata-Rata')
                //                                                     ->options(PenghasilanWalisantri::whereIsActive(1)->pluck('penghasilan_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ak_tdk_hp_id')
                //                                                     ->label('Apakah memiliki nomor handphone?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ak_tdk_hp_id') == 2) {
                //                                                             $set('ak_nomor_handphone_sama_id', null);
                //                                                             $set('ak_nomor_handphone', null);
                //                                                         }
                //                                                     }),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_tdk_hp_id') != 1 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([


                //                                                 ToggleButtons::make('ak_nomor_handphone_sama_id')
                //                                                     ->label('Apakah nomor handphone sama dengan Pendaftar?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ak_nomor_handphone_sama_id') == 1) {
                //                                                             $set('ak_nomor_handphone', $get('hp_komunikasi'));
                //                                                             $set('ik_nomor_handphone_sama_id', 2);
                //                                                             $set('ik_nomor_handphone', null);
                //                                                             $set('w_nomor_handphone_sama_id', 2);
                //                                                             $set('w_nomor_handphone', null);
                //                                                         } else {
                //                                                             $set('ak_nomor_handphone', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                                 TextInput::make('ak_nomor_handphone')
                //                                                     ->label('No. Handphone')
                //                                                     ->helperText('Contoh: 82187782223')
                //                                                     ->prefix('+62')
                //                                                     ->tel()
                //                                                     ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ak_nomor_handphone_sama_id') == 1)
                //                                                     ->dehydrated(),
                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                  <p class="text-lg">Kajian yang diikuti</p>
                //                              </div>')),
                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 Textarea::make('ak_ustadz_kajian')
                //                                                     ->label('Ustadz yang mengisi kajian')
                //                                                     ->required(),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 TextArea::make('ak_tempat_kajian')
                //                                                     ->label('Tempat kajian yang diikuti')
                //                                                     ->required(),

                //                                             ]),

                //                                         // KARTU KELUARGA AYAH KANDUNG
                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([
                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                             <p class="text-lg">A.02 KARTU KELUARGA</p>
                //                             <p class="text-lg">AYAH KANDUNG</p>
                //                                </div>')),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ak_kk_sama_pendaftar_id')
                //                                                     ->label('Apakah KK dan Nama Kepala Keluarga sama dengan Pendaftar?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ak_kk_sama_pendaftar_id') == 1) {
                //                                                             $set('ak_no_kk', $get('kartu_keluarga_santri'));
                //                                                             $set('ak_kep_kel_kk', $get('nama_kpl_kel_santri'));
                //                                                             $set('ik_kk_sama_pendaftar_id', 2);
                //                                                             $set('ik_no_kk', null);
                //                                                             $set('ik_kep_kel_kk', null);
                //                                                             $set('w_kk_sama_pendaftar_id', 2);
                //                                                             $set('w_no_kk', null);
                //                                                             $set('w_kep_kel_kk', null);
                //                                                         } else {
                //                                                             $set('ak_no_kk', null);
                //                                                             $set('ak_kep_kel_kk', null);
                //                                                         }
                //                                                     })->columnSpanFull(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 ComponentsTextInput::make('ak_no_kk')
                //                                                     ->label('No. KK Ayah Kandung')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->length(16)
                //                                                     ->maxLength(16)
                //                                                     ->required()
                //                                                     ->regex('/^[0-9]*$/')
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ak_kk_sama_pendaftar_id') == 1)
                //                                                     ->dehydrated(),

                //                                                 TextInput::make('ak_kep_kel_kk')
                //                                                     ->label('Nama Kepala Keluarga')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ak_kk_sama_pendaftar_id') == 1)
                //                                                     ->dehydrated(),
                //                                             ]),

                //                                         // ALAMAT AYAH KANDUNG
                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([
                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                             <p class="text-lg">A.03 TEMPAT TINGGAL DOMISILI</p>
                //                                             <p class="text-lg">AYAH KANDUNG</p>
                //                                         </div>')),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 ToggleButtons::make('al_ak_tgldi_ln_id')
                //                                                     ->label('Apakah tinggal di luar negeri?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 1)
                //                                             ->schema([

                //                                                 Textarea::make('al_ak_almt_ln')
                //                                                     ->label('Alamat Luar Negeri')
                //                                                     ->required(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 Select::make('al_ak_stts_rmh_id')
                //                                                     ->label('Status Kepemilikan Rumah')
                //                                                     ->placeholder('Pilih Status Kepemilikan Rumah')
                //                                                     ->options(Statuskepemilikanrumah::whereIsActive(1)->pluck('status_kepemilikan_rumah', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 Select::make('al_ak_provinsi_id')
                //                                                     ->label('Provinsi')
                //                                                     ->placeholder('Pilih Provinsi')
                //                                                     ->options(Provinsi::all()->pluck('provinsi', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->afterStateUpdated(function (Set $set) {
                //                                                         $set('al_ak_kabupaten_id', null);
                //                                                         $set('al_ak_kecamatan_id', null);
                //                                                         $set('al_ak_kelurahan_id', null);
                //                                                         $set('al_ak_kodepos', null);
                //                                                     }),

                //                                                 Select::make('al_ak_kabupaten_id')
                //                                                     ->label('Kabupaten')
                //                                                     ->placeholder('Pilih Kabupaten')
                //                                                     ->options(fn(Get $get): Collection => Kabupaten::query()
                //                                                         ->where('provinsi_id', $get('al_ak_provinsi_id'))
                //                                                         ->pluck('kabupaten', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 Select::make('al_ak_kecamatan_id')
                //                                                     ->label('Kecamatan')
                //                                                     ->placeholder('Pilih Kecamatan')
                //                                                     ->options(fn(Get $get): Collection => Kecamatan::query()
                //                                                         ->where('kabupaten_id', $get('al_ak_kabupaten_id'))
                //                                                         ->pluck('kecamatan', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false),

                //                                                 Select::make('al_ak_kelurahan_id')
                //                                                     ->label('Kelurahan')
                //                                                     ->placeholder('Pilih Kelurahan')
                //                                                     ->options(fn(Get $get): Collection => Kelurahan::query()
                //                                                         ->where('kecamatan_id', $get('al_ak_kecamatan_id'))
                //                                                         ->pluck('kelurahan', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                //                                                         if (($get('al_ak_kodepos') ?? '') !== Str::slug($old)) {
                //                                                             return;
                //                                                         }

                //                                                         $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                //                                                         $state = $kodepos;

                //                                                         foreach ($state as $state) {
                //                                                             $set('al_ak_kodepos', Str::substr($state, 12, 5));
                //                                                         }
                //                                                     }),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('al_ak_kodepos')
                //                                                     ->label('Kodepos')
                //                                                     ->disabled()
                //                                                     ->required()
                //                                                     ->dehydrated(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([


                //                                                 TextInput::make('al_ak_rt')
                //                                                     ->label('RT')
                //                                                     ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                                     ->required()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_ak_kodepos') == null)
                //                                                     ->numeric(),

                //                                                 TextInput::make('al_ak_rw')
                //                                                     ->label('RW')
                //                                                     ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                                     ->required()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_ak_kodepos') == null)
                //                                                     ->numeric(),
                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_ak_tgldi_ln_id') != 2 ||
                //                                                 $get('ak_status_id') != 1)
                //                                             ->schema([
                //                                                 Textarea::make('al_ak_alamat')
                //                                                     ->label('Alamat')
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_ak_kodepos') == null)
                //                                                     ->required(),
                //                                             ]),

                //                                     ])->compact(),


                //                                 // //IBU KANDUNG
                //                                 Section::make('B. IBU KANDUNG')
                //                                     ->schema([

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_nama_lengkap_sama_id')
                //                                                     ->label('Apakah Nama sama dengan Nama Kepala Keluarga?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ak_nama_lengkap_sama_id') != 2)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ik_nama_lengkap_sama_id') == 1) {
                //                                                             $set('ik_nama_lengkap', $get('nama_kpl_kel_santri'));
                //                                                             $set('w_nama_lengkap_sama_id', 2);
                //                                                             $set('w_nama_lengkap', null);
                //                                                         } else {
                //                                                             $set('ik_nama_lengkap', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                                 TextInput::make('ik_nama_lengkap')
                //                                                     ->label('Nama Lengkap')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ik_nama_lengkap_sama_id') == 1)
                //                                                     ->dehydrated(),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                             <p class="text-lg">B.01 STATUS IBU KANDUNG</p>
                //                                         </div>')),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_status_id')
                //                                                     ->label('Status')
                //                                                     // ->placeholder('Pilih Status')
                //                                                     ->options(StatusWalisantri::whereIsActive(1)->pluck('status_walisantri', 'id'))
                //                                                     ->required()
                //                                                     ->inline()
                //                                                     ->live()
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ik_status_id') == 1) {
                //                                                             $set('ik_kewarganegaraan_id', 1);
                //                                                         }
                //                                                     }),
                //                                                 // ->native(false),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('ik_nama_kunyah')
                //                                                     ->label('Nama Hijroh/Islami/Panggilan')
                //                                                     ->required(),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_kewarganegaraan_id')
                //                                                     ->label('Kewarganegaraan')
                //                                                     // ->placeholder('Pilih Kewarganegaraan')
                //                                                     ->inline()
                //                                                     ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                //                                                     ->default(1),
                //                                                 // ->native(false)

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_kewarganegaraan_id') != 1 ||
                //                                                 $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 ComponentsTextInput::make('ik_nik')
                //                                                     ->label('NIK')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->regex('/^[0-9]*$/')
                //                                                     ->length(16)
                //                                                     ->maxLength(16)
                //                                                     ->required(),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_kewarganegaraan_id') != 2 ||
                //                                                 $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('ik_asal_negara')
                //                                                     ->label('Asal Negara')
                //                                                     ->required(),

                //                                                 TextInput::make('ik_kitas')
                //                                                     ->label('KITAS')
                //                                                     ->hint('Nomor Izin Tinggal (KITAS)')
                //                                                     ->hintColor('danger')
                //                                                     ->required(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 TextInput::make('ik_tempat_lahir')
                //                                                     ->label('Tempat Lahir')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required(),

                //                                                 DatePicker::make('ik_tanggal_lahir')
                //                                                     ->label('Tanggal Lahir')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->format('dd/mm/yyyy')
                //                                                     ->displayFormat('d M Y')
                //                                                     ->maxDate(now())
                //                                                     // ->native(false)
                //                                                     ->closeOnDateSelection(),
                //                                             ]),

                //                                         Grid::make(6)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 Select::make('ik_pend_terakhir_id')
                //                                                     ->label('Pendidikan Terakhir')
                //                                                     ->placeholder('Pilih Pendidikan Terakhir')
                //                                                     ->options(PendidikanTerakhirWalisantri::whereIsActive(1)->pluck('pendidikan_terakhir_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),

                //                                                 Select::make('ik_pekerjaan_utama_id')
                //                                                     ->label('Pekerjaan Utama')
                //                                                     ->placeholder('Pilih Pekerjaan Utama')
                //                                                     ->options(PekerjaanUtamaWalisantri::whereIsActive(1)->pluck('pekerjaan_utama_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),

                //                                                 Select::make('ik_pghsln_rt_id')
                //                                                     ->label('Penghasilan Rata-Rata')
                //                                                     ->placeholder('Pilih Penghasilan Rata-Rata')
                //                                                     ->options(PenghasilanWalisantri::whereIsActive(1)->pluck('penghasilan_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_tdk_hp_id')
                //                                                     ->label('Apakah memiliki nomor handphone?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_nomor_handphone_sama_id')
                //                                                     ->label('Apakah nomor handphone sama dengan Pendaftar?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))

                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_tdk_hp_id') != 1 ||
                //                                                         $get('ak_nomor_handphone_sama_id') != 2 ||
                //                                                         $get('ik_status_id') != 1)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ik_nomor_handphone_sama_id') == 1) {
                //                                                             $set('ik_nomor_handphone', $get('hp_komunikasi'));
                //                                                             $set('w_nomor_handphone', null);
                //                                                         } else {
                //                                                             $set('ik_nomor_handphone', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                                 TextInput::make('ik_nomor_handphone')
                //                                                     ->label('No. Handphone')
                //                                                     ->helperText('Contoh: 82187782223')
                //                                                     ->prefix('+62')
                //                                                     ->tel()
                //                                                     ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ik_nomor_handphone_sama_id') == 1)
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_tdk_hp_id') != 1 ||
                //                                                         $get('ik_status_id') != 1),
                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                  <p class="text-lg">Kajian yang diikuti</p>
                //                              </div>')),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_kajian_sama_ak_id')
                //                                                     ->label('Apakah kajian yang diikuti sama dengan Ayah?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_status_id') != 1)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ik_kajian_sama_ak_id') == 1) {
                //                                                             $set('ik_ustadz_kajian', $get('ak_ustadz_kajian'));
                //                                                             $set('ik_tempat_kajian', $get('ak_tempat_kajian'));
                //                                                         } else {
                //                                                             $set('ik_ustadz_kajian', null);
                //                                                             $set('ik_tempat_kajian', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 Textarea::make('ik_ustadz_kajian')
                //                                                     ->label('Ustadz yang mengisi kajian')
                //                                                     ->required(),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 TextArea::make('ik_tempat_kajian')
                //                                                     ->label('Tempat kajian yang diikuti')
                //                                                     ->required(),

                //                                             ]),

                //                                         // KARTU KELUARGA IBU KANDUNG
                //                                         Grid::make(2)
                //                                             ->schema([
                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                 <p class="text-lg">B.02 KARTU KELUARGA</p>
                //                                 <p class="text-lg">IBU KANDUNG</p>
                //                                 </div>')),

                //                                             ])

                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_status_id') != 1),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('ik_kk_sama_ak_id')
                //                                                     ->label('Apakah KK Ibu Kandung sama dengan KK Ayah Kandung?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(function (Get $get) {

                //                                                         if ($get('ak_status_id') != 1) {

                //                                                             return ([
                //                                                                 2 => 'Tidak',
                //                                                             ]);
                //                                                         } else {
                //                                                             return ([
                //                                                                 1 => 'Ya',
                //                                                                 2 => 'Tidak',
                //                                                             ]);
                //                                                         }
                //                                                     })
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {
                //                                                         $sama = $get('ik_kk_sama_ak_id');
                //                                                         $set('al_ik_sama_ak_id', $sama);

                //                                                         if ($get('ik_kk_sama_ak_id') == 1) {
                //                                                             $set('al_ik_sama_ak_id', 1);
                //                                                         }
                //                                                     })
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_status_id') != 1),

                //                                                 ToggleButtons::make('al_ik_sama_ak_id')
                //                                                     ->label('Alamat sama dengan Ayah Kandung')
                //                                                     ->helperText('Untuk mengubah alamat, silakan mengubah status KK Ibu kandung')
                //                                                     ->disabled()
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_status_id') != 1),

                //                                                 ToggleButtons::make('ik_kk_sama_pendaftar_id')
                //                                                     ->label('Apakah KK dan Nama Kepala Keluarga sama dengan Pendaftar?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('ak_kk_sama_pendaftar_id') != 2 ||
                //                                                         $get('ik_status_id') != 1)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('ik_kk_sama_pendaftar_id') == 1) {
                //                                                             $set('ik_no_kk', $get('kartu_keluarga_santri'));
                //                                                             $set('ik_kep_kel_kk', $get('nama_kpl_kel_santri'));
                //                                                             $set('w_kk_sama_pendaftar_id', 2);
                //                                                             $set('w_no_kk', null);
                //                                                             $set('w_kep_kel_kk', null);
                //                                                         } else {
                //                                                             $set('ik_no_kk', null);
                //                                                             $set('ik_kep_kel_kk', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ik_kk_sama_ak_id') != 2 ||
                //                                                 $get('ik_status_id') != 1)
                //                                             ->schema([

                //                                                 ComponentsTextInput::make('ik_no_kk')
                //                                                     ->label('No. KK Ibu Kandung')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->length(16)
                //                                                     ->maxLength(16)
                //                                                     ->regex('/^[0-9]*$/')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ik_kk_sama_pendaftar_id') == 1)
                //                                                     ->dehydrated(),

                //                                                 TextInput::make('ik_kep_kel_kk')
                //                                                     ->label('Nama Kepala Keluarga')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('ik_kk_sama_pendaftar_id') == 1)
                //                                                     ->dehydrated(),

                //                                             ]),


                //                                         // ALAMAT IBU KANDUNG
                //                                         Grid::make(2)
                //                                             ->schema([
                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                             <p class="text-lg">B.03 TEMPAT TINGGAL DOMISILI</p>
                //                                             <p class="text-lg">IBU KANDUNG</p>
                //                                         </div>')),
                //                                             ])->hidden(fn(Get $get) =>
                //                                             $get('ik_kk_sama_ak_id') == null ||
                //                                                 $get('ik_kk_sama_ak_id') != 2 ||
                //                                                 $get('ik_status_id') != 1),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('al_ik_tgldi_ln_id')
                //                                                     ->label('Apakah tinggal di luar negeri?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Textarea::make('al_ik_almt_ln')
                //                                                     ->label('Alamat Luar Negeri')
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 1 ||
                //                                                         $get('ik_status_id') != 1),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('al_ik_stts_rmh_id')
                //                                                     ->label('Status Kepemilikan Rumah')
                //                                                     ->placeholder('Pilih Status Kepemilikan Rumah')
                //                                                     ->options(Statuskepemilikanrumah::whereIsActive(1)->pluck('status_kepemilikan_rumah', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('al_ik_provinsi_id')
                //                                                     ->label('Provinsi')
                //                                                     ->placeholder('Pilih Provinsi')
                //                                                     ->options(Provinsi::all()->pluck('provinsi', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1)
                //                                                     ->afterStateUpdated(function (Set $set) {
                //                                                         $set('al_ik_kabupaten_id', null);
                //                                                         $set('al_ik_kecamatan_id', null);
                //                                                         $set('al_ik_kelurahan_id', null);
                //                                                         $set('al_ik_kodepos', null);
                //                                                     }),

                //                                                 Select::make('al_ik_kabupaten_id')
                //                                                     ->label('Kabupaten')
                //                                                     ->placeholder('Pilih Kabupaten')
                //                                                     ->options(fn(Get $get): Collection => Kabupaten::query()
                //                                                         ->where('provinsi_id', $get('al_ik_provinsi_id'))
                //                                                         ->pluck('kabupaten', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('al_ik_kecamatan_id')
                //                                                     ->label('Kecamatan')
                //                                                     ->placeholder('Pilih Kecamatan')
                //                                                     ->options(fn(Get $get): Collection => Kecamatan::query()
                //                                                         ->where('kabupaten_id', $get('al_ik_kabupaten_id'))
                //                                                         ->pluck('kecamatan', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                                 Select::make('al_ik_kelurahan_id')
                //                                                     ->label('Kelurahan')
                //                                                     ->placeholder('Pilih Kelurahan')
                //                                                     ->options(fn(Get $get): Collection => Kelurahan::query()
                //                                                         ->where('kecamatan_id', $get('al_ik_kecamatan_id'))
                //                                                         ->pluck('kelurahan', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1)
                //                                                     ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                //                                                         if (($get('al_ik_kodepos') ?? '') !== Str::slug($old)) {
                //                                                             return;
                //                                                         }

                //                                                         $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                //                                                         $state = $kodepos;

                //                                                         foreach ($state as $state) {
                //                                                             $set('al_ik_kodepos', Str::substr($state, 12, 5));
                //                                                         }
                //                                                     }),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 TextInput::make('al_ik_kodepos')
                //                                                     ->label('Kodepos')
                //                                                     ->disabled()
                //                                                     ->required()
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([


                //                                                 TextInput::make('al_ik_rt')
                //                                                     ->label('RT')
                //                                                     ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                                     ->required()
                //                                                     ->numeric()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_ik_kodepos') == null)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                                 TextInput::make('al_ik_rw')
                //                                                     ->label('RW')
                //                                                     ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                                     ->required()
                //                                                     ->numeric()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_ik_kodepos') == null)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Textarea::make('al_ik_alamat')
                //                                                     ->label('Alamat')
                //                                                     ->required()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_ik_kodepos') == null)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ik_kk_sama_ak_id') != 2 ||
                //                                                         $get('al_ik_tgldi_ln_id') != 2 ||
                //                                                         $get('ik_status_id') != 1),

                //                                             ]),

                //                                     ])->compact(),

                //                                 // WALI

                //                                 Section::make('C. WALI')
                //                                     ->schema([

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 ToggleButtons::make('w_status_id')
                //                                                     ->label('Status')
                //                                                     // ->placeholder('Pilih Status')
                //                                                     ->inline()
                //                                                     ->options(function (Get $get) {

                //                                                         if (($get('ak_status_id') == 1 && $get('ik_status_id') == 1)) {
                //                                                             return ([
                //                                                                 1 => 'Sama dengan ayah kandung',
                //                                                                 2 => 'Sama dengan ibu kandung',
                //                                                                 3 => 'Lainnya'
                //                                                             ]);
                //                                                         } elseif (($get('ak_status_id') == 1 && $get('ik_status_id') !== 1)) {
                //                                                             return ([
                //                                                                 1 => 'Sama dengan ayah kandung',
                //                                                                 3 => 'Lainnya'
                //                                                             ]);
                //                                                         } elseif (($get('ak_status_id') !== 1 && $get('ik_status_id') == 1)) {
                //                                                             return ([
                //                                                                 2 => 'Sama dengan ibu kandung',
                //                                                                 3 => 'Lainnya'
                //                                                             ]);
                //                                                         } elseif (($get('ak_status_id') !== 1 && $get('ik_status_id') !== 1)) {
                //                                                             return ([
                //                                                                 3 => 'Lainnya'
                //                                                             ]);
                //                                                         }
                //                                                     })
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('w_status_id') == 3) {
                //                                                             $set('w_kewarganegaraan_id', 1);
                //                                                         }
                //                                                     }),
                //                                                 // ->native(false),

                //                                             ]),

                //                                         Grid::make(2)

                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('w_status_id') != 3)
                //                                             ->schema([

                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                             <p class="text-lg">C.01 STATUS WALI</p>
                //                                         </div>')),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('w_hubungan_id')
                //                                                     ->label('Hubungan wali dengan calon santri')
                //                                                     ->placeholder('Pilih Hubungan')
                //                                                     ->options(HubunganWali::whereIsActive(1)->pluck('hubungan_wali', 'id'))
                //                                                     ->required()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('w_nama_lengkap_sama_id')
                //                                                     ->label('Apakah Nama sama dengan Nama Kepala Keluarga?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3 ||
                //                                                         $get('ak_nama_lengkap_sama_id') != 2 ||
                //                                                         $get('ik_nama_lengkap_sama_id') != 2)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('w_nama_lengkap_sama_id') == 1) {
                //                                                             $set('w_nama_lengkap', $get('nama_kpl_kel_santri'));
                //                                                         } else {
                //                                                             $set('w_nama_lengkap', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                                 TextInput::make('w_nama_lengkap')
                //                                                     ->label('Nama Lengkap')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('w_nama_lengkap_sama_id') == 1)
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 TextInput::make('w_nama_kunyah')
                //                                                     ->label('Nama Hijroh/Islami/Panggilan')
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('w_kewarganegaraan_id')
                //                                                     ->label('Kewarganegaraan')
                //                                                     // ->placeholder('Pilih Kewarganegaraan')
                //                                                     ->inline()
                //                                                     ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                //                                                     ->default(1)
                //                                                     ->live()
                //                                                     // ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ComponentsTextInput::make('w_nik')
                //                                                     ->label('NIK')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->regex('/^[0-9]*$/')
                //                                                     ->length(16)
                //                                                     ->maxLength(16)
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_kewarganegaraan_id') != 1 ||
                //                                                         $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 TextInput::make('w_asal_negara')
                //                                                     ->label('Asal Negara')
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_kewarganegaraan_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                                 TextInput::make('w_kitas')
                //                                                     ->label('KITAS')
                //                                                     ->hint('Nomor Izin Tinggal (KITAS)')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_kewarganegaraan_id') != 2 ||
                //                                                         $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 TextInput::make('w_tempat_lahir')
                //                                                     ->label('Tempat Lahir')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                                 DatePicker::make('w_tanggal_lahir')
                //                                                     ->label('Tanggal Lahir')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->maxDate(now())
                //                                                     ->required()
                //                                                     // ->format('dd/mm/yyyy')
                //                                                     ->displayFormat('d M Y')
                //                                                     // ->native(false)
                //                                                     ->closeOnDateSelection()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(6)
                //                                             ->schema([

                //                                                 Select::make('w_pend_terakhir_id')
                //                                                     ->label('Pendidikan Terakhir')
                //                                                     ->placeholder('Pilih Pendidikan Terakhir')
                //                                                     ->options(PendidikanTerakhirWalisantri::whereIsActive(1)->pluck('pendidikan_terakhir_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                                 Select::make('w_pekerjaan_utama_id')
                //                                                     ->label('Pekerjaan Utama')
                //                                                     ->placeholder('Pilih Pekerjaan Utama')
                //                                                     ->options(PekerjaanUtamaWalisantri::whereIsActive(1)->pluck('pekerjaan_utama_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                                 Select::make('w_pghsln_rt_id')
                //                                                     ->label('Penghasilan Rata-Rata')
                //                                                     ->placeholder('Pilih Penghasilan Rata-Rata')
                //                                                     ->options(PenghasilanWalisantri::whereIsActive(1)->pluck('penghasilan_walisantri', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('w_tdk_hp_id')
                //                                                     ->label('Apakah memiliki nomor handphone?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('w_nomor_handphone_sama_id')
                //                                                     ->label('Apakah nomor handphone sama dengan Pendaftar?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_tdk_hp_id') != 1 ||
                //                                                         $get('ak_nomor_handphone_sama_id') != 2 ||
                //                                                         $get('ik_nomor_handphone_sama_id') != 2 ||
                //                                                         $get('w_status_id') != 3)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('w_nomor_handphone_sama_id') == 1) {
                //                                                             $set('w_nomor_handphone', $get('hp_komunikasi'));
                //                                                         } else {
                //                                                             $set('w_nomor_handphone', null);
                //                                                         }
                //                                                     })->columnSpanFull(),

                //                                                 TextInput::make('w_nomor_handphone')
                //                                                     ->label('No. Handphone')
                //                                                     ->helperText('Contoh: 82187782223')
                //                                                     // ->mask('82187782223')
                //                                                     ->prefix('+62')
                //                                                     ->tel()
                //                                                     ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('w_nomor_handphone_sama_id') == 1)
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_tdk_hp_id') != 1 ||
                //                                                         $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                  <p class="text-lg">Kajian yang diikuti</p>
                //                              </div>'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Textarea::make('w_ustadz_kajian')
                //                                                     ->label('Ustadz yang mengisi kajian')
                //                                                     ->required()
                //                                                     // ->default('4232')
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 TextArea::make('w_tempat_kajian')
                //                                                     ->label('Tempat kajian yang diikuti')
                //                                                     ->required()
                //                                                     // ->default('4232')
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         // KARTU KELUARGA WALI
                //                                         Grid::make(2)
                //                                             ->schema([
                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                             <p class="text-lg">C.02 KARTU KELUARGA</p>
                //                             <p class="text-lg">WALI</p>
                //                          </div>'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('w_kk_sama_pendaftar_id')
                //                                                     ->label('Apakah KK dan Nama Kepala Keluarga sama dengan Pendaftar?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('ak_kk_sama_pendaftar_id') != 2 ||
                //                                                         $get('ik_kk_sama_pendaftar_id') != 2 ||
                //                                                         $get('w_status_id') != 3)
                //                                                     ->afterStateUpdated(function (Get $get, Set $set) {

                //                                                         if ($get('w_kk_sama_pendaftar_id') == 1) {
                //                                                             $set('w_no_kk', $get('kartu_keluarga_santri'));
                //                                                             $set('w_kep_kel_kk', $get('nama_kpl_kel_santri'));
                //                                                         } else {
                //                                                             $set('w_no_kk', null);
                //                                                             $set('w_kep_kel_kk', null);
                //                                                         }
                //                                                     })->columnSpanFull(),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ComponentsTextInput::make('w_no_kk')
                //                                                     ->label('No. KK Wali')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->length(16)
                //                                                     ->maxLength(16)
                //                                                     ->required()
                //                                                     ->regex('/^[0-9]*$/')
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('w_kk_sama_pendaftar_id') == 1)
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                                 TextInput::make('w_kep_kel_kk')
                //                                                     ->label('Nama Kepala Keluarga')
                //                                                     ->hint('Isi sesuai dengan KK')
                //                                                     ->hintColor('danger')
                //                                                     ->required()
                //                                                     // ->disabled(fn (Get $get) =>
                //                                                     // $get('w_kk_sama_pendaftar_id') == 1)
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),
                //                                             ]),


                //                                         // ALAMAT WALI
                //                                         Grid::make(2)
                //                                             ->schema([
                //                                                 Placeholder::make('')
                //                                                     ->content(new HtmlString('<div class="border-b">
                //                                             <p class="text-lg">C.03 TEMPAT TINGGAL DOMISILI</p>
                //                                             <p class="text-lg">WALI</p>
                //                                         </div>'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 ToggleButtons::make('al_w_tgldi_ln_id')
                //                                                     ->label('Apakah tinggal di luar negeri?')
                //                                                     ->live()
                //                                                     ->inline()
                //                                                     ->grouped()
                //                                                     ->boolean()
                //                                                     ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id'))
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Textarea::make('al_w_almt_ln')
                //                                                     ->label('Alamat Luar Negeri')
                //                                                     ->required()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 1),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('al_w_stts_rmh_id')
                //                                                     ->label('Status Kepemilikan Rumah')
                //                                                     ->placeholder('Pilih Status Kepemilikan Rumah')
                //                                                     ->options(Statuskepemilikanrumah::whereIsActive(1)->pluck('status_kepemilikan_rumah', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('al_w_provinsi_id')
                //                                                     ->label('Provinsi')
                //                                                     ->placeholder('Pilih Provinsi')
                //                                                     ->options(Provinsi::all()->pluck('provinsi', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3)
                //                                                     ->afterStateUpdated(function (Set $set) {
                //                                                         $set('al_w_kabupaten_id', null);
                //                                                         $set('al_w_kecamatan_id', null);
                //                                                         $set('al_w_kelurahan_id', null);
                //                                                         $set('al_w_kodepos', null);
                //                                                     }),

                //                                                 Select::make('al_w_kabupaten_id')
                //                                                     ->label('Kabupaten')
                //                                                     ->placeholder('Pilih Kabupaten')
                //                                                     ->options(fn(Get $get): Collection => Kabupaten::query()
                //                                                         ->where('provinsi_id', $get('al_w_provinsi_id'))
                //                                                         ->pluck('kabupaten', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 Select::make('al_w_kecamatan_id')
                //                                                     ->label('Kecamatan')
                //                                                     ->placeholder('Pilih Kecamatan')
                //                                                     ->options(fn(Get $get): Collection => Kecamatan::query()
                //                                                         ->where('kabupaten_id', $get('al_w_kabupaten_id'))
                //                                                         ->pluck('kecamatan', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                                 Select::make('al_w_kelurahan_id')
                //                                                     ->label('Kelurahan')
                //                                                     ->placeholder('Pilih Kelurahan')
                //                                                     ->options(fn(Get $get): Collection => Kelurahan::query()
                //                                                         ->where('kecamatan_id', $get('al_w_kecamatan_id'))
                //                                                         ->pluck('kelurahan', 'id'))
                //                                                     // ->searchable()
                //                                                     ->required()
                //                                                     ->live()
                //                                                     ->native(false)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3)
                //                                                     ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                //                                                         if (($get('al_w_kodepos') ?? '') !== Str::slug($old)) {
                //                                                             return;
                //                                                         }

                //                                                         $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                //                                                         $state = $kodepos;

                //                                                         foreach ($state as $state) {
                //                                                             $set('al_w_kodepos', Str::substr($state, 12, 5));
                //                                                         }
                //                                                     }),

                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([

                //                                                 TextInput::make('al_w_kodepos')
                //                                                     ->label('Kodepos')
                //                                                     ->disabled()
                //                                                     ->required()
                //                                                     ->dehydrated()
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),
                //                                             ]),

                //                                         Grid::make(4)
                //                                             ->schema([


                //                                                 TextInput::make('al_w_rt')
                //                                                     ->label('RT')
                //                                                     ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                                     ->required()
                //                                                     ->numeric()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_w_kodepos') == null)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                                 TextInput::make('al_w_rw')
                //                                                     ->label('RW')
                //                                                     ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                                     ->required()
                //                                                     ->numeric()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_w_kodepos') == null)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                             ]),

                //                                         Grid::make(2)
                //                                             ->schema([

                //                                                 Textarea::make('al_w_alamat')
                //                                                     ->label('Alamat')
                //                                                     ->required()
                //                                                     ->disabled(fn(Get $get) =>
                //                                                     $get('al_w_kodepos') == null)
                //                                                     ->hidden(fn(Get $get) =>
                //                                                     $get('al_w_tgldi_ln_id') != 2 ||
                //                                                         $get('w_status_id') != 3),

                //                                             ]),



                //                                     ])->compact()

                //                             ])
                //                     ]),
                //                 // end of Walisantri Tab

                //                 Tabs\Tab::make('Santri')
                //                     ->schema([

                //                         Group::make()
                //                             ->relationship('santri')
                //                             ->schema([
                //                                 //SANTRI
                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b">
                //                                                 <p class="text-2xl">SANTRI</p>
                //                                             </div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('nama_lengkap')
                //                                             ->label('Nama Lengkap')
                //                                             ->hint('Isi sesuai dengan KK')
                //                                             ->hintColor('danger')
                //                                             //->default('asfasdad')
                //                                             ->required(),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('kartu_keluarga')
                //                                             ->label('Nomor KK Calon Santri')
                //                                             ->length(16)
                //                                             ->required()
                //                                             // ->disabled(fn (Get $get) =>
                //                                             // $get('kartu_keluarga_sama') !== 'KK Sendiri')
                //                                             ->dehydrated(),

                //                                         TextInput::make('nama_kpl_kel')
                //                                             ->label('Nama Kepala Keluarga')
                //                                             ->required()
                //                                             // ->disabled(fn (Get $get) =>
                //                                             // $get('kartu_keluarga_sama') !== 'KK Sendiri')
                //                                             ->dehydrated(),
                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         ToggleButtons::make('kewarganegaraan_id')
                //                                             ->label('Kewarganegaraan')
                //                                             ->inline()
                //                                             ->options(Kewarganegaraan::whereIsActive(1)->pluck('kewarganegaraan', 'id'))
                //                                             ->default(1)
                //                                             ->live(),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         ComponentsTextInput::make('nik')
                //                                             ->label('NIK')
                //                                             ->hint('Isi sesuai dengan KK')
                //                                             ->hintColor('danger')
                //                                             ->regex('/^[0-9]*$/')
                //                                             ->length(16)
                //                                             ->maxLength(16)
                //                                             ->required()
                //                                             ->unique(Santri::class, 'nik')
                //                                             //->default('3295131306822002')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('kewarganegaraan_id') != 1),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('asal_negara')
                //                                             ->label('Asal Negara Calon Santri')
                //                                             ->required()
                //                                             //->default('asfasdad')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('kewarganegaraan_id') != 2),

                //                                         TextInput::make('kitas')
                //                                             ->label('KITAS Calon Santri')
                //                                             ->hint('Nomor Izin Tinggal (KITAS)')
                //                                             ->hintColor('danger')
                //                                             ->required()
                //                                             //->default('3295131306822002')
                //                                             ->unique(Santri::class, 'kitas')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('kewarganegaraan_id') != 2),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('nama_panggilan')
                //                                             ->label('Nama Hijroh/Islami/Panggilan')
                //                                             //->default('asfasdad')
                //                                             ->required(),

                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b">
                //                                             </div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         ToggleButtons::make('jeniskelamin_id')
                //                                             ->label('Jenis Kelamin')
                //                                             ->inline()
                //                                             ->options(Jeniskelamin::whereIsActive(1)->pluck('jeniskelamin', 'id'))
                //                                             ->required(),

                //                                     ]),

                //                                 Grid::make(6)
                //                                     ->schema([

                //                                         TextInput::make('tempat_lahir')
                //                                             ->label('Tempat Lahir')
                //                                             ->hint('Isi sesuai dengan KK')
                //                                             ->hintColor('danger')
                //                                             //->default('asfasdad')
                //                                             ->required(),

                //                                         DatePicker::make('tanggal_lahir')
                //                                             ->label('Tanggal Lahir')
                //                                             ->hint('Isi sesuai dengan KK')
                //                                             ->hintColor('danger')
                //                                             //->default('20010101')
                //                                             ->required()
                //                                             ->displayFormat('d M Y')
                //                                             ->native(false)
                //                                             ->live()
                //                                             ->closeOnDateSelection()
                //                                             ->afterStateUpdated(function (Set $set, $state) {
                //                                                 $set('umur', Carbon::parse($state)->age);
                //                                             }),

                //                                         TextInput::make('umur')
                //                                             ->label('Umur')
                //                                             ->disabled()
                //                                             ->dehydrated()
                //                                             ->required(),

                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('anak_ke')
                //                                             ->label('Anak ke-')
                //                                             ->required()
                //                                             //->default('3')
                //                                             ->rules([
                //                                                 fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                //                                                     $anakke = $get('anak_ke');
                //                                                     $psjumlahsaudara = $get('jumlah_saudara');
                //                                                     $jumlahsaudara = $psjumlahsaudara + 1;

                //                                                     if ($anakke > $jumlahsaudara) {
                //                                                         $fail("Anak ke tidak bisa lebih dari jumlah saudara + 1");
                //                                                     }
                //                                                 },
                //                                             ]),

                //                                         TextInput::make('jumlah_saudara')
                //                                             ->label('Jumlah saudara')
                //                                             //->default('5')
                //                                             ->required(),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('agama')
                //                                             ->label('Agama')
                //                                             ->default('Islam')
                //                                             ->disabled()
                //                                             ->required()
                //                                             ->dehydrated(),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         Select::make('cita_cita_id')
                //                                             ->label('Cita-cita')
                //                                             ->placeholder('Pilih Cita-cita')
                //                                             ->options(Cita::whereIsActive(1)->pluck('cita', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             ->live()
                //                                             ->native(false),

                //                                         TextInput::make('cita_cita_lainnya')
                //                                             ->label('Cita-cita Lainnya')
                //                                             ->required()
                //                                             //->default('asfasdad')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('cita_cita_id') != 10),
                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([
                //                                         Select::make('hobi_id')
                //                                             ->label('Hobi')
                //                                             ->placeholder('Pilih Hobi')
                //                                             ->options(Hobi::whereIsActive(1)->pluck('hobi', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('Lainnya')
                //                                             ->live()
                //                                             ->native(false),

                //                                         TextInput::make('hobi_lainnya')
                //                                             ->label('Hobi Lainnya')
                //                                             ->required()
                //                                             //->default('asfasdad')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('hobi_id') != 6),

                //                                     ]),


                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(4)
                //                                     ->schema([
                //                                         Select::make('keb_khus_id')
                //                                             ->label('Kebutuhan Khusus')
                //                                             ->placeholder('Pilih Kebutuhan Khusus')
                //                                             ->options(KebutuhanKhusus::whereIsActive(1)->pluck('kebutuhan_khusus', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('Lainnya')
                //                                             ->live()
                //                                             ->native(false),

                //                                         TextInput::make('keb_khus_lainnya')
                //                                             ->label('Kebutuhan Khusus Lainnya')
                //                                             ->required()
                //                                             //->default('asfasdad')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('keb_khus_id') != 6),
                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([
                //                                         Select::make('keb_dis_id')
                //                                             ->label('Kebutuhan Disabilitas')
                //                                             ->placeholder('Pilih Kebutuhan Disabilitas')
                //                                             ->options(KebutuhanDisabilitas::whereIsActive(1)->pluck('kebutuhan_disabilitas', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('Lainnya')
                //                                             ->live()
                //                                             ->native(false),

                //                                         TextInput::make('keb_dis_lainnya')
                //                                             ->label('Kebutuhan Disabilitas Lainnya')
                //                                             ->required()
                //                                             //->default('asfasdad')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('keb_dis_id') != 8),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         ToggleButtons::make('tdk_hp_id')
                //                                             ->label('Apakah memiliki nomor handphone?')
                //                                             ->live()
                //                                             ->inline()
                //                                             ->grouped()
                //                                             ->boolean()
                //                                             ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id')),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('nomor_handphone')
                //                                             ->label('No. Handphone')
                //                                             ->helperText('Contoh: 82187782223')
                //                                             // ->mask('82187782223')
                //                                             ->prefix('+62')
                //                                             ->tel()
                //                                             //->default('82187782223')
                //                                             ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                //                                             ->required()
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('tdk_hp_id') != 1),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('email')
                //                                             ->label('Email')
                //                                             //->default('mail@mail.com')
                //                                             ->email(),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(2)
                //                                     ->schema([

                //                                         ToggleButtons::make('ps_mendaftar_keinginan_id')
                //                                             ->label('Mendaftar atas kenginginan')
                //                                             ->inline()
                //                                             ->options(MendaftarKeinginan::whereIsActive(1)->pluck('mendaftar_keinginan', 'id'))
                //                                             ->live(),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('ps_mendaftar_keinginan_lainnya')
                //                                             ->label('Lainnya')
                //                                             ->required()
                //                                             //->default('asdasf')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('ps_mendaftar_keinginan_id') != 4),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Hidden::make('aktivitaspend_id')
                //                                     ->default(9),

                //                                 Grid::make(2)
                //                                     ->schema([

                //                                         ToggleButtons::make('bya_sklh_id')
                //                                             ->label('Yang membiayai sekolah')
                //                                             ->inline()
                //                                             ->options(MembiayaiSekolah::whereIsActive(1)->pluck('membiayai_sekolah', 'id'))
                //                                             ->live(),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('bya_sklh_lainnya')
                //                                             ->label('Yang membiayai sekolah lainnya')
                //                                             ->required()
                //                                             //->default('asfasdad')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('bya_sklh_id') != 4),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         ToggleButtons::make('belum_nisn_id')
                //                                             ->label('Apakah memiliki NISN?')
                //                                             ->helperText(new HtmlString('<strong>NISN</strong> adalah Nomor Induk Siswa Nasional'))
                //                                             ->live()
                //                                             ->inline()
                //                                             ->grouped()
                //                                             ->boolean()
                //                                             ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id')),

                //                                         TextInput::make('nisn')
                //                                             ->label('Nomor NISN')
                //                                             ->required()
                //                                             //->default('2421324')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('belum_nisn_id') != 1),
                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         ToggleButtons::make('nomor_kip_memiliki_id')
                //                                             ->label('Apakah memiliki KIP?')
                //                                             ->helperText(new HtmlString('<strong>KIP</strong> adalah Kartu Indonesia Pintar'))
                //                                             ->live()
                //                                             ->inline()
                //                                             ->grouped()
                //                                             ->boolean()
                //                                             ->options(YaTidak::whereIsActive(1)->pluck('ya_tidak', 'id')),

                //                                         TextInput::make('nomor_kip')
                //                                             ->label('Nomor KIP')
                //                                             ->required()
                //                                             //->default('32524324')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('nomor_kip_memiliki_id') != 1),
                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 Grid::make(2)
                //                                     ->schema([

                //                                         Textarea::make('ps_peng_pend_agama')
                //                                             ->label('Pengalaman pendidikan agama')
                //                                             ->required(),

                //                                     ]),

                //                                 Grid::make(2)
                //                                     ->schema([

                //                                         Textarea::make('ps_peng_pend_formal')
                //                                             ->label('Pengalaman pendidikan formal')
                //                                             ->required(),
                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         Select::make('hafalan_id')
                //                                             ->label('Hafalan')
                //                                             ->placeholder('Pilih Yang membiayai sekolah')
                //                                             ->options(Hafalan::whereIsActive(1)->pluck('hafalan', 'id'))
                //                                             ->required()
                //                                             ->suffix('juz')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('qism_id') == 1)
                //                                             ->native(false),

                //                                     ]),

                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b"></div>')),

                //                                 // ALAMAT SANTRI
                //                                 Placeholder::make('')
                //                                     ->content(new HtmlString('<div class="border-b">
                //                                                 <p class="text-lg">TEMPAT TINGGAL DOMISILI</p>
                //                                                 <p class="text-lg">SANTRI</p>
                //                                             </div>')),

                //                                 Grid::make(2)
                //                                     ->schema([

                //                                         ToggleButtons::make('al_s_status_mukim_id')
                //                                             ->label('Apakah mukim di Pondok?')
                //                                             ->helperText(new HtmlString('Pilih <strong>Tidak Mukim</strong> khusus bagi pendaftar <strong>Tarbiyatul Aulaad</strong> dan <strong>Pra Tahfidz kelas 1-4</strong>'))
                //                                             ->live()
                //                                             ->inline()
                //                                             ->required()
                //                                             ->default(function (Get $get) {

                //                                                 $qism = $get('qism_id');

                //                                                 $kelas = $get('kelas_id');

                //                                                 if ($qism == 1) {

                //                                                     return 2;
                //                                                 } elseif ($qism == 2 && $kelas == 1) {

                //                                                     return 2;
                //                                                 } elseif ($qism == 2 && $kelas == 1) {

                //                                                     return 2;
                //                                                 } elseif ($qism == 2 && $kelas == 2) {

                //                                                     return 2;
                //                                                 } elseif ($qism == 2 && $kelas == 3) {

                //                                                     return 2;
                //                                                 } elseif ($qism == 2 && $kelas == 4) {

                //                                                     return 2;
                //                                                 } else {
                //                                                     return 1;
                //                                                 }
                //                                             })
                //                                             ->options(function (Get $get) {

                //                                                 $qism = $get('qism_id');

                //                                                 $kelas = $get('kelas_id');

                //                                                 if ($qism == 1) {

                //                                                     return ([
                //                                                         2 => 'Tidak Mukim'
                //                                                     ]);
                //                                                 } elseif ($qism == 2 && $kelas == 1) {

                //                                                     return ([
                //                                                         2 => 'Tidak Mukim',
                //                                                     ]);
                //                                                 } elseif ($qism == 2 && $kelas == 1) {

                //                                                     return ([
                //                                                         2 => 'Tidak Mukim',
                //                                                     ]);
                //                                                 } elseif ($qism == 2 && $kelas == 2) {

                //                                                     return ([
                //                                                         2 => 'Tidak Mukim',
                //                                                     ]);
                //                                                 } elseif ($qism == 2 && $kelas == 3) {

                //                                                     return ([
                //                                                         2 => 'Tidak Mukim',
                //                                                     ]);
                //                                                 } elseif ($qism == 2 && $kelas == 4) {

                //                                                     return ([
                //                                                         2 => 'Tidak Mukim',
                //                                                     ]);
                //                                                 } else {
                //                                                     return ([

                //                                                         1 => 'Mukim',
                //                                                     ]);
                //                                                 }
                //                                             })
                //                                             ->afterStateUpdated(function (Get $get, Set $set) {
                //                                                 if ($get('al_s_status_mukim_id') == 1) {

                //                                                     $set('al_s_stts_tptgl_id', 10);
                //                                                 } elseif ($get('al_s_status_mukim_id') == 2) {

                //                                                     $set('al_s_stts_tptgl_id', null);
                //                                                 }
                //                                             }),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         Select::make('al_s_stts_tptgl_id')
                //                                             ->label('Status tempat tinggal')
                //                                             ->placeholder('Status tempat tinggal')
                //                                             ->options(function (Get $get) {
                //                                                 if ($get('al_s_status_mukim_id') == 2) {
                //                                                     return (StatusTempatTinggal::whereIsActive(1)->pluck('status_tempat_tinggal', 'id'));
                //                                                 }
                //                                             })
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('Kontrak/Kost')
                //                                             ->hidden(fn(Get $get) =>
                //                                             $get('al_s_status_mukim_id') == 1)
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->dehydrated(),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         Select::make('al_s_provinsi_id')
                //                                             ->label('Provinsi')
                //                                             ->placeholder('Pilih Provinsi')
                //                                             ->options(Provinsi::all()->pluck('provinsi', 'id'))
                //                                             // ->searchable()
                //                                             //->default('35')
                //                                             ->required()
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             )
                //                                             ->afterStateUpdated(function (Set $set) {
                //                                                 $set('al_s_kabupaten_id', null);
                //                                                 $set('al_s_kecamatan_id', null);
                //                                                 $set('al_s_kelurahan_id', null);
                //                                                 $set('al_s_kodepos', null);
                //                                             }),

                //                                         Select::make('al_s_kabupaten_id')
                //                                             ->label('Kabupaten')
                //                                             ->placeholder('Pilih Kabupaten')
                //                                             ->options(fn(Get $get): Collection => Kabupaten::query()
                //                                                 ->where('provinsi_id', $get('al_s_provinsi_id'))
                //                                                 ->pluck('kabupaten', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('232')
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         Select::make('al_s_kecamatan_id')
                //                                             ->label('Kecamatan')
                //                                             ->placeholder('Pilih Kecamatan')
                //                                             ->options(fn(Get $get): Collection => Kecamatan::query()
                //                                                 ->where('kabupaten_id', $get('al_s_kabupaten_id'))
                //                                                 ->pluck('kecamatan', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('3617')
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                         Select::make('al_s_kelurahan_id')
                //                                             ->label('Kelurahan')
                //                                             ->placeholder('Pilih Kelurahan')
                //                                             ->options(fn(Get $get): Collection => Kelurahan::query()
                //                                                 ->where('kecamatan_id', $get('al_s_kecamatan_id'))
                //                                                 ->pluck('kelurahan', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('45322')
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             )
                //                                             ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                //                                                 $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                //                                                 $state = $kodepos;

                //                                                 foreach ($state as $state) {
                //                                                     $set('al_s_kodepos', Str::substr($state, 12, 5));
                //                                                 }
                //                                             }),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         TextInput::make('al_s_kodepos')
                //                                             ->label('Kodepos')
                //                                             ->disabled()
                //                                             ->required()
                //                                             ->dehydrated()
                //                                             //->default('63264')
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([


                //                                         TextInput::make('al_s_rt')
                //                                             ->label('RT')
                //                                             ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                             ->required()
                //                                             ->numeric()
                //                                             ->disabled(fn(Get $get) =>
                //                                             $get('al_s_kodepos') == null)
                //                                             //->default('2')
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                         TextInput::make('al_s_rw')
                //                                             ->label('RW')
                //                                             ->helperText('Isi 0 jika tidak ada RT/RW')
                //                                             ->required()
                //                                             ->numeric()
                //                                             ->disabled(fn(Get $get) =>
                //                                             $get('al_s_kodepos') == null)
                //                                             //->default('2')
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                     ]),

                //                                 Grid::make(2)
                //                                     ->schema([

                //                                         Textarea::make('al_s_alamat')
                //                                             ->label('Alamat')
                //                                             ->required()
                //                                             ->disabled(fn(Get $get) =>
                //                                             $get('al_s_kodepos') == null)
                //                                             //->default('sdfsdasdada')
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 1 ||
                //                                                     $get('al_s_stts_tptgl_id') == 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == 3 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([
                //                                         Select::make('al_s_jarak_id')
                //                                             ->label('Jarak tempat tinggal ke Pondok Pesantren')
                //                                             ->options(Jarakpp::whereIsActive(1)->pluck('jarak_kepp', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('Kurang dari 5 km')
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                         Select::make('al_s_transportasi_id')
                //                                             ->label('Transportasi ke Pondok Pesantren')
                //                                             ->options(Transpp::whereIsActive(1)->pluck('transportasi_kepp', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('Ojek')
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                     ]),

                //                                 Grid::make(4)
                //                                     ->schema([

                //                                         Select::make('al_s_waktu_tempuh_id')
                //                                             ->label('Waktu tempuh ke Pondok Pesantren')
                //                                             ->options(Waktutempuh::whereIsActive(1)->pluck('waktu_tempuh', 'id'))
                //                                             // ->searchable()
                //                                             ->required()
                //                                             //->default('10 - 19 menit')
                //                                             ->live()
                //                                             ->native(false)
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),

                //                                         TextInput::make('al_s_koordinat')
                //                                             ->label('Titik koordinat tempat tinggal')
                //                                             //->default('sfasdadasdads')
                //                                             ->hidden(
                //                                                 fn(Get $get) =>
                //                                                 $get('al_s_status_mukim_id') != 2 ||
                //                                                     $get('al_s_stts_tptgl_id') == null
                //                                             ),
                //                                     ]),
                //                             ]),
                //                     ]),
                //                 // end of Santri Tab





                //             ])->columnSpanFull()

                //     ])
                //     ->after(function ($record) {

                //         $walisantri = Walisantri::where('user_id', Auth::user()->id)->first();
                //         $walisantri->ws_emis4 = '1';
                //         $walisantri->save();

                //         $santri = ModelsSantri::where('id', $record->santri_id)->first();
                //         $santri->s_emis4 = '1';
                //         $santri->save();

                //         Notification::make()
                //             ->success()
                //             ->title('Alhamdulillah data telah tersimpan')
                //             ->persistent()
                //             ->color('success')
                //             ->send();
                //     }),

            ]);
    }
}
