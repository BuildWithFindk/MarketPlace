<?php

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserSocialLink extends Model
{
    protected $table = 'user_social_links';

    protected $fillable = [
        'user_id',
        'platform',
        'username',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProfileUrl(): string
    {
        return match ($this->platform) {
            'facebook' => 'https://facebook.com/'.$this->username,
            'instagram' => 'https://instagram.com/'.$this->username,
            'tiktok' => 'https://tiktok.com/@'.$this->username,
            default => '#',
        };
    }

    public function getPlatformIcon(): string
    {
        return match ($this->platform) {
            'facebook' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="#1877F2"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S1.86 6.48 1.86 12.07c0 5 3.66 9.13 8.44 9.93v-7.03H8.39v-2.9h1.91V9.41c0-1.89 1.12-2.94 2.83-2.94.82 0 1.68.15 1.68.15v1.84h-.94c-.93 0-1.22.58-1.22 1.18v1.41h2.07l-.33 2.9h-1.74v7.03c4.78-.8 8.44-4.93 8.44-9.93z"/></svg>
SVG
            ,
            'instagram' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><defs><linearGradient id="ig" x1="0" x2="1" y1="0" y2="1"><stop offset="0" stop-color="#f09433"/><stop offset="0.3" stop-color="#e6683c"/><stop offset="0.6" stop-color="#dc2743"/><stop offset="1" stop-color="#cc2366"/></linearGradient></defs><rect width="24" height="24" rx="6" fill="url(#ig)"/><path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2A3.2 3.2 0 1 1 12 8.8a3.2 3.2 0 0 1 0 6.4zM17.4 6.6a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4z" fill="#fff"/></svg>
SVG
            ,
            'tiktok' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M12.5 3v6.5a3.5 3.5 0 1 0 3.5 3.5V7.5h2A6.5 6.5 0 1 1 12.5 3z" fill="#000"/></svg>
SVG
            ,
            default => '',
        };
    }
}
