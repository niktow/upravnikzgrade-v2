<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    
    protected static ?string $navigationGroup = 'Komunikacija';
    
    protected static ?string $modelLabel = 'Oglas';
    
    protected static ?string $pluralModelLabel = 'Oglasi';
    
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
                        Forms\Components\TextInput::make('title')
                            ->label('Naslov')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Sadržaj')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Postavke')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tip')
                            ->options([
                                'info' => '📢 Informacija',
                                'warning' => '⚠️ Upozorenje',
                                'maintenance' => '🔧 Održavanje',
                                'meeting' => '👥 Sastanak',
                                'financial' => '💰 Finansije',
                            ])
                            ->default('info')
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->label('Prioritet')
                            ->options([
                                'low' => 'Nizak',
                                'normal' => 'Normalan',
                                'high' => 'Visok',
                                'urgent' => 'Hitan',
                            ])
                            ->default('normal')
                            ->required(),
                        Forms\Components\Toggle::make('is_pinned')
                            ->label('Zakači na vrh')
                            ->helperText('Zakačeni oglasi se prikazuju na vrhu liste'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktivan')
                            ->default(true)
                            ->helperText('Neaktivni oglasi nisu vidljivi stanarima'),
                    ])->columns(2),
                    
                Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-bookmark')
                    ->falseIcon('')
                    ->width(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('Naslov')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zgrada')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'info' => 'Informacija',
                        'warning' => 'Upozorenje',
                        'maintenance' => 'Održavanje',
                        'meeting' => 'Sastanak',
                        'financial' => 'Finansije',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'warning' => 'warning',
                        'maintenance' => 'info',
                        'meeting' => 'success',
                        'financial' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritet')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'low' => 'Nizak',
                        'normal' => 'Normalan',
                        'high' => 'Visok',
                        'urgent' => 'Hitan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'low' => 'gray',
                        default => 'primary',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('housing_community_id')
                    ->relationship('housingCommunity', 'name')
                    ->label('Zgrada'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'info' => 'Informacija',
                        'warning' => 'Upozorenje',
                        'maintenance' => 'Održavanje',
                        'meeting' => 'Sastanak',
                        'financial' => 'Finansije',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktivan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
