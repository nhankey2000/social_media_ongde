<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageMenuResource\Pages;
use App\Models\ImageMenu;
use App\Models\MenuCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImageMenuResource extends Resource
{
    protected static ?string $model = ImageMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Ảnh Menu';

    protected static ?string $modelLabel = 'Ảnh Menu';

    protected static ?string $pluralModelLabel = 'Ảnh Menu';

    protected static ?string $navigationGroup = 'Quản lý Menu';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin ảnh')
                    ->schema([
                        Forms\Components\Select::make('menu_category_id')
                            ->label('Danh mục Menu')
                            ->relationship('menuCategory', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên danh mục')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(MenuCategory::class, 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                        if ($state) {
                                            $slug = self::generateUniqueSlug($state);
                                            $set('slug', $slug);
                                        }
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug (URL thân thiện)')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(MenuCategory::class, 'slug')
                                    ->rules(['regex:/^[a-z0-9-]+$/'])
                                    ->helperText('Chỉ được sử dụng chữ thường, số và dấu gạch ngang. VD: khai-vi, mon-chinh, thuc-uong')
                                    ->placeholder('khai-vi')
                                    ->suffixIcon('heroicon-o-link'),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Tạo danh mục mới')
                                    ->modalSubmitActionLabel('Tạo danh mục')
                                    ->modalWidth('md')
                                    ->action(function (array $data) {
                                        if (empty($data['slug']) && !empty($data['name'])) {
                                            $data['slug'] = self::generateUniqueSlug($data['name']);
                                        }

                                        $category = MenuCategory::create($data);

                                        \Filament\Notifications\Notification::make()
                                            ->success()
                                            ->title('Thành công')
                                            ->body("Danh mục '{$category->name}' đã được tạo với slug: {$category->slug}")
                                            ->duration(5000)
                                            ->send();

                                        return $category;
                                    });
                            }),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Ảnh')
                            ->image()
                            ->multiple() // Cho phép upload nhiều ảnh
                            ->required()
                            ->directory('images')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->columnSpanFull()
                            ->helperText('Định dạng: JPG, PNG, WebP, GIF. Kích thước tối đa: 5MB. Mỗi ảnh sẽ được lưu thành một bản ghi riêng với danh mục đã chọn.')
                            ->default(function ($record) {
                                return $record ? [$record->image_path] : [];
                            }) // Điền ảnh hiện tại khi chỉnh sửa
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $slug = self::removeVietnameseAccents($name);

        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;

        while (MenuCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function removeVietnameseAccents(string $str): string
    {
        $accentsMap = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ạ' => 'A', 'Ả' => 'A', 'Ã' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ậ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ặ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẹ' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ệ' => 'E', 'Ể' => 'E', 'Ễ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ị' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ọ' => 'O', 'Ỏ' => 'O', 'Õ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ộ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ợ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ụ' => 'U', 'Ủ' => 'U', 'Ũ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ự' => 'U', 'Ử' => 'U', 'Ữ' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỵ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y',
            'Đ' => 'D',
        ];

        return strtr($str, $accentsMap);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Ảnh')
                    ->extraImgAttributes(['class' => 'w-20 h-20 object-cover rounded-lg']),

                Tables\Columns\TextColumn::make('menuCategory.name')
                    ->label('Danh mục')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('menuCategory.slug')
                    ->label('Slug')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('image_path')
                    ->label('Tên file')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->copyable()
                    ->copyMessage('Đã copy tên file!')
                    ->formatStateUsing(fn (string $state): string => basename($state)),

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
                Tables\Filters\SelectFilter::make('menu_category_id')
                    ->label('Danh mục')
                    ->relationship('menuCategory', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Từ ngày'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Xem trước')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn (ImageMenu $record): \Illuminate\Contracts\View\View => view(
                        'filament.resources.image-menu-resource.preview-image',
                        ['record' => $record]
                    ))
                    ->modalHeading(fn (ImageMenu $record): string => 'Xem trước ảnh: ' . $record->menuCategory->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng'),

                Tables\Actions\EditAction::make()
                    ->label('Sửa'),

                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->requiresConfirmation()
                    ->modalHeading('Xóa ảnh')
                    ->modalDescription('Bạn có chắc chắn muốn xóa ảnh này?')
                    ->modalSubmitActionLabel('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa đã chọn')
                        ->requiresConfirmation()
                        ->modalHeading('Xóa các ảnh đã chọn')
                        ->modalDescription('Bạn có chắc chắn muốn xóa các ảnh này?')
                        ->modalSubmitActionLabel('Xóa'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Chưa có ảnh nào')
            ->emptyStateDescription('Thêm ảnh đầu tiên cho menu.')
            ->emptyStateIcon('heroicon-o-photo')
            ->poll('30s');
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
            'index' => Pages\ListImageMenus::route('/'),
            'create' => Pages\CreateImageMenu::route('/create'),
            'edit' => Pages\EditImageMenu::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['menuCategory']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['image_path', 'menuCategory.name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Danh mục' => $record->menuCategory->name,
            'Ngày tạo' => $record->created_at->format('d/m/Y'),
        ];
    }
}