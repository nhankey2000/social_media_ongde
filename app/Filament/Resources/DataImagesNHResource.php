<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataImagesNHResource\Pages;
use App\Models\DataImagesNH;
use App\Models\DanhmucNHS;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DataImagesNHResource extends Resource
{
    protected static ?string $model = DataImagesNH::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Images Data NH';

    protected static ?string $navigationGroup = 'NH Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'video' => 'Video',
                        'image' => 'Image',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('files', [])),

                Forms\Components\Select::make('id_danhmuc_data')
                    ->label('Danh mục NH')
                    ->required()
                    ->relationship('danhmucNHS', 'ten_danh_muc')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('ten_danh_muc')
                            ->label('Tên danh mục')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionAction(fn ($action) => $action->modalHeading('Thêm danh mục NH mới')),

                Forms\Components\FileUpload::make('files')
                    ->label('Upload Files')
                    ->multiple()
                    ->directory('media-nh-files')
                    ->disk('public')
                    ->required()
                    ->maxFiles(20)
                    ->reorderable()
                    ->acceptedFileTypes(function (callable $get) {
                        $type = $get('type');
                        if ($type === 'image') {
                            return ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
                        } elseif ($type === 'video') {
                            return ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'];
                        }
                        return [];
                    })
                    ->maxSize(function (callable $get) {
                        $type = $get('type');
                        return $type === 'video' ? 204800 : 20480;
                    })
                    ->helperText(function (callable $get) {
                        $type = $get('type');
                        if ($type === 'image') {
                            return 'Upload up to 20 images (JPG, PNG, GIF, WebP) - Max 20MB each';
                        } elseif ($type === 'video') {
                            return 'Upload up to 20 videos (MP4, AVI, MOV, WMV, WebM) - Max 200MB each';
                        }
                        return 'Please select a type first to enable file upload';
                    })
                    ->visible(fn (callable $get): bool => !empty($get('type')))
                    ->columnSpanFull(),

                // Hidden field để lưu URL cho edit form
                Forms\Components\Hidden::make('url')
                    ->visible(fn (?DataImagesNH $record): bool => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'video',
                        'success' => 'image',
                    ]),

                Tables\Columns\TextColumn::make('danhmucNHS.ten_danh_muc')
                    ->label('Danh mục NH')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('url')
                    ->label('Preview')
                    ->height(60)
                    ->width(60)
                    ->circular()
                    ->visible(fn ($record) => $record && $record->type === 'image'),

                Tables\Columns\TextColumn::make('url')
                    ->label('File')
                    ->limit(40)
                    ->copyable()
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : 'No file')
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->visible(false), // Ẩn vì timestamps = false
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'video' => 'Video',
                        'image' => 'Image',
                    ]),
                Tables\Filters\SelectFilter::make('id_danhmuc_data')
                    ->label('Danh mục NH')
                    ->options(DanhmucNHS::pluck('ten_danh_muc', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->url ? Storage::url(str_replace('/storage/', '', $record->url)) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->url)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
             'index' => Pages\ListDataImagesNH::route('/'),
             'create' => Pages\CreateDataImagesNH::route('/create'),
             'edit' => Pages\EditDataImagesNH::route('/{record}/edit'),
        ];
    }
}
