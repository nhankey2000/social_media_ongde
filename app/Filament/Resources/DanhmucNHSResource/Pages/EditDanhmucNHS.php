<?php

namespace App\Filament\Resources\DanhmucNHSResource\Pages;

use App\Filament\Resources\DanhmucNHSResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDanhmucNHS extends EditRecord
{
    protected static string $resource = DanhmucNHSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
