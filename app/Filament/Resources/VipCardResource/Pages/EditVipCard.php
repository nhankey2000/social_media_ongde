<?php

namespace App\Filament\Resources\VipCardResource\Pages;

use App\Filament\Resources\VipCardResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditVipCard extends EditRecord
{
    protected static string $resource = VipCardResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_date'] = Carbon::today()->toDateString();
        return $data;
    }
}