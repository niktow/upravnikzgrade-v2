<?php

namespace App\Filament\Resources\UnitResource\RelationManagers;

use App\Models\UnitLedger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LedgerEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgerEntries';

    protected static ?string $title = 'Kartica stana';

    protected static ?string $modelLabel = 'stavka';

    protected static ?string $pluralModelLabel = 'stavke';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->maxLength(255),
                Forms\Components\TextInput::make('period')
                    ->label('Period (YYYY-MM)')
                    ->placeholder('2026-01')
                    ->maxLength(7),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->defaultSort('date', 'desc')
            ->columns([
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
                    ->limit(40),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('period')
                    ->label('Period'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'charge' => 'Zaduženje',
                        'payment' => 'Uplata',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj stavku'),
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
}
