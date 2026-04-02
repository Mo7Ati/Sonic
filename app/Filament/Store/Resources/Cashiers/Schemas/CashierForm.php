<?php

namespace App\Filament\Store\Resources\Cashiers\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class CashierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.cashiers.general_information'))
                    ->description(__('forms.cashiers.general_information_description'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('forms.common.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('forms.common.email'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label(__('forms.common.phone'))
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Select::make('branch_id')
                            ->label(__('forms.cashiers.branch'))
                            ->options(Branch::query()->where('store_id', auth()->guard('store')->id())->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rule(Rule::exists('branches', 'id')->where('store_id', auth()->guard('store')->id())),
                        TextInput::make('password')
                            ->label(__('forms.common.password'))
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->helperText(fn(string $operation): ?string => $operation === 'edit' ? __('forms.common.leave_blank_to_keep_current_password') : null),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
