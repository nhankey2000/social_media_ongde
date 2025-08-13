<?php

namespace App\Filament\Resources\DanhmucResource\Pages;

use App\Filament\Resources\DanhmucResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDanhmuc extends EditRecord
{
    protected static string $resource = DanhmucResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
