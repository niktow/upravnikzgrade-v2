<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use App\Services\BillingStatementGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Upravljanje zajednicama';

    protected static ?string $navigationLabel = 'Jedinice';

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
                        Forms\Components\TextInput::make('floor')
                            ->label('Sprat')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('area')
                            ->label('Površina (m²)')
                            ->numeric()
                            ->suffix('m²')
                            ->minValue(0)
                            ->step(0.01),
                        Forms\Components\TextInput::make('occupant_count')
                            ->label('Broj članova domaćinstva')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktivna')
                            ->default(true)
                            ->required(),
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
                    })
                    ->color(fn ($state) => match($state) {
                        'stan' => 'success',
                        'lokal' => 'warning',
                        'garaza' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('floor')
                    ->label('Sprat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('Površina')
                    ->numeric(2)
                    ->suffix(' m²')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupant_count')
                    ->label('Članovi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owners_count')
                    ->label('Vlasnici')
                    ->counts('owners')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivna')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'stan' => 'Stan',
                        'lokal' => 'Lokal',
                        'garaza' => 'Garaža',
                        'ostava' => 'Ostava',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Sve')
                    ->trueLabel('Aktivne')
                    ->falseLabel('Neaktivne'),
            ])
            ->actions([
                Tables\Actions\Action::make('generateBillingStatement')
                    ->label('Generiši račun')
                    ->icon('heroicon-o-document-text')
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
                    ->action(function (Unit $record, array $data, BillingStatementGenerator $generator) {
                        try {
                            $period = $data['period'];
                            $pdf = $generator->generateForUnit($record, $period);
                            $filename = "racun_{$record->identifier}_" . str_replace('-', '_', $period) . ".pdf";
                            
                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                $filename
                            );
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Greška pri generisanju računa')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('generateBillingStatements')
                        ->label('Generiši račune')
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
                        ->action(function ($records, array $data, BillingStatementGenerator $generator) {
                            try {
                                $period = $data['period'];
                                $generatedCount = 0;
                                
                                foreach ($records as $unit) {
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
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OwnersRelationManager::class,
            RelationManagers\LedgerEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
