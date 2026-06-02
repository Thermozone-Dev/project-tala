<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }


//    protected function mutateFormDataBeforeFill(array $data): array

//    {
//        $data['roles'] = $this->record
//            ->roles()
//            ->pluck('id')
//            ->toArray();
//
//        return $data;
//    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = $data['first_name'];

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}
