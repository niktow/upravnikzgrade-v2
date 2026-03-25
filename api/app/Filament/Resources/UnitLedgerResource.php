<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitLedgerResource\Pages;
use App\Filament\Resources\UnitLedgerResource\RelationManagers;
use App\Models\UnitLedger;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitLedgerResource extends Resource
{
    protected static ?string $model = UnitLedger::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Kartice stanova';

    protected static ?string $modelLabel = 'Stavka kartice';

    protected static ?string $pluralModelLabel = 'Kartica stanova';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stavka kartice')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('unit_id')
                            ->label('Stan')
                            ->options(function () {
                                return Unit::with('housingCommunity')
                                    ->get()
                                    ->mapWithKeys(fn ($unit) => [
                                        $unit->id => $unit->identifier . ' - ' . $unit->housingCommunity->name
                                    ]);
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Datum')
                            ->required()
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->default(now()),
                        Forms\Components\Select::make('type')
                            ->label('Tip')
                            ->options([
                                'charge' => 'Zaduženje',
                                'payment' => 'Uplata',
                            ])
                            ->required()
                            ->default('charge'),
                        Forms\Components\TextInput::make('amount')
                            ->label('Iznos')
                            ->required()
                            ->numeric()
                            ->prefix('RSD')
                            ->minValue(0),
                        Forms\Components\TextInput::make('description')
                            ->label('Opis')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('period')
                            ->label('Period (YYYY-MM)')
                            ->placeholder('2026-01')
                            ->maxLength(7),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('unit.identifier')
                    ->label('Stan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit.housingCommunity.name')
                    ->label('Zgrada')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'charge' => 'Zaduženje',
                        'payment' => 'Uplata',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'charge' => 'danger',
                        'payment' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period')
                    ->label('Period')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Stan')
                    ->relationship('unit', 'identifier')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'charge' => 'Zaduženje',
                        'payment' => 'Uplata',
                    ]),
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
            'index' => Pages\ListUnitLedgers::route('/'),
            'create' => Pages\CreateUnitLedger::route('/create'),
            'edit' => Pages\EditUnitLedger::route('/{record}/edit'),
        ];
    }
}
