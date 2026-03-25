<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HousingCommunityResource\Pages;
use App\Filament\Resources\HousingCommunityResource\RelationManagers;
use App\Models\HousingCommunity;
use App\Services\BillingStatementGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HousingCommunityResource extends Resource
{
    protected static ?string $model = HousingCommunity::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Upravljanje zajednicama';

    protected static ?string $navigationLabel = 'Stambene zajednice';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Opšti podaci')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naziv')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('registry_number')
                            ->label('Broj registra')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tax_id')
                            ->label('PIB')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Tekući račun')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_line')
                            ->label('Adresa')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->label('Grad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Poštanski broj')
                            ->maxLength(12),
                        Forms\Components\DatePicker::make('established_at')
                            ->label('Datum osnivanja')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(self::statusOptions())
                            ->default('active')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Dodatne informacije')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metapodaci')
                            ->keyLabel('Ključ')
                            ->valueLabel('Vrednost')
                            ->addButtonLabel('Dodaj stavku')
                            ->nullable()
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
                Tables\Columns\TextColumn::make('city')
                    ->label('Grad')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('registry_number')
                    ->label('Registar')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::statusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => $state === 'suspended' ? 'danger' : ($state === 'draft' ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('units_count')
                    ->label('Jedinice')
                    ->counts('units')
                    ->sortable(),
                Tables\Columns\TextColumn::make('established_at')
                    ->label('Osnovana')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ažurirano')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions()),
                Tables\Filters\Filter::make('recent')
                    ->label('Osnovano u poslednjih 12 meseci')
                    ->query(fn (Builder $query) => $query->where('established_at', '>=', now()->subYear())),
            ])
            ->actions([
                Tables\Actions\Action::make('generateAllBillingStatements')
                    ->label('Generiši sve račune')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('period')
                            ->label('Period (mesec/godina)')
                            ->native(false)
                            ->displayFormat('m/Y')
                            ->format('Y-m')
                            ->default(now()->startOfMonth())
                            ->required(),
                    ])
                    ->action(function (HousingCommunity $record, array $data, BillingStatementGenerator $generator) {
                        try {
                            $period = $data['period'];
                            $units = $record->units()->where('is_active', true)->get();
                            $generatedCount = 0;
                            
                            foreach ($units as $unit) {
                                $pdf = $generator->generateForUnit($unit, $period);
                                $generator->saveStatement($pdf, $period, $unit->identifier);
                                $generatedCount++;
                            }
                            
                            Notification::make()
                                ->title('Računi uspešno generisani')
                                ->body("Generisano {$generatedCount} računa za period {$period}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Greška pri generisanju računa')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generisanje računa za sve jedinice')
                    ->modalDescription('Da li ste sigurni da želite da generišete račune za sve aktivne jedinice u ovoj zajednici?'),
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
            'index' => Pages\ListHousingCommunities::route('/'),
            'create' => Pages\CreateHousingCommunity::route('/create'),
            'edit' => Pages\EditHousingCommunity::route('/{record}/edit'),
        ];
    }

    protected static function statusOptions(): array
    {
        return [
            'active' => 'Aktivna',
            'draft' => 'U formiranju',
            'suspended' => 'Suspendovana',
        ];
    }
}
