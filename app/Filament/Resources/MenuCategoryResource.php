<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuCategoryResource\Pages;
use App\Filament\Resources\MenuCategoryResource\RelationManagers;
use App\Models\MenuCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MenuCategoryResource extends Resource
{
    protected static ?string $model = MenuCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Danh mục Menu';

    protected static ?string $modelLabel = 'Danh mục Menu';

    protected static ?string $pluralModelLabel = 'Danh mục Menu';

    protected static ?string $navigationGroup = 'Quản lý Menu Ông Đề';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin danh mục')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên danh mục')
                            ->required()
                            ->maxLength(255)
                            ->unique(MenuCategory::class, 'name', ignoreRecord: true)
                            ->placeholder('Nhập tên danh mục menu')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $slug = Str::slug($state); // Sử dụng helper Str của Laravel
                                $set('slug', $slug);
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(MenuCategory::class, 'slug', ignoreRecord: true)
                            ->placeholder('Nhập slug hoặc để tự động tạo')
                            ->disabled(fn ($component) => $component->getState() !== null)
                            ->dehydrated() // Đảm bảo slug được gửi lên server ngay cả khi disabled
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên danh mục')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('images_count')
                    ->label('Số ảnh')
                    ->counts('images')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Có thể thêm filters ở đây nếu cần
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Xem'),
                Tables\Actions\EditAction::make()
                    ->label('Sửa'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->requiresConfirmation()
                    ->modalHeading('Xóa danh mục menu')
                    ->modalDescription('Bạn có chắc chắn muốn xóa danh mục này? Tất cả ảnh liên quan sẽ bị xóa.')
                    ->modalSubmitActionLabel('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa các danh mục đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn xóa các danh mục này? Tất cả ảnh liên quan sẽ bị xóa.')
                        ->modalSubmitActionLabel('Xóa'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Chưa có danh mục menu nào')
            ->emptyStateDescription('Tạo danh mục menu đầu tiên để bắt đầu.')
            ->emptyStateIcon('heroicon-o-squares-2x2');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuCategories::route('/'),
            'create' => Pages\CreateMenuCategory::route('/create'),
            'view' => Pages\ViewMenuCategory::route('/{record}'),
            'edit' => Pages\EditMenuCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['images']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Số ảnh' => $record->images->count(),
            'Ngày tạo' => $record->created_at->format('d/m/Y'),
        ];
    }
}