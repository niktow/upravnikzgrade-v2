<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Filament\Resources\InspectionResource\RelationManagers;
use App\Models\Inspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationGroup = 'Zakonske obaveze';
    
    protected static ?string $navigationLabel = 'Inspekcije';
    
    protected static ?string $modelLabel = 'Inspekcija';
    
    protected static ?string $pluralModelLabel = 'Inspekcije';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->schema([
                        Forms\Components\Select::make('housing_community_id')
                            ->relationship('housingCommunity', 'name')
                            ->label('Stambena zajednica')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Select::make('inspection_type')
                            ->label('Tip inspekcije')
                            ->options([
                                'fire_safety' => 'Požarna zaštita',
                                'electrical' => 'Elektro pregled',
                                'gas' => 'Gasna instalacija',
                                'elevator' => 'Lift',
                                'lightning' => 'Gromobran',
                                'chimney' => 'Dimnjak',
                                'building' => 'Građevinska',
                                'health' => 'Sanitarna',
                                'other' => 'Ostalo',
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('conducted_by')
                            ->label('Izvršilac / Inspektor')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ime inspektora ili firme'),
                    ])
                    ->columns(3),
                    
                Forms\Components\Section::make('Datumi i status')
                    ->schema([
                        Forms\Components\DatePicker::make('scheduled_at')
                            ->label('Zakazano za')
                            ->displayFormat('d.m.Y'),
                            
                        Forms\Components\DatePicker::make('conducted_at')
                            ->label('Izvršeno dana')
                            ->displayFormat('d.m.Y'),
                            
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Zakazano',
                                'completed' => 'Završeno',
                                'passed' => 'Položeno',
                                'failed' => 'Palo',
                                'cancelled' => 'Otkazano',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ])
                    ->columns(3),
                    
                Forms\Components\Section::make('Detalji')
                    ->schema([
                        Forms\Components\Select::make('document_id')
                            ->relationship('document', 'title')
                            ->label('Povezan dokument')
                            ->searchable()
                            ->preload()
                            ->helperText('Opciono: povežite sa postojećim dokumentom'),
                            
                        Forms\Components\Textarea::make('findings')
                            ->label('Nalazi / Napomene')
                            ->rows(4)
                            ->placeholder('Unesite nalaze inspekcije, primedbe, preporuke...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zajednica')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('inspection_type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'fire_safety' => 'Požarna',
                        'electrical' => 'Elektro',
                        'gas' => 'Gas',
                        'elevator' => 'Lift',
                        'lightning' => 'Gromobran',
                        'chimney' => 'Dimnjak',
                        'building' => 'Građevinska',
                        'health' => 'Sanitarna',
                        'other' => 'Ostalo',
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'fire_safety' => 'danger',
                        'electrical' => 'warning',
                        'gas' => 'info',
                        'elevator' => 'primary',
                        default => 'gray'
                    }),
                    
                Tables\Columns\TextColumn::make('conducted_by')
                    ->label('Izvršilac')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Zakazano')
                    ->date('d.m.Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('conducted_at')
                    ->label('Izvršeno')
                    ->date('d.m.Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'scheduled' => 'Zakazano',
                        'completed' => 'Završeno',
                        'passed' => 'Položeno',
                        'failed' => 'Palo',
                        'cancelled' => 'Otkazano',
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'passed' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray'
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('housing_community_id')
                    ->relationship('housingCommunity', 'name')
                    ->label('Stambena zajednica')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('inspection_type')
                    ->label('Tip inspekcije')
                    ->options([
                        'fire_safety' => 'Požarna zaštita',
                        'electrical' => 'Elektro',
                        'gas' => 'Gas',
                        'elevator' => 'Lift',
                        'lightning' => 'Gromobran',
                        'chimney' => 'Dimnjak',
                        'building' => 'Građevinska',
                        'health' => 'Sanitarna',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Zakazano',
                        'completed' => 'Završeno',
                        'passed' => 'Položeno',
                        'failed' => 'Palo',
                        'cancelled' => 'Otkazano',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->emptyStateHeading('Nema inspekcija')
            ->emptyStateDescription('Kreirajte prvu inspekciju klikom na dugme iznad.')
            ->emptyStateIcon('heroicon-o-shield-check');
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
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
        ];
    }
}
