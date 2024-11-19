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

class ObatResource extends Resource
{
    protected static ?string $model = Obat::class;



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
}
