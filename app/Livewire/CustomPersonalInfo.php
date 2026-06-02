<?php

namespace App\Livewire;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    public array $only = ['first_name', 'middle_name', 'last_name', 'suffix', 'email','phone','birthdate','gender'];

    // You can override the default components by returning an array of components.
    protected function getProfileFormComponents(): array
    {
        return [
            $this->getFirstNameComponent(),
            $this->getMiddleNameComponent(),
            $this->getLastNameComponent(),
            $this->getSuffixComponent(),
            $this->getEmailComponent(),
            $this->getPhoneComponent(),
            $this->getBirthdateComponent(),
            $this->getGenderComponent(),
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

    protected function getPhoneComponent(): TextInput
    {
        return TextInput::make('phone')
            ->required()
            ->tel()
            ->telRegex('/^(?:\+63|0)9\d{9}$/')
            ->placeholder('e.g. 0917XXXXXXXX or +63917XXXXXXXX')
            ->label(__('Phone'));
    }

    protected function getBirthdateComponent(): DatePicker
    {
        return DatePicker::make('birthdate')
            ->required()
            ->minDate(now()->subYears(150))
            ->maxDate(now())
            ->label(__('Birdate'));
    }

    protected function getGenderComponent(): Select
    {
        return Select::make('gender')
             ->options([
                 'Male' => 'Male',
                 'Female' => 'Female',
                 'Other' => 'Other',
             ])
            ->required()
            ->label(__('Gender'));
    }


    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title('Saved Data!')
            ->send();
    }
}
