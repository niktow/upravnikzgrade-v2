<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Troškovi';

    protected static ?int $navigationSort = 1;

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
                        Forms\Components\Select::make('expense_category_id')
                            ->label('Kategorija')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Naziv')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Opis'),
                            ]),
                        Forms\Components\Select::make('type')
                            ->label('Tip')
                            ->options([
                                'one_time' => 'Jednokratni',
                                'recurring' => 'Redovni',
                            ])
                            ->required()
                            ->default('one_time'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Na čekanju',
                                'approved' => 'Odobren',
                                'paid' => 'Plaćen',
                                'cancelled' => 'Otkazan',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\TextInput::make('amount')
                            ->label('Iznos')
                            ->required()
                            ->numeric()
                            ->prefix('RSD')
                            ->minValue(0),
                    ]),
                Forms\Components\Section::make('Dodatne informacije')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('contract_id')
                            ->label('Ugovor')
                            ->relationship('contract', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('unit_id')
                            ->label('Jedinica')
                            ->relationship('unit', 'identifier')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('incurred_on')
                            ->label('Datum nastanka')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Rok plaćanja')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        Forms\Components\Textarea::make('description')
                            ->label('Opis')
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
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategorija')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'one_time' ? 'Jednokratni' : 'Redovni')
                    ->color(fn ($state) => $state === 'recurring' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Na čekanju',
                        'approved' => 'Odobren',
                        'paid' => 'Plaćen',
                        'cancelled' => 'Otkazan',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Rok plaćanja')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('incurred_on')
                    ->label('Datum nastanka')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Na čekanju',
                        'approved' => 'Odobren',
                        'paid' => 'Plaćen',
                        'cancelled' => 'Otkazan',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'one_time' => 'Jednokratni',
                        'recurring' => 'Redovni',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategorija')
                    ->relationship('category', 'name'),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
