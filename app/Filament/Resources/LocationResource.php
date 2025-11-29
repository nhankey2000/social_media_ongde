<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class  LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationLabel = 'Điểm kinh doanh';
    
    protected static ?string $modelLabel = 'Điểm kinh doanh';
    protected static ?string $navigationGroup = 'Quản lý';
    protected static ?string $pluralModelLabel = 'Điểm kinh doanh';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin cơ bản')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên điểm kinh doanh')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('code')
                            ->label('Mã điểm')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('VD: DKD-001')
                            ->helperText('Mã định danh duy nhất cho điểm kinh doanh'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Đang hoạt động')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Thông tin liên hệ')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Địa chỉ')
                            ->rows(2)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('manager_name')
                            ->label('Tên quản lý')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Telegram Integration')
                    ->schema([
                        Forms\Components\TextInput::make('chat_id')
                            ->label('Telegram Chat ID')
                            ->numeric()
                            ->helperText('ID của group Telegram liên kết với điểm này'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên điểm')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Location $record): ?string => $record->address),
                
                Tables\Columns\TextColumn::make('manager_name')
                    ->label('Quản lý')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('chat_id')
                    ->label('Telegram')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => !empty($record->chat_id))  // ← Fix này
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Hoạt động')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reports_count')
                    ->label('Số báo cáo')
                    ->counts('reports')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Ngưng hoạt động'),
                
                Tables\Filters\TernaryFilter::make('chat_id')
                    ->label('Telegram')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã liên kết')
                    ->falseLabel('Chưa liên kết')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('chat_id'),
                        false: fn ($query) => $query->whereNull('chat_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Thông tin điểm kinh doanh')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Mã điểm')
                            ->badge()
                            ->color('primary'),
                        
                        Infolists\Components\TextEntry::make('name')
                            ->label('Tên điểm'),
                        
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Trạng thái')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        
                        Infolists\Components\TextEntry::make('address')
                            ->label('Địa chỉ')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('manager_name')
                            ->label('Quản lý'),
                        
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Điện thoại'),
                        
                        Infolists\Components\TextEntry::make('chat_id')
                            ->label('Telegram Chat ID')
                            ->badge()
                            ->color('info')
                            ->default('Chưa liên kết'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Thống kê')
                    ->schema([
                        Infolists\Components\TextEntry::make('reports_count')
                            ->label('Tổng báo cáo')
                            ->state(fn (Location $record) => $record->reports()->count()),
                        
                        Infolists\Components\TextEntry::make('pending_reports')
                            ->label('Đang chờ')
                            ->state(fn (Location $record) => $record->pendingReports()->count()),
                        
                        Infolists\Components\TextEntry::make('in_progress_reports')
                            ->label('Đang xử lý')
                            ->state(fn (Location $record) => $record->inProgressReports()->count()),
                        
                        Infolists\Components\TextEntry::make('completed_reports')
                            ->label('Hoàn thành')
                            ->state(fn (Location $record) => $record->completedReports()->count()),
                        
                        Infolists\Components\TextEntry::make('overdue_reports')
                            ->label('Quá hạn')
                            ->state(fn (Location $record) => $record->overdueReports()->count())
                            ->badge()
                            ->color('danger'),
                        
                        Infolists\Components\TextEntry::make('completion_rate')
                            ->label('Tỷ lệ hoàn thành')
                            ->state(fn (Location $record) => $record->getCompletionRate() . '%')
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Ghi chú')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->default('Không có ghi chú')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // ReportsRelationManager can be added here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'view' => Pages\ViewLocation::route('/{record}'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
