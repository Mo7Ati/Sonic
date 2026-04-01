<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->label(__('forms.admins.admin_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('forms.common.name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('forms.common.email'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Select::make('roles')
                            ->label(__('forms.admins.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        TextInput::make('password')
                            ->label(__('forms.common.password'))
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->helperText(fn(string $operation): ?string => $operation === 'edit' ? __('forms.common.leave_blank_to_keep_current_password') : null),
                        Toggle::make('is_active')
                            ->label(__('forms.common.is_active'))
                            ->default(true)
                            ->required(),
                    ])->columnSpanFull(),
            ]);
    }
}
