<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Ugovori';

    protected static ?int $navigationSort = 2;

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
                        Forms\Components\Select::make('vendor_id')
                            ->label('Dobavljač')
                            ->relationship('vendor', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv')
                                    ->required(),
                                Forms\Components\TextInput::make('tax_number')
                                    ->label('PIB'),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Telefon'),
                            ]),
                        Forms\Components\TextInput::make('title')
                            ->label('Naziv ugovora')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Opis')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Finansijski podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Iznos')
                            ->numeric()
                            ->prefix('RSD')
                            ->minValue(0),
                        Forms\Components\Select::make('payment_interval')
                            ->label('Interval plaćanja')
                            ->options([
                                'monthly' => 'Mesečno',
                                'quarterly' => 'Kvartalno',
                                'yearly' => 'Godišnje',
                                'one_time' => 'Jednokratno',
                            ]),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Datum početka')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Datum završetka')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'U pripremi',
                                'active' => 'Aktivan',
                                'expired' => 'Istekao',
                                'cancelled' => 'Otkazan',
                            ])
                            ->required()
                            ->default('draft'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Dobavljač')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zajednica')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_interval')
                    ->label('Interval')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'monthly' => 'Mesečno',
                        'quarterly' => 'Kvartalno',
                        'yearly' => 'Godišnje',
                        'one_time' => 'Jednokratno',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Od')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Do')
                    ->date('d.m.Y')
                    ->sortable(),
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
                        'draft' => 'gray',
                        'active' => 'success',
                        'expired' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'U pripremi',
                        'active' => 'Aktivan',
                        'expired' => 'Istekao',
                        'cancelled' => 'Otkazan',
                    ]),
                Tables\Filters\SelectFilter::make('vendor')
                    ->label('Dobavljač')
                    ->relationship('vendor', 'name'),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
