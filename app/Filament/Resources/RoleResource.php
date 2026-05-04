<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Role Configuration')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(?string $state, Set $set) => $set('code', Str::slug($state, '_'))),

                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->nullable()
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('guard_name')
                                    ->default('web')
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                Toggle::make('is_system')
                                    ->label('System Protected')
                                    ->disabled()
                                    ->helperText('System roles cannot be moified')
                                    ->dehydrated(false)// Ensures the UI can't overwrite the DB value

                            ]),
                    ])->columns(2),
                Section::make('Module Permissions')
                    ->schema([
                        CheckboxList::make('modules')
                            ->relationship('modules', 'name')
                            ->columns(3)
                            ->gridDirection('column')
                            ->bulkToggleable()
                            ->searchable()
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->description(fn(Role $record) => $record->description)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->trueColor('danger'),
                TextColumn::make('modules_count')
                    ->counts('modules')
                    ->label('Permissions')
                    ->badge()

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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
