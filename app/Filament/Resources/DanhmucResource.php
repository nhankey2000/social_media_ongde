<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DanhmucResource\Pages;
use App\Models\DanhmucData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DanhmucResource extends Resource
{
    protected static ?string $model = DanhmucData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Danh mục';

    protected static ?string $navigationGroup = 'LDL Ông Đề';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_danh_muc')
                    ->label('Tên danh mục')
                    ->required()
                    ->maxLength(255)
                    ->unique(DanhmucData::class, 'ten_danh_muc'),
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
            'index' => Pages\ListDanhmucs::route('/'),
            'create' => Pages\CreateDanhmuc::route('/create'),
            'edit' => Pages\EditDanhmuc::route('/{record}/edit'),
        ];
    }
}