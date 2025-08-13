<?php

namespace App\Filament\Resources\DanhmucBXResource\Pages;

use App\Filament\Resources\DanhmucBXResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDanhmucBX extends ListRecords
{
    protected static string $resource = DanhmucBXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
