<?php

namespace Modules\User\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\User\App\Models\PendingRegistration;

class CleanupPendingRegistrations extends Command
{
    protected $signature = 'user:cleanup-pending';

    protected $description = 'Remove expired pending registration records';

    public function handle(): int
    {
        $deleted = PendingRegistration::deleteExpired();

        $this->info("Deleted {$deleted} expired pending registrations");

        return self::SUCCESS;
    }
}
