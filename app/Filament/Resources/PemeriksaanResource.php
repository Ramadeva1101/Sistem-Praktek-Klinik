<?php

namespace App\Filament\Resources;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\PemeriksaanResource\Pages;
use App\Filament\Resources\PemeriksaanResource\RelationManagers;
use App\Models\Pemeriksaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PemeriksaanResource extends Resource
{
    protected static ?string $model = Pemeriksaan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([

                Forms\Components\TextInput::make('nama_pemeriksaan')
                ->label('Nama Pemeriksaan')
                ->required(),
                Forms\Components\TextInput::make('harga_pemeriksaan')
                ->label('Harga Pemeriksaan')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_pemeriksaan')
                ->label('Kode Pemeriksaan'),
                Tables\Columns\TextColumn::make('nama_pemeriksaan')
                ->label('Nama Pemeriksaan'),
                Tables\Columns\TextColumn::make('harga_pemeriksaan')
                ->label('Harga Pemeriksaan'),
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
            'index' => Pages\ListPemeriksaans::route('/'),
            'create' => Pages\CreatePemeriksaan::route('/create'),
            'edit' => Pages\EditPemeriksaan::route('/{record}/edit'),
        ];
    }
    public static function getNavigationItems(): array
    {
        return in_array(auth()->user()?->role, ['admin', 'dokter'])
            ? [
                NavigationItem::make('Pemeriksaan')
                    ->url(static::getUrl())
                    ->icon('heroicon-o-document-text')
                    ->group('Master Data')
            ]
            : [];
    }
}
