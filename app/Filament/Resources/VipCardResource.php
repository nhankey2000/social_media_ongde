<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VipCardResource\Pages;
use App\Models\VipCard;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class VipCardResource extends Resource
{
    protected static ?string $model = VipCard::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Thẻ VIP';
    protected static ?string $pluralLabel = 'Thẻ VIP';
    protected static ?string $navigationGroup = 'Nhà Hàng Hồ Bơi';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Thông tin thẻ')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Loại thẻ')
                        ->options([
                            'GOLD' => 'GOLD VIP',
                            'SAPPHIRE' => 'SAPPHIRE VIP',
                            'DIAMOND' => 'DIAMOND VIP',
                        ])
                        ->required(),

                    Forms\Components\RichEditor::make('content')
                        ->label('Nội dung ưu đãi')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('expiry_date')
                        ->label('Thời hạn sử dụng')
                        ->required()
                        ->minDate(now()),

                    Forms\Components\DatePicker::make('created_date')
                        ->label('Ngày tạo')
                        ->default(now())
                        ->disabled(),

                    Forms\Components\DatePicker::make('updated_date')
                        ->label('Ngày cập nhật')
                        ->default(now())
                        ->visibleOn('edit'),

                    Forms\Components\Toggle::make('status')
                        ->label('Trạng thái')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Trạng thái')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Loại thẻ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'GOLD' => 'warning',
                        'SILVER' => 'gray',
                        'PLATINUM' => 'success',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('content')
                    ->label('Nội dung')
                    ->limit(50)
                    ->html(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Hạn dùng')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state < now() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_date')
                    ->label('Ngày tạo')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_date')
                    ->label('Cập nhật')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại thẻ')
                    ->options([
                        'GOLD' => 'GOLD VIP',
                        'SILVER' => 'SAPPHIRE VIP',
                        'PLATINUM' => 'DIAMOND VIP',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVipCards::route('/'),
            'create' => Pages\CreateVipCard::route('/create'),
            'edit' => Pages\EditVipCard::route('/{record}/edit'),
        ];
    }
}