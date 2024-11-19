<?php

namespace App\Filament\Resources;

use Filament\Navigation\NavigationItem;
use App\Filament\Resources\PenggunaResource\Pages;
use App\Filament\Resources\PenggunaResource\RelationManagers;
use App\Models\Pengguna;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenggunaResource extends Resource
{
    protected static ?string $model = Pengguna::class;

  

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                    ->required(fn($record) => $record === null),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'dokter' => 'Dokter',
                        'kasir' => 'Kasir',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('role')->label('Role'),
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
            'index' => Pages\ListPenggunas::route('/'),
            'create' => Pages\CreatePengguna::route('/create'),
            'edit' => Pages\EditPengguna::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return in_array(auth()->user()?->role, ['admin'])
            ? [
                NavigationItem::make('Pengguna')
                    ->url(static::getUrl())
                    ->icon('heroicon-o-user'),
            ]
            : [];
    }
}
