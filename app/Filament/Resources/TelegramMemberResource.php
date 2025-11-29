<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramMemberResource\Pages;
use App\Models\TelegramMember;
use App\Models\Location;
use App\Services\TelegramMemberService;
use App\Services\TaskAssignmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TelegramMemberResource extends Resource
{
    protected static ?string $model = TelegramMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Telegram Members';

    protected static ?string $modelLabel = 'Telegram Member';

    protected static ?string $pluralModelLabel = 'Telegram Members';

    protected static ?string $navigationGroup = 'Quản lý';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin cơ bản')
                    ->schema([
                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->options(Location::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('telegram_id')
                            ->label('Telegram ID')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->helperText('ID Telegram của user'),

                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->helperText('Username Telegram (không có @)'),

                        Forms\Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required(),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Last Name'),

                        Forms\Components\TextInput::make('full_name')
                            ->label('Full Name')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Tự động tạo từ first_name + last_name'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Vai trò & Keywords')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Vai trò')
                            ->options([
                                'IT' => 'IT',
                                'Bảo trì' => 'Bảo trì',
                                'Kế toán' => 'Kế toán',
                                'Phục vụ' => 'Phục vụ',
                                'Bếp' => 'Bếp',
                                'Lễ tân' => 'Lễ tân',
                                'Quản lý' => 'Quản lý',
                            ])
                            ->searchable()
                            ->helperText('Nếu để trống, hệ thống sẽ tự động phát hiện từ tên')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Auto-generate keywords khi chọn role
                                if ($state && empty($get('keywords'))) {
                                    $keywords = TelegramMember::generateKeywords($state);
                                    $set('keywords', $keywords);
                                }
                            }),

                        Forms\Components\TagsInput::make('keywords')
                            ->label('Keywords')
                            ->helperText('Các từ khóa để tự động giao việc. Nhấn Enter sau mỗi keyword.')
                            ->placeholder('VD: máy tính, wifi, phần mềm')
                            ->suggestions(function ($get) {
                                $role = $get('role');
                                if ($role) {
                                    return TelegramMember::generateKeywords($role);
                                }
                                return [];
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Trạng thái')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Đang hoạt động')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\DateTimePicker::make('last_seen_at')
                            ->label('Last Seen')
                            ->disabled()
                            ->displayFormat('d/m/Y H:i'),
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

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Tên')
                    ->sortable()
                    ->searchable()
                    ->description(fn (TelegramMember $record): string =>
                    $record->username ? "@{$record->username}" : "TG ID: {$record->telegram_id}"
                    )
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Vai trò')
                    ->colors([
                        'primary' => 'IT',
                        'success' => 'Bảo trì',
                        'warning' => 'Kế toán',
                        'info' => 'Phục vụ',
                        'danger' => 'Bếp',
                        'secondary' => fn ($state) => is_null($state),
                    ])
                    ->default('Chưa xác định')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keywords')
                    ->label('Keywords')
                    ->badge()
                    ->limit(3)
                    ->tooltip(function (TelegramMember $record): ?string {
                        if (!$record->keywords || count($record->keywords) <= 3) {
                            return null;
                        }
                        return implode(', ', array_slice($record->keywords, 3));
                    }),

                Tables\Columns\TextColumn::make('taskAssignments_count')
                    ->label('Tasks')
                    ->counts('taskAssignments')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->description(function (TelegramMember $record): string {
                        $active = $record->taskAssignments()
                            ->whereIn('status', ['assigned', 'acknowledged'])
                            ->count();
                        return $active > 0 ? "{$active} active" : '';
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->since()
                    ->description(fn (TelegramMember $record): string =>
                    $record->last_seen_at ? $record->last_seen_at->format('d/m/Y H:i') : '-'
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Location')
                    ->options(Location::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('role')
                    ->label('Vai trò')
                    ->options([
                        'IT' => 'IT',
                        'Bảo trì' => 'Bảo trì',
                        'Kế toán' => 'Kế toán',
                        'Phục vụ' => 'Phục vụ',
                        'Bếp' => 'Bếp',
                        'Lễ tân' => 'Lễ tân',
                        'Quản lý' => 'Quản lý',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang active')
                    ->falseLabel('Không active'),

                Tables\Filters\Filter::make('has_tasks')
                    ->label('Có tasks')
                    ->query(fn (Builder $query): Builder => $query->has('taskAssignments')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('sync_role')
                    ->label('Auto-detect Role')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->action(function (TelegramMember $record) {
                        $role = TelegramMember::detectRole($record->full_name);
                        if ($role) {
                            $keywords = TelegramMember::generateKeywords($role);
                            $record->update([
                                'role' => $role,
                                'keywords' => $keywords,
                            ]);

                            Notification::make()
                                ->title('Đã tự động phát hiện!')
                                ->body("Role: {$role}, Keywords: " . count($keywords))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Không phát hiện được role')
                                ->body('Vui lòng đặt tên có vai trò (VD: Tân Bảo Trì)')
                                ->warning()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (TelegramMember $record) => !$record->role),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kích hoạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title('Đã kích hoạt!')
                                ->body(count($records) . ' members đã được kích hoạt')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Vô hiệu hóa')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title('Đã vô hiệu hóa!')
                                ->body(count($records) . ' members đã bị vô hiệu hóa')
                                ->warning()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync_from_telegram')
                    ->label('Sync từ Telegram')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('location_id')
                            ->label('Chọn Location')
                            ->options(Location::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $location = Location::find($data['location_id']);
                        $service = app(TelegramMemberService::class);

                        $result = $service->syncGroupMembers($location);

                        if ($result['success']) {
                            $stats = $result['stats'];
                            Notification::make()
                                ->title('Sync thành công!')
                                ->body("Mới: {$stats['new']}, Cập nhật: {$stats['updated']}, Tổng: {$stats['total']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Lỗi khi sync!')
                                ->body($result['error'])
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListTelegramMembers::route('/'),
            'create' => Pages\CreateTelegramMember::route('/create'),
            'view' => Pages\ViewTelegramMember::route('/{record}'),
            'edit' => Pages\EditTelegramMember::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['location', 'taskAssignments']);
    }
}