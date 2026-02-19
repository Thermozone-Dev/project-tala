<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    public array $only = ['first_name', 'middle_name', 'last_name', 'suffix', 'email'];

    // You can override the default components by returning an array of components.
    protected function getProfileFormComponents(): array
    {
        return [
            $this->getFirstNameComponent(),
            $this->getMiddleNameComponent(),
            $this->getLastNameComponent(),
            $this->getSuffixComponent(),
            $this->getEmailComponent(),
        ];
    }

    protected function getFirstNameComponent(): TextInput
    {
        return TextInput::make('first_name')
            ->required()
            ->label(__('First Name'));
    }

    protected function getMiddleNameComponent(): TextInput
    {
        return TextInput::make('middle_name')
            ->label(__('Middle Name'));
    }

    protected function getLastNameComponent(): TextInput
    {
        return TextInput::make('last_name')
            ->required()
            ->label(__('Last Name'));
    }

    protected function getSuffixComponent(): TextInput
    {
        return TextInput::make('suffix')
            ->label(__('Suffix'));
    }

    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title('Saved Data!')
            ->send();
    }
}
