<?php

namespace App\Filament\Resources\DanhmucBXResource\Pages;

use App\Filament\Resources\DanhmucBXResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDanhmucBX extends EditRecord
{
    protected static string $resource = DanhmucBXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
