<?php

return [
    // Social URLs are loaded from environment variables for developer control.
    // Use config('social.instagram') in views/controllers.
    'instagram' => env('SITE_INSTAGRAM_URL', null),
    'facebook' => env('SITE_FACEBOOK_URL', null),
    'youtube' => env('SITE_YOUTUBE_URL', null),
    'tiktok' => env('SITE_TIKTOK_URL', null),
    'discord' => env('SITE_DISCORD_URL', null),
    'linkedin' => env('SITE_LINKEDIN_URL', null),
    'whatsapp' => env('SITE_WHATSAPP', null),
    'blog' => env('SITE_BLOG_URL', null),
    'website' => env('SITE_WEBSITE_URL', null),
];
