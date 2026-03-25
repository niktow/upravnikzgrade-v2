<?php

namespace App\Filament\Resources\OwnerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('housing_community_id')
                    ->label('Stambena zajednica')
                    ->relationship('housingCommunity', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('identifier')
                    ->label('Oznaka (broj stana/lokala)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tip')
                    ->options([
                        'stan' => 'Stan',
                        'lokal' => 'Lokal',
                        'garaza' => 'Garaža',
                        'ostava' => 'Ostava',
                    ])
                    ->required()
                    ->default('stan'),
                Forms\Components\TextInput::make('area')
                    ->label('Površina (m²)')
                    ->numeric()
                    ->suffix('m²'),
                Forms\Components\TextInput::make('ownership_share')
                    ->label('Procenat vlasništva (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(100)
                    ->suffix('%')
                    ->required(),
                Forms\Components\DatePicker::make('starts_at')
                    ->label('Datum početka vlasništva')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->default(now()),
                Forms\Components\DatePicker::make('ends_at')
                    ->label('Datum završetka vlasništva')
                    ->native(false)
                    ->displayFormat('d.m.Y'),
                Forms\Components\Textarea::make('obligation_notes')
                    ->label('Napomene o obavezama')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('identifier')
            ->columns([
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zajednica')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('identifier')
                    ->label('Oznaka')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'stan' => 'Stan',
                        'lokal' => 'Lokal',
                        'garaza' => 'Garaža',
                        'ostava' => 'Ostava',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('area')
                    ->label('Površina')
                    ->numeric(2)
                    ->suffix(' m²'),
                Tables\Columns\TextColumn::make('ownership_share')
                    ->label('Vlasništvo')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Početak')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Završetak')
                    ->date('d.m.Y')
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('ownership_share')
                            ->label('Procenat vlasništva (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(100)
                            ->suffix('%')
                            ->required(),
                        Forms\Components\DatePicker::make('starts_at')
                            ->label('Datum početka vlasništva')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->default(now()),
                        Forms\Components\DatePicker::make('ends_at')
                            ->label('Datum završetka vlasništva')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\Textarea::make('obligation_notes')
                            ->label('Napomene o obavezama')
                            ->rows(3),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
