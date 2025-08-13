<?php

namespace App\Filament\Resources\DataPostBXResource\Pages;

use App\Filament\Resources\DataPostBXResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPostBX extends EditRecord
{
    protected static string $resource = DataPostBXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load files hiện tại để hiển thị trong form
        $data['files'] = DataPostBXResource::getExistingFiles($this->record);
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Tách files ra
        $files = $data['files'] ?? [];
        unset($data['files']);
        unset($data['existing_files']);

        // Update DataPostBX
        $record->update($data);

        // Update files trong bảng DataImagesBX
        DataPostBXResource::updateFilesInImagesData($record, $files);

        return $record;
    }
}