<?php

namespace App\Filament\Resources\DataPostNHResource\Pages;

use App\Filament\Resources\DataPostNHResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataPostNH extends ListRecords
{
    protected static string $resource = DataPostNHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
