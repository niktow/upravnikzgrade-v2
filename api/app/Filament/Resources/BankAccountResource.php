<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Resources\BankAccountResource\RelationManagers;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Bankovni računi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('housing_community_id')
                            ->label('Stambena zajednica')
                            ->relationship('housingCommunity', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Naziv banke')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Broj računa')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('currency')
                            ->label('Valuta')
                            ->options([
                                'RSD' => 'RSD',
                                'EUR' => 'EUR',
                                'USD' => 'USD',
                            ])
                            ->required()
                            ->default('RSD'),
                    ]),
                Forms\Components\Section::make('Stanje')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('opening_balance')
                            ->label('Početno stanje')
                            ->required()
                            ->numeric()
                            ->prefix('RSD')
                            ->default(0.00),
                        Forms\Components\TextInput::make('current_balance')
                            ->label('Trenutno stanje')
                            ->required()
                            ->numeric()
                            ->prefix('RSD')
                            ->default(0.00),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Dodatne informacije')
                            ->keyLabel('Ključ')
                            ->valueLabel('Vrednost')
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Banka')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Broj računa')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Valuta')
                    ->badge(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Trenutno stanje')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transakcije')
                    ->counts('transactions')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('currency')
                    ->label('Valuta')
                    ->options([
                        'RSD' => 'RSD',
                        'EUR' => 'EUR',
                        'USD' => 'USD',
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
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
