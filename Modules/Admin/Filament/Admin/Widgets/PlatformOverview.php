<?php

namespace Modules\Admin\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Banner\App\Models\AdRequest;
use Modules\Conversation\App\Models\Conversation;
use Modules\Listing\Models\Listing;
use Modules\User\App\Models\User;

class PlatformOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Platform Overview';

    protected function getStats(): array
    {
        return [
            $this->buildUserStat(),
            $this->buildListingStat(),
            $this->buildConversationStat(),
            $this->buildBannedUserStat(),
            $this->buildPendingAdRequestStat(),
        ];
    }

    private function buildUserStat(): Stat
    {
        $count = User::query()->where('status', 'active')->count();

        return Stat::make('Active Users', number_format($count))
            ->icon('heroicon-o-users')
            ->color('success');
    }

    private function buildListingStat(): Stat
    {
        $count = Listing::query()->where('status', 'active')->count();

        return Stat::make('Live Listings', number_format($count))
            ->icon('heroicon-o-home-modern')
            ->color('primary');
    }

    private function buildConversationStat(): Stat
    {
        $count = Conversation::query()->count();

        return Stat::make('Conversations', number_format($count))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('info');
    }

    private function buildBannedUserStat(): Stat
    {
        $count = User::query()->where('status', 'banned')->count();

        return Stat::make('Banned Accounts', number_format($count))
            ->icon('heroicon-o-shield-exclamation')
            ->color('danger');
    }

    private function buildPendingAdRequestStat(): Stat
    {
        $count = AdRequest::query()->where('status', 'pending')->count();

        return Stat::make('Pending Ad Requests', number_format($count))
            ->icon('heroicon-o-bell-alert')
            ->color('warning');
    }
}
