<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorInvoiceResource\Pages;
use App\Filament\Resources\VendorInvoiceResource\RelationManagers;
use App\Models\VendorInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorInvoiceResource extends Resource
{
    protected static ?string $model = VendorInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?string $navigationLabel = 'Pristigli računi';

    protected static ?string $modelLabel = 'Račun dobavljača';

    protected static ?string $pluralModelLabel = 'Pristigli računi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podaci o računu')
                    ->columns(2)
                    ->schema([
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
                                Forms\Components\TextInput::make('contact_person')
                                    ->label('Kontakt osoba'),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Telefon'),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                            ]),
                        Forms\Components\Select::make('housing_community_id')
                            ->label('Stambena zajednica')
                            ->relationship('housingCommunity', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Broj računa')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('amount')
                            ->label('Iznos')
                            ->required()
                            ->numeric()
                            ->prefix('RSD')
                            ->minValue(0),
                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Datum računa')
                            ->required()
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->default(now()),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Rok plaćanja')
                            ->native(false)
                            ->displayFormat('d.m.Y'),
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
                        Forms\Components\DatePicker::make('paid_date')
                            ->label('Datum plaćanja')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->visible(fn ($get) => $get('status') === 'paid'),
                    ]),
                Forms\Components\Section::make('Dodatne informacije')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->label('Opis')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Napomene')
                            ->rows(3),
                        Forms\Components\FileUpload::make('document_path')
                            ->label('Skeniran račun')
                            ->directory('vendor-invoices')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('invoice_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Dobavljač')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('housingCommunity.name')
                    ->label('Zgrada')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Broj računa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Rok')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn (VendorInvoice $record): string => 
                        $record->isOverdue() ? 'danger' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Iznos')
                    ->money('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Na čekanju',
                        'approved' => 'Odobren',
                        'paid' => 'Plaćen',
                        'cancelled' => 'Otkazan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->limit(30)
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
                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Dobavljač')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('housing_community_id')
                    ->label('Zgrada')
                    ->relationship('housingCommunity', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('overdue')
                    ->label('Dospeli')
                    ->query(fn (Builder $query): Builder => $query->overdue()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Odobri')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (VendorInvoice $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (VendorInvoice $record) => $record->approve()),
                Tables\Actions\Action::make('markPaid')
                    ->label('Označi plaćeno')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (VendorInvoice $record): bool => in_array($record->status, ['pending', 'approved']))
                    ->form([
                        Forms\Components\DatePicker::make('paid_date')
                            ->label('Datum plaćanja')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                    ])
                    ->action(function (VendorInvoice $record, array $data): void {
                        $record->markAsPaid($data['paid_date']);
                        Notification::make()
                            ->title('Račun označen kao plaćen')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListVendorInvoices::route('/'),
            'create' => Pages\CreateVendorInvoice::route('/create'),
            'edit' => Pages\EditVendorInvoice::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
