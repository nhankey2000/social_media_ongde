<?php

namespace App\Filament\Resources\DanhmucNHSResource\Pages;

use App\Filament\Resources\DanhmucNHSResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDanhmucNHS extends ListRecords
{
    protected static string $resource = DanhmucNHSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
