<?php
// File: app/Filament/Resources/MenuCategoryResource/RelationManagers/ImagesRelationManager.php

namespace App\Filament\Resources\MenuCategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Ảnh của danh mục';

    protected static ?string $modelLabel = 'Ảnh';

    protected static ?string $pluralModelLabel = 'Ảnh';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin ảnh')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Tải ảnh lên')
                            ->image()
                            ->required()
                            ->directory('menu-images')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image_path')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Ảnh')
                    ->disk('public')
                    ->size(60)
                    ->circular(),

                Tables\Columns\TextColumn::make('image_path')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Thêm ảnh mới')
                    ->modalHeading('Thêm ảnh cho danh mục')
                    ->successNotificationTitle('Ảnh đã được thêm thành công!'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Xem')
                    ->modalContent(fn ($record) => view('filament.resources.menu-category-resource.view-image', ['record' => $record])),

                Tables\Actions\EditAction::make()
                    ->label('Sửa')
                    ->modalHeading('Chỉnh sửa ảnh')
                    ->successNotificationTitle('Ảnh đã được cập nhật!'),

                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->requiresConfirmation()
                    ->modalHeading('Xóa ảnh')
                    ->modalDescription('Bạn có chắc chắn muốn xóa ảnh này?')
                    ->successNotificationTitle('Ảnh đã được xóa!'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa các ảnh đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn xóa các ảnh này?')
                        ->successNotificationTitle('Các ảnh đã được xóa!'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Chưa có ảnh nào')
            ->emptyStateDescription('Thêm ảnh đầu tiên cho danh mục này.')
            ->emptyStateIcon('heroicon-o-photo');
    }
}