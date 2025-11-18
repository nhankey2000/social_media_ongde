<?php

namespace App\Filament\Resources\VipCardResource\Pages;

use App\Filament\Resources\VipCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVipCards extends ListRecords
{
    protected static string $resource = VipCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
