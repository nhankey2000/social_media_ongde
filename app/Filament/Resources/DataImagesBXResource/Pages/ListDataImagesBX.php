<?php

namespace App\Filament\Resources\DataImagesBXResource\Pages;

use App\Filament\Resources\DataImagesBXResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataImagesBX extends ListRecords
{
    protected static string $resource = DataImagesBXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
