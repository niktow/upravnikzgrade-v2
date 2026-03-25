<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerResource\Pages;
use App\Filament\Resources\OwnerResource\RelationManagers;
use App\Models\Owner;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Upravljanje zajednicama';

    protected static ?string $navigationLabel = 'Vlasnici';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('Ime i prezime / Naziv')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('national_id')
                            ->label('JMBG / PIB')
                            ->maxLength(32),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Datum rođenja')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                    ]),
                Forms\Components\Section::make('Kontakt')
                    ->columns(2)
                    ->schema([
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
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Dodatno')
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
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Ime i prezime / Naziv')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('units_count')
                    ->label('Broj jedinica')
                    ->counts('units')
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_account')
                    ->label('Portal')
                    ->state(fn (Owner $record): bool => User::where('owner_id', $record->id)->exists())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('national_id')
                    ->label('JMBG/PIB')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('createAccount')
                    ->label('Kreiraj nalog')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (Owner $record): bool => 
                        $record->email && !User::where('owner_id', $record->id)->exists()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Kreiranje stanarskog naloga')
                    ->modalDescription(fn (Owner $record) => 
                        "Da li želite da kreirate stanarski nalog za {$record->full_name}? Privremena lozinka će biti generisana."
                    )
                    ->modalSubmitActionLabel('Kreiraj nalog')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('Lozinka')
                            ->default('Promeni123')
                            ->required()
                            ->helperText('Zapišite ovu lozinku i prosledite je stanaru.'),
                    ])
                    ->action(function (Owner $record, array $data): void {
                        $user = User::create([
                            'name' => $record->full_name,
                            'email' => $record->email,
                            'password' => Hash::make($data['password']),
                            'role' => 'tenant',
                            'owner_id' => $record->id,
                        ]);

                        Notification::make()
                            ->title('Nalog kreiran')
                            ->body("Stanarski nalog za {$record->full_name} je uspešno kreiran. Lozinka: {$data['password']}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                Tables\Actions\Action::make('viewAccount')
                    ->label('Ima nalog')
                    ->icon('heroicon-o-check-badge')
                    ->color('gray')
                    ->disabled()
                    ->visible(fn (Owner $record): bool => 
                        User::where('owner_id', $record->id)->exists()
                    ),
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
            RelationManagers\UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            'create' => Pages\CreateOwner::route('/create'),
            'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }
}
