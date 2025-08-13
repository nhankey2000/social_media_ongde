<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DanhmucNHSResource\Pages;
use App\Models\DanhmucNHS;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DanhmucNHSResource extends Resource
{
    protected static ?string $model = DanhmucNHS::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Danh mục NH';

    protected static ?string $navigationGroup = 'NH Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten_danh_muc')
                    ->label('Tên danh mục')
                    ->required()
                    ->maxLength(255)
                    ->unique(DanhmucNHS::class, 'ten_danh_muc'),
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

                Tables\Columns\TextColumn::make('dataPostsNH_count')
                    ->counts('dataPostsNH')
                    ->label('Posts')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('dataImagesNH_count')
                    ->counts('dataImagesNH')
                    ->label('Images')
                    ->badge()
                    ->color('success'),

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
             'index' => Pages\ListDanhmucNHS::route('/'),
             'create' => Pages\CreateDanhmucNHS::route('/create'),
             'edit' => Pages\EditDanhmucNHS::route('/{record}/edit'),
        ];
    }
}