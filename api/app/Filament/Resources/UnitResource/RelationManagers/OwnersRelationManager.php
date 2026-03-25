<?php

namespace App\Filament\Resources\UnitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnersRelationManager extends RelationManager
{
    protected static string $relationship = 'owners';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('Ime i prezime / Naziv')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->maxLength(255),
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
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Ime i prezime')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->copyable(),
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
