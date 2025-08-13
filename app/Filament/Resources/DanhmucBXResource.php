<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DanhmucBXResource\Pages;
use App\Models\DanhmucBX;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DanhmucBXResource extends Resource
{
    protected static ?string $model = DanhmucBX::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Danh mục';

    protected static ?string $navigationGroup = 'Bánh Xèo Cô Tư';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_danh_muc')
                    ->label('Tên danh mục')
                    ->required()
                    ->maxLength(255)
                    ->unique(DanhmucBX::class, 'ten_danh_muc'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ten_danh_muc')
                    ->label('Tên danh mục')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDanhmucBX::route('/'),
            'create' => Pages\CreateDanhmucBX::route('/create'),
            'edit' => Pages\EditDanhmucBX::route('/{record}/edit'),
        ];
    }
}