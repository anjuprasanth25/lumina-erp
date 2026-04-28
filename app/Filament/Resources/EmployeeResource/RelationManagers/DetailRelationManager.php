<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enum\FamilyStatus;
use App\Enum\Gender;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DetailRelationManager extends RelationManager
{
    protected static string $relationship = 'detail';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('dob')
                    ->label('Date of Birth')
                    ->required(),
                Select::make('gender')
                    ->label('Gender')
                    ->options(Gender::class)
                    ->required(),
                Select::make('family_status')
                    ->label('Family Status')
                    ->options(FamilyStatus::class)
                    ->required()
                    ->native(false)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('dob')->label('Date Of Birth'),
                TextColumn::make('gender'),
                TextColumn::make('family_status')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Employee Details')
                    ->visible(fn($livewire) => $livewire->ownerRecord->detail === null)
                    ->createAnother(false)
                    ->label('Save'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
