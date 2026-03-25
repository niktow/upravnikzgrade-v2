<?php

namespace App\Filament\Resources\VendorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $title = 'Ugovori';

    protected static ?string $modelLabel = 'Ugovor';

    protected static ?string $pluralModelLabel = 'Ugovori';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Naziv')
                    ->searchable(),
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zajednica')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'U pripremi',
                        'active' => 'Aktivan',
                        'expired' => 'Istekao',
                        'cancelled' => 'Otkazan',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'draft' => 'warning',
                        'active' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Od')
                    ->date('d.m.Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Do')
                    ->date('d.m.Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
