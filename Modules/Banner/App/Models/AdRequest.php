<?php

namespace Modules\Banner\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\User\App\Models\User;

class AdRequest extends Model
{
    protected $fillable = [
        'user_id',
        'ad_title',
        'image_path',
        'desired_link_type',
        'desired_link_value',
        'contact_phone',
        'contact_email',
        'message',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'banner_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function getImageUrl(): string
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? Storage::disk('public')->url($this->image_path)
            : asset('images/banner-placeholder.png');
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }
}
