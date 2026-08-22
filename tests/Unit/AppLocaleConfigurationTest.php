<?php

namespace Tests\Unit;

use Tests\TestCase;

class AppLocaleConfigurationTest extends TestCase
{
    public function test_only_english_and_nepali_are_available_locales(): void
    {
        $this->assertSame(['en', 'ne'], config('app.available_locales'));
    }
}
