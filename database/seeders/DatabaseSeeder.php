<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Database\Seeders\CategorySeeder;
use Modules\Listing\Database\Seeders\ListingCustomFieldSeeder;
use Modules\Location\Database\Seeders\LocationSeeder;
use Modules\User\Database\Seeders\AuthUserSeeder;
use Modules\User\Database\Seeders\UserWorkspaceSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthUserSeeder::class,
            LocationSeeder::class,
            CategorySeeder::class,
            ListingCustomFieldSeeder::class,
            UserWorkspaceSeeder::class,
        ]);
    }
}
