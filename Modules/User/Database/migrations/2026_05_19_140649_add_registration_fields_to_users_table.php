<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('country_code')->default('+977')->after('last_name');
            $table->string('phone')->nullable()->unique()->after('country_code');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->date('dob')->nullable()->after('phone_verified_at');
            $table->string('country')->nullable()->after('dob');
            $table->string('district')->nullable()->after('country');
            $table->string('city')->nullable()->after('district');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_phone_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_phone_unique');
            });
        }

        $columns = array_values(array_filter([
            'first_name',
            'last_name',
            'country_code',
            'phone',
            'phone_verified_at',
            'dob',
            'country',
            'district',
            'city',
        ], fn (string $column): bool => Schema::hasColumn('users', $column)));

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
