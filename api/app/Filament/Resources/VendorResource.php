<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Šifarnici';

    protected static ?string $navigationLabel = 'Dobavljači';

    protected static ?string $modelLabel = 'Dobavljač';

    protected static ?string $pluralModelLabel = 'Dobavljači';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tip')
                            ->options([
                                'company' => 'Pravno lice',
                                'individual' => 'Fizičko lice',
                            ])
                            ->required()
                            ->default('company')
                            ->live(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktivan',
                                'inactive' => 'Neaktivan',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\TextInput::make('name')
                            ->label(fn (Forms\Get $get) => $get('type') === 'individual' ? 'Ime i prezime' : 'Naziv firme')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tax_number')
                            ->label(fn (Forms\Get $get) => $get('type') === 'individual' ? 'JMBG' : 'PIB')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Matični broj')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'company'),
                    ]),
                Forms\Components\Section::make('Kontakt podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Kontakt osoba')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->label('Adresa')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Bankovni podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('bank_account')
                            ->label('Broj tekućeg računa')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Naziv banke')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Napomene')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Napomene')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'individual' ? 'Fizičko lice' : 'Pravno lice')
                    ->color(fn ($state) => $state === 'individual' ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('tax_number')
                    ->label('PIB / JMBG')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contracts_count')
                    ->label('Ugovori')
                    ->counts('contracts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Aktivan' : 'Neaktivan')
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'company' => 'Pravno lice',
                        'individual' => 'Fizičko lice',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktivan',
                        'inactive' => 'Neaktivan',
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
            RelationManagers\ContractsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
