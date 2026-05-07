<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Notifications\Notifiable;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Credentials')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(
                                function ($record) {
                                    return Employee::query()
                                        ->where('is_system_record', '!=', 1)
                                        ->when(!$record, fn($q) => $q->whereDoesntHave('user'))
                                        ->pluck('name', 'id');
                                }

                            )
                            ->preload()
                            ->searchable()
                            ->required()
                            ->disabled(fn($record) => $record != null)//disable while edit
                            ->dehydrated()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                if ($state) {
                                    $employee = Employee::with('company')->find($state);
                                    $set('name', $employee->name);
                                    $set('email', $employee->email);
                                    $set('employee_company_name', $employee->company?->name);
                                }
                            })
                            //this loads while edit
                            ->afterStateHydrated(function ($set, $state) {
                                if ($state) {
                                    $employee = Employee::with('company')->find($state);
                                    $set('employee_company_name', $employee->company?->name);
                                }
                            }),

                        TextInput::make('name')
                            ->readOnly()
                            ->dehydrated(),
                        TextInput::make('email')
                            ->readOnly()
                            ->dehydrated(),
                        TextInput::make('employee_company_name')
                            ->label('Employee Company')
                            ->readOnly()
                            ->dehydrated()

                    ])->columns(2),

                Section::make('Company & Role Permissions')
                    ->schema([
                        Repeater::make('companyRoleAssignments')
                            ->label('Assign levels')
                            ->relationship('companyRoleAssignments')
                            ->schema([
                                Select::make('company_id')
                                    ->label('Company')
                                    ->options(Company::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->preload(),

                                Select::make('role_id')
                                    ->label('Roles')
                                    ->options(Role::where('is_active', true)
                                        ->where('is_system', false)
                                        ->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),


                            ])->columns(2)
                            ->rules([
                                fn() => function (string $attribute, $value, Closure $fail) {
                                    $combinations = collect($value)->map(fn($item) => $item['company_id'] . '-' . $item['role_id']);

                                    if ($combinations->duplicates()->isNotEmpty()) {
                                        $fail('You have already assigned this specific role to this company.');
                                    }
                                }
                            ])
                            ->addActionLabel('Add Access')
                            ->itemLabel(
                                fn(array $state): ?string =>
                                isset($state['company_id'])
                                ? Company::find($state['company_id'])?->name . ' Access Profile'
                                : 'New Profile Access Mapping'
                            ),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->hidden(fn($record) => $record->employee?->is_system_record)
                        ->hidden(fn($record) => !$record->is_active),

                    Tables\Actions\Action::make('deactivate')
                        ->hidden(fn($record) => $record->employee?->is_system_record)
                        ->label(fn($record) => $record->is_active ? 'Deactivate' : "Activate")
                        ->icon(fn($record) => $record->is_active ? 'heroicon-o-user-minus' : 'heroicon-o-user-plus')
                        ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['is_active' => !$record->is_active]);
                            Notification::make()
                                ->title('User status updated')
                                ->success()
                                ->send();
                        })
                ]),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
