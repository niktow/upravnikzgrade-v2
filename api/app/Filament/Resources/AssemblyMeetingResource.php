<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssemblyMeetingResource\Pages;
use App\Models\AssemblyMeeting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssemblyMeetingResource extends Resource
{
    protected static ?string $model = AssemblyMeeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Zakonske obaveze';
    
    protected static ?string $navigationLabel = 'Skupštine stanara';
    
    protected static ?string $modelLabel = 'Skupština';
    
    protected static ?string $pluralModelLabel = 'Skupštine stanara';
    
    protected static ?int $navigationSort = 2;

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
                            
                        Forms\Components\DateTimePicker::make('scheduled_for')
                            ->label('Zakazano za')
                            ->required()
                            ->displayFormat('d.m.Y H:i'),
                            
                        Forms\Components\TextInput::make('location')
                            ->label('Mesto održavanja')
                            ->maxLength(255)
                            ->placeholder('npr. Hodnik zgrade, stan br. 1...'),
                            
                        Forms\Components\TextInput::make('called_by')
                            ->label('Sazvao')
                            ->maxLength(255)
                            ->placeholder('Ime osobe koja je sazvala skupštinu'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Dnevni red i status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Zakazana',
                                'in_progress' => 'U toku',
                                'completed' => 'Održana',
                                'cancelled' => 'Otkazana',
                                'postponed' => 'Odložena',
                            ])
                            ->default('scheduled')
                            ->required(),
                            
                        Forms\Components\Select::make('document_id')
                            ->relationship('document', 'title')
                            ->label('Zapisnik')
                            ->searchable()
                            ->preload()
                            ->helperText('Povežite zapisnik sa skupštine'),
                            
                        Forms\Components\Textarea::make('agenda')
                            ->label('Dnevni red')
                            ->rows(6)
                            ->placeholder("1. Usvajanje zapisnika sa prethodne skupštine\n2. Izveštaj o finansijskom stanju\n3. Plan održavanja za tekuću godinu\n4. Razno"),
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
                    
                Tables\Columns\TextColumn::make('scheduled_for')
                    ->label('Zakazano za')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('location')
                    ->label('Mesto')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('called_by')
                    ->label('Sazvao')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'scheduled' => 'Zakazana',
                        'in_progress' => 'U toku',
                        'completed' => 'Održana',
                        'cancelled' => 'Otkazana',
                        'postponed' => 'Odložena',
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'postponed' => 'gray',
                        default => 'gray'
                    }),
                    
                Tables\Columns\IconColumn::make('document_id')
                    ->label('Zapisnik')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('housing_community_id')
                    ->relationship('housingCommunity', 'name')
                    ->label('Stambena zajednica')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Zakazana',
                        'in_progress' => 'U toku',
                        'completed' => 'Održana',
                        'cancelled' => 'Otkazana',
                        'postponed' => 'Odložena',
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
            ->defaultSort('scheduled_for', 'desc')
            ->emptyStateHeading('Nema zakazanih skupština')
            ->emptyStateDescription('Zakažite prvu skupštinu stanara.')
            ->emptyStateIcon('heroicon-o-user-group');
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
            'index' => Pages\ListAssemblyMeetings::route('/'),
            'create' => Pages\CreateAssemblyMeeting::route('/create'),
            'edit' => Pages\EditAssemblyMeeting::route('/{record}/edit'),
        ];
    }
}
