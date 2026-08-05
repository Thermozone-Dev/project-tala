<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class MyCustomLogin extends BaseLogin
{
    public bool $showPrivacyModal = false;

    protected string $view = 'filament.pages.auth.my-custom-login';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),

                Checkbox::make('terms_of_service')
                    ->label(new HtmlString('
                        I agree to the
                        <span style="color: rgb(13, 110, 253); text-decoration: underline; font-weight: 600; cursor: pointer;" wire:click="showModal">
                            Terms of Service and Privacy Policy
                        </span>'
                    ))
                    ->accepted()
                    ->extraAttributes([
                        'x-show' => 'true',
                    ]),

                $this->getRememberFormComponent(),
            ]);
    }

    public function showModal()
    {
        $this->dispatch('open-modal', id: 'data-privacy-modal');
    }

    public function agreeToTerms()
    {
        // Auto-check the terms_of_service checkbox
        $this->form->fill(['terms_of_service' => true]);

        // Close the modal
        $this->dispatch('close-modal', id: 'data-privacy-modal');
    }
}
