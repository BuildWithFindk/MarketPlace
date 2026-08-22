<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AddProfileFieldsToUsersTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Add new columns if they don't already exist
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'cover_photo')) {
                $table->string('cover_photo')->nullable()->after('username');
            }

            // Ensure avatar column exists; if `avatar_url` exists we'll try to rename it below
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('cover_photo');
            }

            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('avatar');
            }

            if (! Schema::hasColumn('users', 'dob_visibility')) {
                $table->enum('dob_visibility', ['public', 'private'])->default('private')->after('bio');
            }
        });

        // If an existing `avatar_url` column exists, try to rename it to `avatar`.
        if (Schema::hasColumn('users', 'avatar_url')) {
            try {
                Schema::table('users', function (Blueprint $table): void {
                    $table->renameColumn('avatar_url', 'avatar');
                });
            } catch (Throwable $e) {
                // Fallback: attempt a driver-agnostic raw rename first (works on modern MySQL/Postgres/SQLite)
                try {
                    DB::statement('ALTER TABLE users RENAME COLUMN avatar_url TO avatar');
                } catch (Throwable $e2) {
                    // Final fallback: create `avatar` if it doesn't exist, copy data and drop old column.
                    if (! Schema::hasColumn('users', 'avatar')) {
                        Schema::table('users', function (Blueprint $table): void {
                            $table->string('avatar')->nullable()->after('cover_photo');
                        });
                    }

                    // Copy values from avatar_url -> avatar
                    DB::table('users')
                        ->whereNotNull('avatar_url')
                        ->update(['avatar' => DB::raw('avatar_url')]);

                    // Drop the old column if possible
                    try {
                        Schema::table('users', function (Blueprint $table): void {
                            if (Schema::hasColumn('users', 'avatar_url')) {
                                $table->dropColumn('avatar_url');
                            }
                        });
                    } catch (Throwable $e3) {
                        // If dropping is not supported on this platform, leave the old column.
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Reverse the avatar rename carefully to avoid data loss.
        if (Schema::hasColumn('users', 'avatar')) {
            // If avatar_url does not exist, try to rename avatar -> avatar_url
            if (! Schema::hasColumn('users', 'avatar_url')) {
                try {
                    Schema::table('users', function (Blueprint $table): void {
                        $table->renameColumn('avatar', 'avatar_url');
                    });
                } catch (Throwable $e) {
                    try {
                        DB::statement('ALTER TABLE users RENAME COLUMN avatar TO avatar_url');
                    } catch (Throwable $e2) {
                        // Last resort: recreate avatar_url and copy data back, then drop avatar
                        Schema::table('users', function (Blueprint $table): void {
                            if (! Schema::hasColumn('users', 'avatar_url')) {
                                $table->string('avatar_url')->nullable()->after('avatar');
                            }
                        });

                        DB::table('users')
                            ->whereNotNull('avatar')
                            ->update(['avatar_url' => DB::raw('avatar')]);

                        try {
                            Schema::table('users', function (Blueprint $table): void {
                                if (Schema::hasColumn('users', 'avatar')) {
                                    $table->dropColumn('avatar');
                                }
                            });
                        } catch (Throwable $e3) {
                            // If drop fails, leave both columns to avoid data loss.
                        }
                    }
                }
            } else {
                // Both columns exist; drop the `avatar` column to restore original state.
                try {
                    Schema::table('users', function (Blueprint $table): void {
                        if (Schema::hasColumn('users', 'avatar')) {
                            $table->dropColumn('avatar');
                        }
                    });
                } catch (Throwable $e) {
                    // ignore - avoid data loss
                }
            }
        }

        // Drop added columns if present. We attempt to remove indexes first where appropriate.
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'dob_visibility')) {
                try {
                    $table->dropColumn('dob_visibility');
                } catch (Throwable $e) {
                    // ignore
                }
            }

            if (Schema::hasColumn('users', 'bio')) {
                try {
                    $table->dropColumn('bio');
                } catch (Throwable $e) {
                    // ignore
                }
            }

            if (Schema::hasColumn('users', 'cover_photo')) {
                try {
                    $table->dropColumn('cover_photo');
                } catch (Throwable $e) {
                    // ignore
                }
            }

            if (Schema::hasColumn('users', 'username')) {
                try {
                    // Attempt to drop the unique index then the column
                    $table->dropUnique(['username']);
                } catch (Throwable $e) {
                    // ignore if index doesn't exist
                }

                try {
                    $table->dropColumn('username');
                } catch (Throwable $e) {
                    // ignore
                }
            }
        });
    }
}
