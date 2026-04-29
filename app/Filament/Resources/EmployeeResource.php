<?php

namespace App\Filament\Resources;

use App\Enum\FamilyStatus;
use App\Enum\Gender;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Filament\Resources\EmployeeResource\RelationManagers\DetailRelationManager;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use function Laravel\Prompts\title;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Personal Information')->schema([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn($operation) => $operation === 'edit'),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('first_name')->required()->maxLength(255),
                TextInput::make('middle_name')->maxLength(255),
                TextInput::make('last_name')->required()->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true)
            ])->columns(2),

            Section::make('Employment Details')->schema([
                DatePicker::make('date_of_joining')
                    ->label('Joining Date')
                    ->required(),
                Select::make('company_id')
                    ->label('Company')
                    ->relationship(
                        name: 'company',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                    )->required()
                    ->searchable()
                    ->preload(),
                Select::make('department_id')
                    ->label('Department')
                    ->relationship(
                        name: 'department',
                        titleAttribute: 'name'
                    )->required()
                    ->searchable()
                    ->preload(),
                Select::make('designation_id')
                    ->label('Designation')
                    ->relationship(
                        name: 'designation',
                        titleAttribute: 'name'
                    )->searchable()
                    ->required()
                    ->preload(),
                Select::make('country_id')
                    ->label('Country')
                    ->relationship(
                        name: 'country',
                        titleAttribute: 'name'
                    )->searchable()
                    ->required()
                    ->preload(),
                Select::make('billing_type_id')
                    ->label('Biling Type')
                    ->relationship(
                        name: 'billingType',
                        titleAttribute: 'name'
                    )
                    ->required()
                    ->preload()
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('company.name')->label('Company'),
                TextColumn::make('department.name')->label('Dept')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn($record) => $record->is_system_record),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DetailRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
