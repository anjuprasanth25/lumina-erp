<?php

namespace App\Filament\Admin\Resources;

use App\Enum\FamilyStatus;
use App\Enum\Gender;
use App\Filament\Admin\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Date;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\Nullable;
use Str;
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
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true),
                TextInput::make('first_name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($get, $set) => $set('name', trim($get('first_name')) . ' ' . trim($get('middle_name')) . ' ' . trim($get('last_name'))))
                    ->required()
                    ->maxLength(255),
                TextInput::make('middle_name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($get, $set) => $set('name', trim($get('first_name')) . ' ' . trim($get('middle_name')) . ' ' . trim($get('last_name'))))
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($get, $set) => $set('name', trim($get('first_name')) . ' ' . trim($get('middle_name')) . ' ' . trim($get('last_name'))))
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Full Name')
                    ->readOnly()
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
                Select::make('line_manager_id')
                    ->label('Reporting To')
                    ->relationship(
                        'lineManager',
                        'name',
                        modifyQueryUsing: fn(Builder $query, $record)
                        => $query->when($record, fn($q) => $q->where('id', '!=', $record->id))
                    )
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} - {$record->designation->name}"),

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
            ])->columns(2),

            Section::make('Personal Information')
                ->relationship('details')
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

                ])->columns(3)

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
                //TextColumn::make('department.name')->label('Dept'),
                TextColumn::make('designation.name')->label('Designation')
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
