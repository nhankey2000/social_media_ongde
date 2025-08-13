<?php

namespace App\Filament\Resources\DataImagesNHResource\Pages;

use App\Filament\Resources\DataImagesNHResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataImagesNH extends ListRecords
{
    protected static string $resource = DataImagesNHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
