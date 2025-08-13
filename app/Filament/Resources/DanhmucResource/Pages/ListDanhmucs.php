<?php

namespace App\Filament\Resources\DanhmucResource\Pages;

use App\Filament\Resources\DanhmucResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDanhmucs extends ListRecords
{
    protected static string $resource = DanhmucResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
