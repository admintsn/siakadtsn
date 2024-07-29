<?php

namespace App\Filament\Tsn\Resources\PengajarResource\Widgets;

use App\Models\Pengajar as ModelsPengajar;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\KelasSantri;
use App\Models\Santri as ModelsSantri;
use App\Models\TahunBerjalan;
use App\Models\Walisantri;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
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
use App\Models\NismPerTahun;
use App\Models\Pendaftar;
use App\Models\Provinsi;
use App\Models\Qism;
use App\Models\QismDetailHasKelas;
use App\Models\Semester;
use App\Models\TahunAjaran;
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
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;
use stdClass;
use Filament\Tables\Grouping\Group as GroupingGroup;

class Pengajar extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {

        return $table
            ->heading('Profil')
            ->description('Silakan melengkapi data Profil untuk keperluan kelengkapan data EMIS KEMENAG.  Jazaakumullahu khoiron.')
            ->paginated(false)
            ->query(

                ModelsPengajar::where('user_id', Auth::user()->id)
            )
            ->columns([
                Stack::make([
                    TextColumn::make('nama')
                        ->label('Nama')
                        ->size(TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Bold),

                    TextColumn::make('is_lengkap')
                        ->label('Status Kelengkapan Data')
                        ->default('Belum Lengkap')
                        ->size(TextColumn\TextColumnSize::Large)
                        ->weight(FontWeight::Bold)
                        ->description(fn ($record): string => "Status Kelengkapan Data:", position: 'above')
                        ->formatStateUsing(function (Model $record) {
                            $semis4 = ModelsPengajar::where('id', $record->id)->first();

                            // dd($pendaftar->ps_kadm_status);
                            if ($semis4->is_lengkap === '1') {
                                return ('Lengkap');
                            } else {
                                return ('Belum Lengkap');
                            }
                        })
                        ->badge()
                        ->color(function (Model $record) {
                            $semis4 = ModelsPengajar::where('id', $record->id)->first();
                            // dd($pendaftar->ps_kadm_status);
                            if ($semis4->is_lengkap === '1') {
                                return ('success');
                            } else {
                                return ('danger');
                            }
                        }),

                    TextColumn::make('a')
                        ->default(new HtmlString('</br>Silakan melengkapi data Profil dengan klik tombol di bawah ini')),

                ])
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 2,
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->label('Edit Data')
                    ->modalCloseButton(false)
                    ->modalHeading(' ')
                    ->modalWidth('full')
                    // ->stickyModalHeader()
                    ->button()
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('Batal'))
                    ->form([

                        Placeholder::make('')
                            ->content(function (Model $record) {
                                return (new HtmlString('<div><p class="text-3xl"><strong>' . $record->nama . '</strong></p></div>'));
                            }),


                        //DATA DIRI
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>DATA DIRI</strong></p></div>')),

                                Grid::make(3)
                                    ->schema([

                                        TextInput::make('gelar_depan')
                                            ->label('Gelar Depan')
                                            ->helperText(new HtmlString('Contoh: <strong>Prof. Dr.</strong>')),

                                        TextInput::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->required(),

                                        TextInput::make('gelar_belakang')
                                            ->label('Gelar Belakang')
                                            ->helperText(new HtmlString('Contoh: <strong>S.pd M.pd</strong>')),

                                    ]),



                                TextInput::make('nik')
                                    ->label('NIK')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger')
                                    ->length(16)
                                    ->required(),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('status_kepegawaian')
                                            ->label('Status Kepegawaian')
                                            ->disabled(),

                                        DatePicker::make('tmt_pegawai')
                                            ->label('Terhitung Mulai Tanggal')
                                            ->helperText('Tanggal mulai bertugas')
                                            ->required()
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->native(false),

                                    ]),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('hp')
                                            ->label('No. Handphone')
                                            ->helperText('Contoh: 6282187782223')
                                            ->tel()
                                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                            ->required(),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email(),

                                    ]),

                                TextInput::make('npwp')
                                    ->label('NPWP'),

                                Radio::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'Laki-laki' => 'Laki-laki',
                                        'Perempuan' => 'Perempuan',
                                    ])
                                    ->required()
                                    ->inline(),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->required(),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->hint('Isi sesuai dengan KK')
                                            ->required()
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->native(false),

                                    ]),

                                TextInput::make('agama')
                                    ->label('Agama')
                                    ->disabled(),

                                Select::make('golongan_darah')
                                    ->label('Golongan Darah')
                                    ->placeholder('Pilih Golongan Darah')
                                    ->options([
                                        'Golongan Darah A' => 'Golongan Darah A',
                                        'Golongan Darah B' => 'Golongan Darah B',
                                        'Golongan Darah AB' => 'Golongan Darah AB',
                                        'Golongan Darah O' => 'Golongan Darah O',
                                    ])
                                    ->native(false),

                                Grid::make(3)
                                    ->schema([

                                        Select::make('pendidikan_terakhir')
                                            ->label('Pendidikan Terakhir')
                                            ->placeholder('Pilih Pendidikan Terakhir')
                                            ->options([
                                                'SD/Sederajat' => 'SD/Sederajat',
                                                'SMP/Sederajat' => 'SMP/Sederajat',
                                                'SMA/Sederajat' => 'SMA/Sederajat',
                                                'D1' => 'D1',
                                                'D2' => 'D2',
                                                'D3' => 'D3',
                                                'D4/S1' => 'D4/S1',
                                                'S2' => 'S2',
                                                'S3' => 'S3',
                                                'Tidak Memiliki Pendidikan Formal' => 'Tidak Memiliki Pendidikan Formal',
                                                'M1' => 'M1',
                                                'M2' => 'M2',
                                                'M3' => 'M3',
                                            ])
                                            ->required()
                                            ->native(false),

                                        TextInput::make('prodi_terakhir')
                                            ->label('Prodi Terakhir'),

                                        DatePicker::make('tanggal_ijazah')
                                            ->label('Tanggal Ijazah')
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->native(false),

                                    ]),

                            ])->columnSpanFull(),


                        //INFORMASI TEMPAT TINGGAL
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>INFORMASI TEMPAT TINGGAL</strong></p></div>')),

                                Select::make('status_tempat_tinggal')
                                    ->label('Status Tempat Tinggal')
                                    ->placeholder('Pilih Status Tempat Tinggal')
                                    ->options([
                                        'Milik Sendiri' => 'Milik Sendiri',
                                        'Rumah Orang Tua' => 'Rumah Orang Tua',
                                        'Rumah Saudara/kerabat' => 'Rumah Saudara/kerabat',
                                        'Rumah Dinas' => 'Rumah Dinas',
                                        'Sewa/kontrak' => 'Sewa/kontrak',
                                        'Lainnya' => 'Lainnya',
                                    ])
                                    ->required()
                                    ->native(false),

                                Grid::make(2)
                                    ->schema([

                                        Select::make('provinsi_id')
                                            ->label('Provinsi')
                                            ->placeholder('Pilih Provinsi')
                                            ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->afterStateUpdated(function (Set $set) {
                                                $set('kabupaten_id', null);
                                                $set('kecamatan_id', null);
                                                $set('kelurahan_id', null);
                                                $set('kodepos', null);
                                            }),

                                        Select::make('kabupaten_id')
                                            ->label('Kabupaten')
                                            ->placeholder('Pilih Kabupaten')
                                            ->options(fn (Get $get): Collection => Kabupaten::query()
                                                ->where('provinsi_id', $get('provinsi_id'))
                                                ->pluck('kabupaten', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        Select::make('kecamatan_id')
                                            ->label('Kecamatan')
                                            ->placeholder('Pilih Kecamatan')
                                            ->options(fn (Get $get): Collection => Kecamatan::query()
                                                ->where('kabupaten_id', $get('kabupaten_id'))
                                                ->pluck('kecamatan', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        Select::make('kelurahan_id')
                                            ->label('Kelurahan')
                                            ->placeholder('Pilih Kelurahan')
                                            ->options(fn (Get $get): Collection => Kelurahan::query()
                                                ->where('kecamatan_id', $get('kecamatan_id'))
                                                ->pluck('kelurahan', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                                                if (($get('kodepos') ?? '') !== Str::slug($old)) {
                                                    return;
                                                }

                                                $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                                                $state = $kodepos;

                                                foreach ($state as $state) {
                                                    $set('kodepos', Str::substr($state, 12, 5));
                                                }
                                            }),


                                        TextInput::make('rt')
                                            ->label('RT')
                                            ->required()
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        TextInput::make('rw')
                                            ->label('RW')
                                            ->required()
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        Textarea::make('alamat')
                                            ->label('Alamat')
                                            ->required()
                                            ->columnSpanFull()
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        TextInput::make('kodepos')
                                            ->label('Kodepos')
                                            ->disabled()
                                            ->required()
                                            ->dehydrated(),
                                    ]),

                                Grid::make(3)
                                    ->schema([

                                        Select::make('transportasi')
                                            ->label('Transportasi ke Pondok Pesantren')
                                            ->options([
                                                'Jalan kaki' => 'Jalan kaki',
                                                'Sepeda' => 'Sepeda',
                                                'Sepeda Motor' => 'Sepeda Motor',
                                                'Mobil Pribadi' => 'Mobil Pribadi',
                                                'Antar Jemput Sekolah' => 'Antar Jemput Sekolah',
                                                'Angkutan Umum' => 'Angkutan Umum',
                                                'Perahu/Sampan' => 'Perahu/Sampan',
                                                'Lainnya' => 'Lainnya',
                                            ])
                                            ->required()
                                            ->native(false),

                                        Select::make('jarak')
                                            ->label('Jarak tempat tinggal ke Pondok Pesantren')
                                            ->options([
                                                'Kurang dari 5 km' => 'Kurang dari 5 km',
                                                'Antara 5 - 10 Km' => 'Antara 5 - 10 Km',
                                                'Antara 11 - 20 Km' => 'Antara 11 - 20 Km',
                                                'Antara 21 - 30 Km' => 'Antara 21 - 30 Km',
                                                'Lebih dari 30 Km' => 'Lebih dari 30 Km',
                                            ])
                                            ->required()
                                            ->native(false),



                                        Select::make('waktu_tempuh')
                                            ->label('Waktu tempuh ke Pondok Pesantren')
                                            ->options([
                                                '1 - 10 menit' => '1 - 10 menit',
                                                '10 - 19 menit' => '10 - 19 menit',
                                                '20 - 29 menit' => '20 - 29 menit',
                                                '30 - 39 menit' => '30 - 39 menit',
                                                '1 - 2 jam' => '1 - 2 jam',
                                                '> 2 jam' => '> 2 jam',
                                            ])
                                            ->required()
                                            ->native(false),
                                    ]),







                            ])->columnSpanFull(),


                        //INFORMASI KELUARGA
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>INFORMASI KELUARGA</strong></p></div>')),

                                TextInput::make('nama_ibu_kandung')
                                    ->label('Nama Ibu Kandung')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger')
                                    ->required(),

                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>STATUS PERKAWINAN</strong></p></div>')),

                                Radio::make('status_perkawinan')
                                    ->label('Status Perkawinan')
                                    ->options([
                                        'Kawin' => 'Kawin',
                                        'Belum Kawin' => 'Belum Kawin',
                                        'Duda/Janda' => 'Duda/Janda',
                                    ])
                                    ->required()
                                    ->inline(),

                                TextInput::make('nomor_kk')
                                    ->label('Nomor KK')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger')
                                    ->length(16)
                                    ->required(),

                            ])->columnSpanFull(),

                        //DATA BANK
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>DATA BANK</strong></p></div>')),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('no_rekening')
                                            ->label('No Rekening'),

                                        TextInput::make('nama_rekening')
                                            ->label('Nama Rekening'),

                                        TextInput::make('nama_bank')
                                            ->label('Nama Bank'),

                                        TextInput::make('cabang_bank')
                                            ->label('Cabang Bank'),
                                    ]),

                            ])->columnSpanFull(),

                        //TUGAS UTAMA
                        Section::make('')
                            ->schema([

                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>TUGAS UTAMA</strong></p></div>')),

                                Select::make('tugas_utama')
                                    ->label('Tugas Utama')
                                    ->options([
                                        'Pengemudi' => 'Pengemudi',
                                        'Tenaga Keamanan' => 'Tenaga Keamanan',
                                        'Lainnya' => 'Lainnya',
                                        'Tenaga Administrasi' => 'Tenaga Administrasi',
                                        'Tenaga Pendidik' => 'Tenaga Pendidik',
                                        'Tenaga Perpustakaan' => 'Tenaga Perpustakaan',
                                        'Tenaga Laboratorium' => 'Tenaga Laboratorium',
                                        'Tenaga Kebersihan' => 'Tenaga Kebersihan',
                                        'Penjaga Sekolah/Pesuruh' => 'Penjaga Sekolah/Pesuruh',
                                    ])
                                    ->required()
                                    ->native(false),

                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>TUGAS TAMBAHAN</strong></p></div>')),

                                Select::make('tugas_tambahan')
                                    ->label('Tugas Tambahan')
                                    ->options([
                                        'Pengemudi' => 'Pengemudi',
                                        'Tenaga Keamanan' => 'Tenaga Keamanan',
                                        'Lainnya' => 'Lainnya',
                                        'Tenaga Administrasi' => 'Tenaga Administrasi',
                                        'Tenaga Pendidik' => 'Tenaga Pendidik',
                                        'Tenaga Perpustakaan' => 'Tenaga Perpustakaan',
                                        'Tenaga Laboratorium' => 'Tenaga Laboratorium',
                                        'Tenaga Kebersihan' => 'Tenaga Kebersihan',
                                        'Penjaga Sekolah/Pesuruh' => 'Penjaga Sekolah/Pesuruh',
                                    ])
                                    ->native(false),


                            ])->columnSpanFull(),



                    ])
                    ->after(function ($record) {

                        $pengajar = ModelsPengajar::where('id', $record->id)->first();
                        $pengajar->is_lengkap = '1';
                        $pengajar->save();

                        Notification::make()
                            ->success()
                            ->title('Alhamdulillah data telah tersimpan')
                            ->persistent()
                            ->color('success')
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('Lihat Data')
                    ->modalCloseButton(false)
                    ->modalHeading(' ')
                    ->modalWidth('full')
                    // ->stickyModalHeader()
                    ->button()
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('Batal'))
                    ->form([

                        Placeholder::make('')
                            ->content(function (Model $record) {
                                return (new HtmlString('<div><p class="text-3xl"><strong>' . $record->nama . '</strong></p></div>'));
                            }),


                        //DATA DIRI
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>DATA DIRI</strong></p></div>')),

                                Grid::make(3)
                                    ->schema([

                                        TextInput::make('gelar_depan')
                                            ->label('Gelar Depan')
                                            ->helperText(new HtmlString('Contoh: <strong>Prof. Dr.</strong>')),

                                        TextInput::make('nama_lengkap')
                                            ->label('Nama Lengkap')
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->required(),

                                        TextInput::make('gelar_belakang')
                                            ->label('Gelar Belakang')
                                            ->helperText(new HtmlString('Contoh: <strong>S.pd M.pd</strong>')),

                                    ]),



                                TextInput::make('nik')
                                    ->label('NIK')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger')
                                    ->length(16)
                                    ->required(),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('status_kepegawaian')
                                            ->label('Status Kepegawaian')
                                            ->disabled(),

                                        DatePicker::make('tmt_pegawai')
                                            ->label('Terhitung Mulai Tanggal')
                                            ->helperText('Tanggal mulai bertugas')
                                            ->required()
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->native(false),

                                    ]),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('hp')
                                            ->label('No. Handphone')
                                            ->helperText('Contoh: 6282187782223')
                                            ->tel()
                                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                            ->required(),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email(),

                                    ]),

                                TextInput::make('npwp')
                                    ->label('NPWP'),

                                Radio::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'Laki-laki' => 'Laki-laki',
                                        'Perempuan' => 'Perempuan',
                                    ])
                                    ->required()
                                    ->inline(),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('tempat_lahir')
                                            ->label('Tempat Lahir')
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->required(),

                                        DatePicker::make('tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->hint('Isi sesuai dengan KK')
                                            ->required()
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->native(false),

                                    ]),

                                TextInput::make('agama')
                                    ->label('Agama')
                                    ->disabled(),

                                Select::make('golongan_darah')
                                    ->label('Golongan Darah')
                                    ->placeholder('Pilih Golongan Darah')
                                    ->options([
                                        'Golongan Darah A' => 'Golongan Darah A',
                                        'Golongan Darah B' => 'Golongan Darah B',
                                        'Golongan Darah AB' => 'Golongan Darah AB',
                                        'Golongan Darah O' => 'Golongan Darah O',
                                    ])
                                    ->native(false),

                                Grid::make(3)
                                    ->schema([

                                        Select::make('pendidikan_terakhir')
                                            ->label('Pendidikan Terakhir')
                                            ->placeholder('Pilih Pendidikan Terakhir')
                                            ->options([
                                                'SD/Sederajat' => 'SD/Sederajat',
                                                'SMP/Sederajat' => 'SMP/Sederajat',
                                                'SMA/Sederajat' => 'SMA/Sederajat',
                                                'D1' => 'D1',
                                                'D2' => 'D2',
                                                'D3' => 'D3',
                                                'D4/S1' => 'D4/S1',
                                                'S2' => 'S2',
                                                'S3' => 'S3',
                                                'Tidak Memiliki Pendidikan Formal' => 'Tidak Memiliki Pendidikan Formal',
                                                'M1' => 'M1',
                                                'M2' => 'M2',
                                                'M3' => 'M3',
                                            ])
                                            ->required()
                                            ->native(false),

                                        TextInput::make('prodi_terakhir')
                                            ->label('Prodi Terakhir'),

                                        DatePicker::make('tanggal_ijazah')
                                            ->label('Tanggal Ijazah')
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y')
                                            ->closeOnDateSelection()
                                            ->native(false),

                                    ]),

                            ])->columnSpanFull(),


                        //INFORMASI TEMPAT TINGGAL
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>INFORMASI TEMPAT TINGGAL</strong></p></div>')),

                                Select::make('status_tempat_tinggal')
                                    ->label('Status Tempat Tinggal')
                                    ->placeholder('Pilih Status Tempat Tinggal')
                                    ->options([
                                        'Milik Sendiri' => 'Milik Sendiri',
                                        'Rumah Orang Tua' => 'Rumah Orang Tua',
                                        'Rumah Saudara/kerabat' => 'Rumah Saudara/kerabat',
                                        'Rumah Dinas' => 'Rumah Dinas',
                                        'Sewa/kontrak' => 'Sewa/kontrak',
                                        'Lainnya' => 'Lainnya',
                                    ])
                                    ->required()
                                    ->native(false),

                                Grid::make(2)
                                    ->schema([

                                        Select::make('provinsi_id')
                                            ->label('Provinsi')
                                            ->placeholder('Pilih Provinsi')
                                            ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->afterStateUpdated(function (Set $set) {
                                                $set('kabupaten_id', null);
                                                $set('kecamatan_id', null);
                                                $set('kelurahan_id', null);
                                                $set('kodepos', null);
                                            }),

                                        Select::make('kabupaten_id')
                                            ->label('Kabupaten')
                                            ->placeholder('Pilih Kabupaten')
                                            ->options(fn (Get $get): Collection => Kabupaten::query()
                                                ->where('provinsi_id', $get('provinsi_id'))
                                                ->pluck('kabupaten', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        Select::make('kecamatan_id')
                                            ->label('Kecamatan')
                                            ->placeholder('Pilih Kecamatan')
                                            ->options(fn (Get $get): Collection => Kecamatan::query()
                                                ->where('kabupaten_id', $get('kabupaten_id'))
                                                ->pluck('kecamatan', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        Select::make('kelurahan_id')
                                            ->label('Kelurahan')
                                            ->placeholder('Pilih Kelurahan')
                                            ->options(fn (Get $get): Collection => Kelurahan::query()
                                                ->where('kecamatan_id', $get('kecamatan_id'))
                                                ->pluck('kelurahan', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->native(false)
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger')
                                            ->afterStateUpdated(function (Get $get, ?string $state, Set $set, ?string $old) {

                                                if (($get('kodepos') ?? '') !== Str::slug($old)) {
                                                    return;
                                                }

                                                $kodepos = Kodepos::where('kelurahan_id', $state)->get('kodepos');

                                                $state = $kodepos;

                                                foreach ($state as $state) {
                                                    $set('kodepos', Str::substr($state, 12, 5));
                                                }
                                            }),


                                        TextInput::make('rt')
                                            ->label('RT')
                                            ->required()
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        TextInput::make('rw')
                                            ->label('RW')
                                            ->required()
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        Textarea::make('alamat')
                                            ->label('Alamat')
                                            ->required()
                                            ->columnSpanFull()
                                            ->hint('Isi sesuai dengan KK')
                                            ->hintColor('danger'),

                                        TextInput::make('kodepos')
                                            ->label('Kodepos')
                                            ->disabled()
                                            ->required()
                                            ->dehydrated(),
                                    ]),

                                Grid::make(3)
                                    ->schema([

                                        Select::make('transportasi')
                                            ->label('Transportasi ke Pondok Pesantren')
                                            ->options([
                                                'Jalan kaki' => 'Jalan kaki',
                                                'Sepeda' => 'Sepeda',
                                                'Sepeda Motor' => 'Sepeda Motor',
                                                'Mobil Pribadi' => 'Mobil Pribadi',
                                                'Antar Jemput Sekolah' => 'Antar Jemput Sekolah',
                                                'Angkutan Umum' => 'Angkutan Umum',
                                                'Perahu/Sampan' => 'Perahu/Sampan',
                                                'Lainnya' => 'Lainnya',
                                            ])
                                            ->required()
                                            ->native(false),

                                        Select::make('jarak')
                                            ->label('Jarak tempat tinggal ke Pondok Pesantren')
                                            ->options([
                                                'Kurang dari 5 km' => 'Kurang dari 5 km',
                                                'Antara 5 - 10 Km' => 'Antara 5 - 10 Km',
                                                'Antara 11 - 20 Km' => 'Antara 11 - 20 Km',
                                                'Antara 21 - 30 Km' => 'Antara 21 - 30 Km',
                                                'Lebih dari 30 Km' => 'Lebih dari 30 Km',
                                            ])
                                            ->required()
                                            ->native(false),



                                        Select::make('waktu_tempuh')
                                            ->label('Waktu tempuh ke Pondok Pesantren')
                                            ->options([
                                                '1 - 10 menit' => '1 - 10 menit',
                                                '10 - 19 menit' => '10 - 19 menit',
                                                '20 - 29 menit' => '20 - 29 menit',
                                                '30 - 39 menit' => '30 - 39 menit',
                                                '1 - 2 jam' => '1 - 2 jam',
                                                '> 2 jam' => '> 2 jam',
                                            ])
                                            ->required()
                                            ->native(false),
                                    ]),







                            ])->columnSpanFull(),


                        //INFORMASI KELUARGA
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>INFORMASI KELUARGA</strong></p></div>')),

                                TextInput::make('nama_ibu_kandung')
                                    ->label('Nama Ibu Kandung')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger')
                                    ->required(),

                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>STATUS PERKAWINAN</strong></p></div>')),

                                Radio::make('status_perkawinan')
                                    ->label('Status Perkawinan')
                                    ->options([
                                        'Kawin' => 'Kawin',
                                        'Belum Kawin' => 'Belum Kawin',
                                        'Duda/Janda' => 'Duda/Janda',
                                    ])
                                    ->required()
                                    ->inline(),

                                TextInput::make('nomor_kk')
                                    ->label('Nomor KK')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger')
                                    ->length(16)
                                    ->required(),

                            ])->columnSpanFull(),

                        //DATA BANK
                        Section::make('')
                            ->schema([

                                //DATA DIRI
                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>DATA BANK</strong></p></div>')),

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('no_rekening')
                                            ->label('No Rekening'),

                                        TextInput::make('nama_rekening')
                                            ->label('Nama Rekening'),

                                        TextInput::make('nama_bank')
                                            ->label('Nama Bank'),

                                        TextInput::make('cabang_bank')
                                            ->label('Cabang Bank'),
                                    ]),

                            ])->columnSpanFull(),

                        //TUGAS UTAMA
                        Section::make('')
                            ->schema([

                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>TUGAS UTAMA</strong></p></div>')),

                                Select::make('tugas_utama')
                                    ->label('Tugas Utama')
                                    ->options([
                                        'Pengemudi' => 'Pengemudi',
                                        'Tenaga Keamanan' => 'Tenaga Keamanan',
                                        'Lainnya' => 'Lainnya',
                                        'Tenaga Administrasi' => 'Tenaga Administrasi',
                                        'Tenaga Pendidik' => 'Tenaga Pendidik',
                                        'Tenaga Perpustakaan' => 'Tenaga Perpustakaan',
                                        'Tenaga Laboratorium' => 'Tenaga Laboratorium',
                                        'Tenaga Kebersihan' => 'Tenaga Kebersihan',
                                        'Penjaga Sekolah/Pesuruh' => 'Penjaga Sekolah/Pesuruh',
                                    ])
                                    ->required()
                                    ->native(false),

                                Placeholder::make('')
                                    ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>TUGAS TAMBAHAN</strong></p></div>')),

                                Select::make('tugas_tambahan')
                                    ->label('Tugas Tambahan')
                                    ->options([
                                        'Pengemudi' => 'Pengemudi',
                                        'Tenaga Keamanan' => 'Tenaga Keamanan',
                                        'Lainnya' => 'Lainnya',
                                        'Tenaga Administrasi' => 'Tenaga Administrasi',
                                        'Tenaga Pendidik' => 'Tenaga Pendidik',
                                        'Tenaga Perpustakaan' => 'Tenaga Perpustakaan',
                                        'Tenaga Laboratorium' => 'Tenaga Laboratorium',
                                        'Tenaga Kebersihan' => 'Tenaga Kebersihan',
                                        'Penjaga Sekolah/Pesuruh' => 'Penjaga Sekolah/Pesuruh',
                                    ])
                                    ->native(false),


                            ])->columnSpanFull(),



                    ])
            ]);
    }
}
