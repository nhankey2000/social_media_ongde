<?php

namespace App\Filament\Resources\DataPostNHResource\Pages;

use App\Filament\Resources\DataPostNHResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPostNH extends EditRecord
{
    protected static string $resource = DataPostNHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load files hiện tại để hiển thị trong form
        $data['files'] = DataPostNHResource::getExistingFiles($this->record);
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Tách files ra
        $files = $data['files'] ?? [];
        unset($data['files']);
        unset($data['existing_files']);

        // Update DataPostNH
        $record->update($data);

        // Update files trong bảng DataImagesNH
        DataPostNHResource::updateFilesInImagesData($record, $files);

        return $record;
    }
}