<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuNhaHangResource\Pages;
use App\Models\MenuNhaHang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class MenuNhaHangResource extends Resource
{
    protected static ?string $model = MenuNhaHang::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Menu Nhà Hàng';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('img')
                ->label('Ảnh Menu')
                ->image()
                ->multiple()
                ->enableReordering()
                ->directory('menu')
                ->visibility('public')
                ->maxFiles(100)
                ->imageEditor()
                ->columnSpanFull()
                ->helperText('Upload nhiều ảnh = tạo nhiều bản ghi riêng biệt'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('img')
                    ->label('Ảnh Menu')
                    ->width(90)
                    ->height(90)
                    ->square()                                 // ← HÌNH VUÔNG ĐẸP NHẤT
                    ->extraImgAttributes(['class' => 'object-cover rounded-xl shadow-md border border-gray-200']),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Thứ tự')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        Storage::disk('public')->delete($record->img);
                        $record->delete();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Đã xóa ảnh thành công!')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            Storage::disk('public')->delete($record->img);
                            $record->delete();
                        }
                    }),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMenuNhaHangs::route('/'),
            'create' => Pages\CreateMenuNhaHang::route('/new'),
            'edit'   => Pages\EditMenuNhaHang::route('/{record}/edit'),
        ];
    }
}