<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Báo cáo';
    
    protected static ?string $modelLabel = 'Báo cáo';
    
    protected static ?string $pluralModelLabel = 'Báo cáo';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin báo cáo')
                    ->schema([
                        Forms\Components\Select::make('location_id')
                            ->label('Điểm kinh doanh')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên điểm')
                                    ->required(),
                                Forms\Components\TextInput::make('code')
                                    ->label('Mã điểm')
                                    ->required(),
                            ]),
                        
                        Forms\Components\TextInput::make('reporter_name')
                            ->label('Người báo cáo')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('reporter_telegram_id')
                            ->label('Telegram ID')
                            ->numeric()
                            ->placeholder('User ID trên Telegram'),
                        
                        Forms\Components\TextInput::make('reporter_username')
                            ->label('Telegram Username')
                            ->maxLength(255)
                            ->placeholder('@username'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nội dung')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Nội dung báo cáo')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('ai_response')
                            ->label('Chỉ đạo từ TGĐ AI')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Sẽ được AI tự động tạo...'),
                    ]),

                Forms\Components\Section::make('Trạng thái & Ưu tiên')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'pending' => 'Đang chờ',
                                'in_progress' => 'Đang xử lý',
                                'completed' => 'Đã hoàn thành',
                                'overdue' => 'Quá hạn',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        
                        Forms\Components\Select::make('priority')
                            ->label('Mức độ ưu tiên')
                            ->options([
                                'low' => 'Thấp',
                                'medium' => 'Trung bình',
                                'high' => 'Cao',
                            ])
                            ->default('low')
                            ->required()
                            ->native(false),
                        
                        Forms\Components\DateTimePicker::make('deadline')
                            ->label('Deadline')
                            ->seconds(false)
                            ->native(false),
                        
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Thời gian hoàn thành')
                            ->seconds(false)
                            ->native(false),
                        
                        Forms\Components\TextInput::make('completed_by')
                            ->label('Người hoàn thành')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('processing_time')
                            ->label('Thời gian xử lý (phút)')
                            ->numeric()
                            ->suffix('phút')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Thông tin bổ sung')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Điểm')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('reporter_name')
                    ->label('Người báo')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('content')
                    ->label('Nội dung')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(function (Report $record): string {
                        return $record->content;
                    }),
                
                Tables\Columns\TextColumn::make('ai_response')
                    ->label('Chỉ đạo AI')
                    ->limit(60)
                    ->searchable()
                    ->toggleable()
                    ->tooltip(function (Report $record): string {
                        return $record->ai_response ?? 'Chưa có';
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'overdue',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'in_progress',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-exclamation-circle' => 'overdue',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Đang chờ',
                        'in_progress' => 'Đang xử lý',
                        'completed' => 'Hoàn thành',
                        'overdue' => 'Quá hạn',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Ưu tiên')
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Thấp',
                        'medium' => 'TB',
                        'high' => 'Cao',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('deadline')
                    ->label('Deadline')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn (Report $record): string => 
                        $record->isOverdue() ? 'danger' : 'gray'
                    )
                    ->icon(fn (Report $record): ?string =>
                        $record->isOverdue() ? 'heroicon-o-exclamation-triangle' : null
                    ),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Hoàn thành lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('processing_time')
                    ->label('Thời gian xử lý')
                    ->suffix(' phút')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Điểm kinh doanh')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Đang chờ',
                        'in_progress' => 'Đang xử lý',
                        'completed' => 'Đã hoàn thành',
                        'overdue' => 'Quá hạn',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Ưu tiên')
                    ->options([
                        'low' => 'Thấp',
                        'medium' => 'Trung bình',
                        'high' => 'Cao',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('deadline')
                    ->label('Có deadline')
                    ->query(fn ($query) => $query->whereNotNull('deadline')),
                
                Tables\Filters\Filter::make('overdue')
                    ->label('Quá hạn')
                    ->query(fn ($query) => $query->overdue()),
                
                Tables\Filters\Filter::make('today')
                    ->label('Hôm nay')
                    ->query(fn ($query) => $query->today()),
                
                Tables\Filters\Filter::make('this_week')
                    ->label('Tuần này')
                    ->query(fn ($query) => $query->thisWeek()),
                
                Tables\Filters\Filter::make('this_month')
                    ->label('Tháng này')
                    ->query(fn ($query) => $query->thisMonth()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('markCompleted')
                        ->label('Đánh dấu hoàn thành')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Report $record) => !$record->isCompleted())
                        ->requiresConfirmation()
                        ->action(fn (Report $record) => $record->markAsCompleted()),
                    
                    Tables\Actions\Action::make('markOverdue')
                        ->label('Đánh dấu quá hạn')
                        ->icon('heroicon-o-exclamation-circle')
                        ->color('danger')
                        ->visible(fn (Report $record) => !$record->isCompleted() && !$record->isOverdue())
                        ->requiresConfirmation()
                        ->action(fn (Report $record) => $record->markAsOverdue()),
                    
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markCompleted')
                        ->label('Đánh dấu hoàn thành')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->markAsCompleted();
                            }
                        }),
                    
                    Tables\Actions\BulkAction::make('changePriority')
                        ->label('Đổi mức ưu tiên')
                        ->icon('heroicon-o-flag')
                        ->form([
                            Forms\Components\Select::make('priority')
                                ->label('Mức ưu tiên mới')
                                ->options([
                                    'low' => 'Thấp',
                                    'medium' => 'Trung bình',
                                    'high' => 'Cao',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, $data) {
                            foreach ($records as $record) {
                                $record->update(['priority' => $data['priority']]);
                            }
                        }),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Thông tin báo cáo')
                    ->schema([
                        Infolists\Components\TextEntry::make('location.full_name')
                            ->label('Điểm kinh doanh')
                            ->badge()
                            ->color('primary'),
                        
                        Infolists\Components\TextEntry::make('reporter_name')
                            ->label('Người báo cáo'),
                        
                        Infolists\Components\TextEntry::make('reporter_username')
                            ->label('Telegram')
                            ->default('Chưa có')
                            ->badge()
                            ->color('info'),
                        
                        Infolists\Components\TextEntry::make('status_label')
                            ->label('Trạng thái')
                            ->badge()
                            ->color(fn (Report $record) => $record->status_color),
                        
                        Infolists\Components\TextEntry::make('priority_label')
                            ->label('Ưu tiên')
                            ->badge()
                            ->color(fn (Report $record) => $record->priority_color),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Thời gian báo cáo')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Nội dung báo cáo')
                    ->schema([
                        Infolists\Components\TextEntry::make('content')
                            ->label('')
                            ->columnSpanFull()
                            ->prose(),
                    ]),

                Infolists\Components\Section::make('Chỉ đạo từ TGĐ AI')
                    ->schema([
                        Infolists\Components\TextEntry::make('ai_response')
                            ->label('')
                            ->default('Chưa có chỉ đạo')
                            ->columnSpanFull()
                            ->prose(),
                    ])
                    ->collapsed(fn (Report $record) => empty($record->ai_response)),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('deadline')
                            ->label('Deadline')
                            ->dateTime('d/m/Y H:i')
                            ->default('Không có deadline')
                            ->badge()
                            ->color(fn (Report $record) => $record->isOverdue() ? 'danger' : 'gray'),
                        
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Hoàn thành lúc')
                            ->dateTime('d/m/Y H:i')
                            ->default('Chưa hoàn thành'),
                        
                        Infolists\Components\TextEntry::make('completed_by')
                            ->label('Người hoàn thành')
                            ->default('N/A'),
                        
                        Infolists\Components\TextEntry::make('processing_time')
                            ->label('Thời gian xử lý')
                            ->suffix(' phút')
                            ->default('N/A'),
                        
                        Infolists\Components\TextEntry::make('time_remaining')
                            ->label('Thời gian còn lại')
                            ->state(fn (Report $record) => $record->getTimeRemaining() ?? 'N/A')
                            ->badge()
                            ->color(fn (Report $record) => $record->isOverdue() ? 'danger' : 'info'),
                    ])
                    ->columns(3),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['location']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'overdue')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
