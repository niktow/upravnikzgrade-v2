<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceProgramResource\Pages;
use App\Filament\Resources\MaintenanceProgramResource\RelationManagers;
use App\Models\MaintenanceProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceProgramResource extends Resource
{
    protected static ?string $model = MaintenanceProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    // Sakrij iz navigacije dok se ne implementira
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMaintenancePrograms::route('/'),
            'create' => Pages\CreateMaintenanceProgram::route('/create'),
            'edit' => Pages\EditMaintenanceProgram::route('/{record}/edit'),
        ];
    }
}
