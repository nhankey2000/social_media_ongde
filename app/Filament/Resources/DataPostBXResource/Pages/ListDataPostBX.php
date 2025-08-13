<?php

namespace App\Filament\Resources\DataPostBXResource\Pages;

use App\Filament\Resources\DataPostBXResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataPostBX extends ListRecords
{
    protected static string $resource = DataPostBXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
