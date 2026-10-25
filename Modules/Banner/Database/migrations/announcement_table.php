<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->enum('type', [
                'text',
                'background_image',
                'banner_image',
            ]);

            $table->text('message')->nullable();

            $table->string('background_color', 7)
                ->default('#111827');

            $table->string('text_color', 7)
                ->default('#FFFFFF');

            $table->string('background_image')->nullable();

            $table->string('banner_image')->nullable();

            $table->enum('position', [
                'top',
                'bottom',
                'homepage',
                'listing',
            ])->default('top');

            $table->unsignedInteger('priority')->default(0);

            $table->boolean('is_active')->default(false);

            $table->timestamp('starts_at')->nullable();

            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index([
                'position',
                'is_active',
                'starts_at',
                'ends_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};