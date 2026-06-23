<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Notifications\CustomNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FlashNotification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use UnitEnum;

class SendNotification extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.send-notification';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('general.navigation_groups.control_panel');
    }

    public static function getNavigationLabel(): string
    {
        return 'Send notification';
    }

    public function getTitle(): string
    {
        return 'Send notification';
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Compose & send')
                ->icon(Heroicon::PaperAirplane)
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('body')
                        ->label('Message')
                        ->required()
                        ->maxLength(1000)
                        ->rows(4),

                    Select::make('audience')
                        ->label('Audience')
                        ->options([
                            'all' => 'All customers',
                            'specific' => 'Specific customers',
                        ])
                        ->default('all')
                        ->required()
                        ->live(),

                    Select::make('customers')
                        ->label('Customers')
                        ->multiple()
                        ->searchable()
                        ->visible(fn (Get $get): bool => $get('audience') === 'specific')
                        ->required(fn (Get $get): bool => $get('audience') === 'specific')
                        ->getSearchResultsUsing(fn (string $search): array => Customer::query()
                            ->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone_number', 'like', "%{$search}%"))
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->all())
                        ->getOptionLabelsUsing(fn (array $values): array => Customer::query()
                            ->whereIn('id', $values)
                            ->pluck('name', 'id')
                            ->all()),
                ])
                ->action(fn (array $data) => $this->dispatchNotification($data)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function dispatchNotification(array $data): void
    {
        $notification = new CustomNotification($data['title'], $data['body']);

        $query = Customer::query()->where('is_active', true);

        if ($data['audience'] === 'specific') {
            $query->whereIn('id', $data['customers'] ?? []);
        }

        $count = 0;

        $query->chunkById(500, function (Collection $customers) use ($notification, &$count): void {
            Notification::send($customers, $notification);
            $count += $customers->count();
        });

        FlashNotification::make()
            ->title("Notification queued for {$count} customer(s).")
            ->success()
            ->send();
    }
}
