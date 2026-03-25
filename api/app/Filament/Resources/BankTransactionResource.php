<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankTransactionResource\Pages;
use App\Filament\Resources\BankTransactionResource\RelationManagers;
use App\Models\BankTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankTransactionResource extends Resource
{
    protected static ?string $model = BankTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Transakcije';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('bank_account_id')
                            ->label('Bankovni račun')
                            ->relationship('bankAccount', 'account_number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('direction')
                            ->label('Smer')
                            ->options([
                                'credit' => 'Uplata (kredit)',
                                'debit' => 'Isplata (debit)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Iznos')
                            ->required()
                            ->numeric()
                            ->prefix('RSD')
                            ->minValue(0),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Datum transakcije')
                            ->required()
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\DatePicker::make('value_date')
                            ->label('Datum valute')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'recorded' => 'Evidentirano',
                                'reconciled' => 'Usklađeno',
                                'pending' => 'Na čekanju',
                            ])
                            ->required()
                            ->default('recorded'),
                    ]),
                Forms\Components\Section::make('Povezivanje')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->label('Vlasnik')
                            ->options(function () {
                                return \App\Models\Owner::whereNotNull('full_name')
                                    ->get()
                                    ->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('unit_id')
                            ->label('Jedinica')
                            ->options(function () {
                                return \App\Models\Unit::whereNotNull('identifier')
                                    ->get()
                                    ->pluck('identifier', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('expense_id')
                            ->label('Trošak')
                            ->options(function () {
                                return \App\Models\Expense::whereNotNull('description')
                                    ->get()
                                    ->pluck('description', 'id');
                            })
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Detalji')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Broj reference')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('purpose_code')
                            ->label('Šifra namene')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('counterparty_name')
                            ->label('Druga strana')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('raw_payload')
                            ->label('Sirovi podaci (JSON)')
                            ->keyLabel('Polje')
                            ->valueLabel('Vrednost')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bankAccount.account_number')
                    ->label('Račun')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('direction')
                    ->label('Smer')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'credit' ? 'Uplata' : 'Isplata')
                    ->color(fn ($state) => $state === 'credit' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('counterparty_name')
                    ->label('Druga strana')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Vlasnik')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit.identifier')
                    ->label('Jedinica')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referenca')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'recorded' => 'Evidentirano',
                        'reconciled' => 'Usklađeno',
                        'pending' => 'Na čekanju',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'reconciled' => 'success',
                        'recorded' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('Smer')
                    ->options([
                        'credit' => 'Uplata',
                        'debit' => 'Isplata',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'recorded' => 'Evidentirano',
                        'reconciled' => 'Usklađeno',
                        'pending' => 'Na čekanju',
                    ]),
                Tables\Filters\SelectFilter::make('bank_account')
                    ->label('Račun')
                    ->relationship('bankAccount', 'account_number'),
                Tables\Filters\Filter::make('transaction_date')
                    ->label('Period')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Od'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
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
            'index' => Pages\ListBankTransactions::route('/'),
            'create' => Pages\CreateBankTransaction::route('/create'),
            'edit' => Pages\EditBankTransaction::route('/{record}/edit'),
        ];
    }
}
