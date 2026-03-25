<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Dokumentacija';
    
    protected static ?string $navigationLabel = 'Dokumenti';
    
    protected static ?string $modelLabel = 'Dokument';
    
    protected static ?string $pluralModelLabel = 'Dokumenti';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Naziv dokumenta')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('npr. Zapisnik sa skupštine 15.01.2026.'),
                            
                        Forms\Components\Select::make('category')
                            ->label('Kategorija')
                            ->options([
                                'contract' => 'Ugovor',
                                'invoice' => 'Račun',
                                'report' => 'Izveštaj',
                                'minutes' => 'Zapisnik',
                                'certificate' => 'Sertifikat/Atest',
                                'decision' => 'Odluka',
                                'notice' => 'Obaveštenje',
                                'legal' => 'Pravni dokument',
                                'technical' => 'Tehnička dokumentacija',
                                'financial' => 'Finansijski dokument',
                                'other' => 'Ostalo',
                            ])
                            ->required()
                            ->searchable(),
                            
                        Forms\Components\DatePicker::make('issued_at')
                            ->label('Datum izdavanja')
                            ->displayFormat('d.m.Y'),
                    ])
                    ->columns(3),
                    
                Forms\Components\Section::make('Fajl')
                    ->schema([
                        Forms\Components\FileUpload::make('storage_path')
                            ->label('Dokument')
                            ->directory('documents')
                            ->preserveFilenames()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(10240) // 10MB
                            ->helperText('Dozvoljeni formati: PDF, Word, Excel, slike. Maksimalno 10MB.')
                            ->required()
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Povezivanje (opciono)')
                    ->schema([
                        Forms\Components\Select::make('documentable_type')
                            ->label('Tip entiteta')
                            ->options([
                                'App\\Models\\HousingCommunity' => 'Stambena zajednica',
                                'App\\Models\\Inspection' => 'Inspekcija',
                                'App\\Models\\AssemblyMeeting' => 'Skupština',
                                'App\\Models\\Contract' => 'Ugovor',
                                'App\\Models\\Expense' => 'Trošak',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('documentable_id', null)),
                            
                        Forms\Components\Select::make('documentable_id')
                            ->label('Poveži sa')
                            ->options(function (callable $get) {
                                $type = $get('documentable_type');
                                if (!$type) return [];
                                
                                return match($type) {
                                    'App\\Models\\HousingCommunity' => \App\Models\HousingCommunity::pluck('name', 'id'),
                                    'App\\Models\\Inspection' => \App\Models\Inspection::with('housingCommunity')
                                        ->get()
                                        ->mapWithKeys(fn ($i) => [$i->id => $i->housingCommunity?->name . ' - ' . $i->inspection_type . ' (' . $i->scheduled_at?->format('d.m.Y') . ')']),
                                    'App\\Models\\AssemblyMeeting' => \App\Models\AssemblyMeeting::with('housingCommunity')
                                        ->get()
                                        ->mapWithKeys(fn ($m) => [$m->id => $m->housingCommunity?->name . ' - ' . $m->scheduled_for?->format('d.m.Y')]),
                                    'App\\Models\\Contract' => \App\Models\Contract::with('vendor')
                                        ->get()
                                        ->mapWithKeys(fn ($c) => [$c->id => $c->vendor?->name . ' - ' . $c->contract_number]),
                                    'App\\Models\\Expense' => \App\Models\Expense::with('housingCommunity')
                                        ->get()
                                        ->mapWithKeys(fn ($e) => [$e->id => $e->housingCommunity?->name . ' - ' . $e->description]),
                                    default => []
                                };
                            })
                            ->searchable()
                            ->visible(fn (callable $get) => filled($get('documentable_type'))),
                    ])
                    ->columns(2)
                    ->collapsed(),
                    
                Forms\Components\Section::make('Dodatne informacije')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metapodaci')
                            ->keyLabel('Polje')
                            ->valueLabel('Vrednost')
                            ->addActionLabel('Dodaj polje')
                            ->helperText('Opciono: dodajte dodatne informacije o dokumentu'),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                    
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategorija')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'contract' => 'Ugovor',
                        'invoice' => 'Račun',
                        'report' => 'Izveštaj',
                        'minutes' => 'Zapisnik',
                        'certificate' => 'Sertifikat',
                        'decision' => 'Odluka',
                        'notice' => 'Obaveštenje',
                        'legal' => 'Pravni',
                        'technical' => 'Tehnički',
                        'financial' => 'Finansijski',
                        'other' => 'Ostalo',
                        default => $state ?? '-'
                    })
                    ->color(fn ($state) => match($state) {
                        'contract' => 'primary',
                        'invoice' => 'warning',
                        'report' => 'info',
                        'minutes' => 'success',
                        'certificate' => 'danger',
                        'decision' => 'primary',
                        'legal' => 'gray',
                        'technical' => 'info',
                        'financial' => 'warning',
                        default => 'gray'
                    }),
                    
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('file_extension')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->color(fn ($state) => match(strtolower($state)) {
                        'pdf' => 'danger',
                        'doc', 'docx' => 'primary',
                        'xls', 'xlsx' => 'success',
                        'jpg', 'jpeg', 'png', 'webp' => 'warning',
                        default => 'gray'
                    }),
                    
                Tables\Columns\TextColumn::make('documentable_type')
                    ->label('Povezano sa')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'App\\Models\\HousingCommunity' => 'Zajednica',
                        'App\\Models\\Inspection' => 'Inspekcija',
                        'App\\Models\\AssemblyMeeting' => 'Skupština',
                        'App\\Models\\Contract' => 'Ugovor',
                        'App\\Models\\Expense' => 'Trošak',
                        default => '-'
                    })
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploadovano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategorija')
                    ->options([
                        'contract' => 'Ugovor',
                        'invoice' => 'Račun',
                        'report' => 'Izveštaj',
                        'minutes' => 'Zapisnik',
                        'certificate' => 'Sertifikat/Atest',
                        'decision' => 'Odluka',
                        'notice' => 'Obaveštenje',
                        'legal' => 'Pravni dokument',
                        'technical' => 'Tehnička dokumentacija',
                        'financial' => 'Finansijski dokument',
                        'other' => 'Ostalo',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Preuzmi')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Document $record) => Storage::url($record->storage_path))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nema dokumenata')
            ->emptyStateDescription('Uploadujte prvi dokument klikom na dugme iznad.')
            ->emptyStateIcon('heroicon-o-document-text');
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
