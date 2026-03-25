<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitTypePricingResource\Pages;
use App\Filament\Resources\UnitTypePricingResource\RelationManagers;
use App\Models\UnitTypePricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitTypePricingResource extends Resource
{
    protected static ?string $model = UnitTypePricing::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Cenovnik';

    protected static ?string $modelLabel = 'Cena';

    protected static ?string $pluralModelLabel = 'Cenovnik';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovno')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('unit_type')
                            ->label('Tip jedinice')
                            ->options(UnitTypePricing::getUnitTypes())
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('housing_community_id')
                            ->label('Stambena zajednica')
                            ->relationship('housingCommunity', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Ostavite prazno za globalnu cenu'),
                    ]),
                Forms\Components\Section::make('Cene')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('monthly_fee')
                            ->label('Mesečna naknada (RSD)')
                            ->numeric()
                            ->prefix('RSD')
                            ->default(0)
                            ->required()
                            ->helperText('Fiksna mesečna cena'),
                        Forms\Components\TextInput::make('fee_per_sqm')
                            ->label('Cena po m² (RSD)')
                            ->numeric()
                            ->prefix('RSD')
                            ->nullable()
                            ->helperText('Opciono - dodaje se na fiksnu cenu'),
                    ]),
                Forms\Components\Section::make('Validnost')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktivna cena')
                            ->default(true),
                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Važi od')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Važi do')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                    ]),
                Forms\Components\Textarea::make('description')
                    ->label('Opis / Napomena')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_type')
                    ->label('Tip')
                    ->formatStateUsing(fn ($state) => UnitTypePricing::getUnitTypes()[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'stan' => 'success',
                        'lokal' => 'warning',
                        'garaza' => 'info',
                        'ostava' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zajednica')
                    ->placeholder('Globalna cena')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_fee')
                    ->label('Mesečna naknada')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_per_sqm')
                    ->label('Po m²')
                    ->money('RSD')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivna')
                    ->boolean(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Od')
                    ->date('d.m.Y')
                    ->placeholder('∞')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Do')
                    ->date('d.m.Y')
                    ->placeholder('∞')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->label('Tip jedinice')
                    ->options(UnitTypePricing::getUnitTypes()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Sve')
                    ->trueLabel('Aktivne')
                    ->falseLabel('Neaktivne'),
                Tables\Filters\SelectFilter::make('housing_community_id')
                    ->label('Zajednica')
                    ->relationship('housingCommunity', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('unit_type');
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
            'index' => Pages\ListUnitTypePricings::route('/'),
            'create' => Pages\CreateUnitTypePricing::route('/create'),
            'edit' => Pages\EditUnitTypePricing::route('/{record}/edit'),
        ];
    }
}
