<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PengajarResource\Pages;
use App\Filament\Admin\Resources\PengajarResource\RelationManagers;
use App\Models\Pengajar;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Pengajar as ModelsPengajar;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\KelasSantri;
use App\Models\Santri as ModelsSantri;
use App\Models\TahunBerjalan;
use App\Models\Walisantri;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use App\Models\Kesantrian\DataSantri;
use App\Models\QismDetail;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
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
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;
use stdClass;
use Filament\Tables\Grouping\Group as GroupingGroup;

class PengajarResource extends Resource
{

    public static function canViewAny(): bool
    {
        return auth()->user()->id == 1 || auth()->user()->id == 2;
    }

    protected static ?string $navigationGroup = 'User';

    protected static ?int $navigationSort = 99030;

    protected static ?string $modelLabel = 'Profil';

    protected static ?string $navigationLabel = 'Profil';

    protected static ?string $pluralModelLabel = 'Profil';

    protected static ?string $model = Pengajar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                //USER
                Section::make('')
                    ->schema([

                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([

                                TextInput::make('name'),

                                TextInput::make('username'),

                                Select::make('panelrole')
                                    ->options([
                                        'admin' => 'admin',
                                        'pengajar' => 'pengajar',
                                        'walisantri' => 'walisantri',
                                    ]),

                                TextInput::make('tsnunique'),

                                TextInput::make('password')
                                    ->password(),



                            ]),
                    ]),


                //DATA DIRI
                Section::make('')
                    ->schema([

                        TextInput::make('nama')
                            ->label('Nama'),

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
                                    ->hintColor('danger'),

                                TextInput::make('gelar_belakang')
                                    ->label('Gelar Belakang')
                                    ->helperText(new HtmlString('Contoh: <strong>S.pd M.pd</strong>')),

                            ]),

                        TextInput::make('nik')
                            ->label('NIK')
                            ->hint('Isi sesuai dengan KK')
                            ->hintColor('danger')
                            ->length(16),

                        Grid::make(2)
                            ->schema([

                                TextInput::make('status_kepegawaian')
                                    ->label('Status Kepegawaian')
                                    ->disabled(),

                                DatePicker::make('tmt_pegawai')
                                    ->label('Terhitung Mulai Tanggal')
                                    ->helperText('Tanggal mulai')
                                    //
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
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),

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
                            //
                            ->inline(),

                        Grid::make(2)
                            ->schema([

                                TextInput::make('tempat_lahir')
                                    ->label('Tempat Lahir')
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger'),

                                DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->hint('Isi sesuai dengan KK')

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

                            ->native(false),

                        Grid::make(2)
                            ->schema([

                                Select::make('provinsi_id')
                                    ->label('Provinsi')
                                    ->placeholder('Pilih Provinsi')
                                    ->options(Provinsi::all()->pluck('provinsi', 'id'))
                                    ->searchable()

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

                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger'),

                                TextInput::make('rw')
                                    ->label('RW')

                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger'),

                                Textarea::make('alamat')
                                    ->label('Alamat')

                                    ->columnSpanFull()
                                    ->hint('Isi sesuai dengan KK')
                                    ->hintColor('danger'),

                                TextInput::make('kodepos')
                                    ->label('Kodepos')
                                    ->disabled()

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
                            ->hintColor('danger'),

                        Placeholder::make('')
                            ->content(new HtmlString('<div class="border-b"><p class="text-lg strong"><strong>STATUS PERKAWINAN</strong></p></div>')),

                        Radio::make('status_perkawinan')
                            ->label('Status Perkawinan')
                            ->options([
                                'Kawin' => 'Kawin',
                                'Belum Kawin' => 'Belum Kawin',
                                'Duda/Janda' => 'Duda/Janda',
                            ])

                            ->inline(),

                        TextInput::make('nomor_kk')
                            ->label('Nomor KK')
                            ->hint('Isi sesuai dengan KK')
                            ->hintColor('danger')
                            ->length(16),

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



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                CheckboxColumn::make('is_emis')
                    ->label('EMIS')
                    ->alignCenter(),
                
                TextColumn::make('is_lengkap')
                    ->label('Status')
                    ->sortable()
                    ->default('Belum Lengkap')
                    ->size(TextColumn\TextColumnSize::Large)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->weight(FontWeight::Bold)
                    // ->description(fn ($record): string => "Status Data Santri:", position: 'above')
                    ->formatStateUsing(function (Model $record, $state) {
                        // $semis4 = ModelsSantri::where('id', $record->santri_id)->first();
                        // // dd($pendaftar->ps_kadm_status);
                        // if ($semis4->s_emis4 === null) {
                        //     return ('Belum lengkap');
                        // } elseif ($semis4->s_emis4 !== null) {
                        //     return ('Lengkap');
                        // }
                        if ($state !== '1') {
                            return ('Belum lengkap');
                        } elseif ($state === '1') {
                            return ('Lengkap');
                        }
                    })
                    ->badge()
                    ->color(function (Model $record, $state) {
                        // $semis4 = ModelsSantri::where('id', $record->santri_id)->first();
                        // // dd($pendaftar->ps_kadm_status);
                        // if ($semis4->s_emis4 === null) {
                        //     return ('danger');
                        // } elseif ($semis4->s_emis4 !== null) {
                        //     return ('success');
                        // }

                        if ($state !== '1') {
                            return ('danger');
                        } elseif ($state === '1') {
                            return ('success');
                        }
                    }),
                    
                    TextInputColumn::make('nama')
                    ->label('Pengajar-nama')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->extraAttributes([
                        'style' => 'width:200px'
                    ])
                    ->sortable(),
                
                TextInputColumn::make('user.id')
                    ->label('User-id')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->extraAttributes([
                        'style' => 'width:100px'
                    ])
                    ->sortable(),

                TextInputColumn::make('user_id')
                    ->label('Pengajar-user_id')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->extraAttributes([
                        'style' => 'width:100px'
                    ])
                    ->sortable(),

                TextInputColumn::make('user.username')
                    ->label('User-username')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->extraAttributes([
                        'style' => 'width:200px'
                    ])
                    ->sortable(),

                SelectColumn::make('user.panelrole')
                    ->label('User-panelrole')
                    ->options([
                        'admin' => 'admin',
                        'pengajar' => 'pengajar',
                        'walisantri' => 'walisantri',
                    ])
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->extraAttributes([
                        'style' => 'width:200px'
                    ])
                    ->sortable(),

                TextInputColumn::make('nama_lengkap')
                    ->label('Pengajar-nama_lengkap')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->extraAttributes([
                        'style' => 'width:200px'
                    ])
                    ->sortable(),
                    
                    
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPengajars::route('/'),
            'create' => Pages\CreatePengajar::route('/create'),
            'view' => Pages\ViewPengajar::route('/{record}'),
            'edit' => Pages\EditPengajar::route('/{record}/edit'),
        ];
    }
    
    
}
