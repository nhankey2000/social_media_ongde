<?php

namespace App\Filament\Resources\MenuNhaHangResource\Pages;

use App\Filament\Resources\MenuNhaHangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuNhaHangs extends ListRecords
{
    protected static string $resource = MenuNhaHangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
