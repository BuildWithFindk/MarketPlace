<?php

namespace Modules\User\App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Conversation\App\Models\Conversation;
use Modules\Conversation\App\Models\ConversationMessage;
use Modules\Favorite\App\Models\FavoriteSearch;
use Modules\Listing\Models\Listing;
use Modules\Site\App\Support\LocalMedia;
use Modules\User\App\States\UserStatus;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\ModelStates\HasStates;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasStates;
    use LogsActivity;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'avatar_url',
        'status',
        'first_name',
        'last_name',
        'phone',
        'country_code',
        'gender',
        'dob',
        'country',
        'district',
        'city',
        'phone_verified_at',
        'account_expires_at',
        'username',
        'cover_photo',
        'avatar',
        'bio',
        'dob_visibility',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'dob' => 'date',
            'phone_verified_at' => 'datetime',
            'account_expires_at' => 'datetime',
        ];
    }

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['password'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            default => false,
        };
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function favoriteListings()
    {
        return $this->belongsToMany(Listing::class, 'favorite_listings')->withTimestamps();
    }

    public function favoriteSellers()
    {
        return $this->belongsToMany(self::class, 'favorite_sellers', 'user_id', 'seller_id')->withTimestamps();
    }

    public function favoriteSearches()
    {
        return $this->hasMany(FavoriteSearch::class);
    }

    public function buyerConversations()
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations()
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function sentConversationMessages()
    {
        return $this->hasMany(ConversationMessage::class, 'sender_id');
    }

    public function canImpersonate(): bool
    {
        return $this->hasRole('admin');
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('admin');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! filled($this->avatar_url)) {
            return null;
        }

        $path = trim((string) $this->avatar_url);

        if (! LocalMedia::managesPath($path)) {
            return LocalMedia::url($path);
        }

        $disk = LocalMedia::disk();

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->url($path);
        }

        return LocalMedia::url($path);
    }

    public function getDisplayName(): string
    {
        return trim((string) ($this->name ?: $this->email ?: 'User'));
    }

    public function getEmail(): string
    {
        return trim((string) $this->email);
    }

    public function toggleFavoriteListing(Listing $listing): bool
    {
        $isFavorite = $this->favoriteListings()->whereKey($listing->getKey())->exists();

        if ($isFavorite) {
            $this->favoriteListings()->detach($listing->getKey());

            return false;
        }

        $this->favoriteListings()->syncWithoutDetaching([$listing->getKey()]);

        return true;
    }

    public function toggleFavoriteSeller(self $seller): bool
    {
        if ((int) $seller->getKey() === (int) $this->getKey()) {
            return false;
        }

        $isFavorite = $this->favoriteSellers()->whereKey($seller->getKey())->exists();

        if ($isFavorite) {
            $this->favoriteSellers()->detach($seller->getKey());

            return false;
        }

        $this->favoriteSellers()->syncWithoutDetaching([$seller->getKey()]);

        return true;
    }

    public function rememberListing(Listing $listing): void
    {
        $this->favoriteListings()->syncWithoutDetaching([$listing->getKey()]);
    }

    public function unreadInboxCount(): int
    {
        return Conversation::unreadCountForUser((int) $this->getKey());
    }

    public function unreadNotificationCount(): int
    {
        try {
            return (int) $this->unreadNotifications()->count();
        } catch (Throwable) {
            return 0;
        }
    }

    public function savedListingsCount(): int
    {
        try {
            return (int) $this->favoriteListings()->count();
        } catch (Throwable) {
            return 0;
        }
    }

    public function headerBadgeCounts(): array
    {
        return [
            'messages' => $this->unreadInboxCount(),
            'notifications' => $this->unreadNotificationCount(),
            'favorites' => $this->savedListingsCount(),
        ];
    }

    public static function totalCount(): int
    {
        return (int) static::query()->count();
    }

    public function favoriteListingIds(): array
    {
        return $this->favoriteListings()
            ->pluck('listings.id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public function homeFavoriteListingIds(): array
    {
        return $this->favoriteListingIds();
    }

    public function favoriteListingsPage(string $statusFilter = 'all', ?int $categoryId = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->favoriteListings()
            ->with(['category:id,name', 'user:id,name'])
            ->wherePivot('created_at', '>=', now()->subYear())
            ->when($statusFilter === 'active', fn ($query) => $query->where('status', 'active'))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderByPivot('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function favoriteSearchesPage(int $perPage = 10): LengthAwarePaginator
    {
        return $this->favoriteSearches()
            ->with('category:id,name')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function favoriteSellersPage(int $perPage = 10): LengthAwarePaginator
    {
        return $this->favoriteSellers()
            ->withCount([
                'listings as active_listings_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderByPivot('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function panelListingOptions(): Collection
    {
        return $this->listings()
            ->latest('id')
            ->get(['id', 'title', 'status']);
    }

    public function loadPanelProfile(): self
    {
        return $this->loadCount([
            'listings',
            'favoriteListings',
            'favoriteSearches',
            'favoriteSellers',
        ]);
    }

    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function isActive(): bool
    {
        return (string) $this->status === 'active';
    }

    public function getFullName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(UserSocialLink::class);
    }

    public function getProfileUrl(): string
    {
        return route('profile.show', ['username' => $this->ensureUsername()]);
    }

    public function ensureUsername(): string
    {
        if (filled($this->username)) {
            return $this->username;
        }

        $username = self::generateUsername(
            $this->first_name ?: Str::before((string) $this->email, '@'),
            $this->last_name ?: (string) $this->getKey()
        );

        $this->forceFill(['username' => $username])->save();

        return $username;
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='
            .urlencode(($this->first_name ?? '').' '.($this->last_name ?? ''))
            .'&background=DC2626&color=fff&size=200';
    }

    public function getCoverUrl(): ?string
    {
        if ($this->cover_photo) {
            return Storage::url($this->cover_photo);
        }

        return null;
    }

    public static function generateUsername(string $firstName, string $lastName): string
    {
        $base = Str::slug($firstName.'-'.$lastName);
        $username = $base;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $base.'-'.$counter;
            $counter++;
        }

        return $username;
    }
}
