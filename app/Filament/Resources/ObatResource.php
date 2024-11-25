<?php

namespace App\Filament\Resources;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\ObatResource\Pages;
use App\Filament\Resources\ObatResource\RelationManagers;
use App\Models\Obat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Navigation\NavigationGroup;

class ObatResource extends Resource
{
    protected static ?string $model = Obat::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return
         $form->schema([
                 Forms\Components\TextInput::make('nama_obat') ->required(),

                 Forms\Components\TextInput::make('harga')->required()     ->maxLength(100),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->columns([
                    Tables\Columns\TextColumn::make('kode_obat')->label('Kode Obat')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_obat')->label('Nama Obat'),
                Tables\Columns\TextColumn::make('harga')->label('Harga Obat')

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListObats::route('/'),
            'create' => Pages\CreateObat::route('/create'),
            'edit' => Pages\EditObat::route('/{record}/edit'),
        ];
    }
    public static function getNavigationItems(): array
    {
        return in_array(auth()->user()?->role, ['admin', 'dokter'])
            ? [
                NavigationItem::make('Obat')
                    ->url(static::getUrl())
                    ->icon('heroicon-o-beaker')
                    ->group('Master Data')
            ]
            : [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'dokter']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'dokter']);
    }

    public static function getNavigationLabel(): string
    {
        return 'Obat';
    }

    public static function getNavigationGroup(): ?string
    {
        return auth()->user()->role === 'dokter' ? 'Data' : 'Master Data';
    }
}
