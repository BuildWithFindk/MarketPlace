<?php

namespace Modules\User\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class PendingRegistration extends Model
{
    protected $fillable = [
        'token',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'country_code',
        'password',
        'gender',
        'dob',
        'country',
        'district',
        'city',
        'otp_code',
        'otp_expires_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'otp_code' => 'string',
            'dob' => 'date',
            'otp_expires_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function createPending(array $data): self
    {
        return DB::transaction(function () use ($data): self {
            static::query()
                ->where('email', $data['email'])
                ->orWhere('phone', $data['phone'])
                ->delete();

            return static::query()->create([
                ...$data,
                'token' => Str::uuid()->toString(),
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'password' => Hash::make($data['password']),
                'otp_code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'otp_expires_at' => now()->addMinutes(15),
                'expires_at' => now()->addMinutes(30),
            ]);
        });
    }

    public static function findByToken(string $token): ?self
    {
        return static::query()
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function hasOtpCode(string $code): bool
    {
        if ((string) $this->otp_code !== (string) trim($code)) {
            return false;
        }

        return true;
    }

    public function isOtpExpired(): bool
    {
        return now()->isAfter($this->otp_expires_at);
    }

    public function regenerateOtp(): self
    {
        $this->update([
            'otp_code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'otp_expires_at' => now()->addMinutes(15),
            'expires_at' => now()->addMinutes(30),
        ]);

        return $this;
    }

    public static function deleteExpired(): int
    {
        return static::query()
            ->where('expires_at', '<', now())
            ->delete();
    }

    public function convertToUser(string $username): User
    {
        return DB::transaction(function () use ($username): User {
            if ($this->isExpired()) {
                $this->delete();

                throw new RuntimeException('Pending registration has expired.');
            }

            if ($this->userAlreadyExists()) {
                $this->delete();

                throw new RuntimeException('User with this email or phone already exists.');
            }

            $user = User::query()->create([
                ...$this->userAttributes(),
                'username' => $username,
                'email_verified_at' => now(),
                'account_expires_at' => now()->addYear(),
                'status' => 'active',
            ]);

            $this->delete();

            return $user;
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->expires_at);
    }

    public function userAlreadyExists(): bool
    {
        return User::query()
            ->where('email', $this->email)
            ->orWhere('phone', $this->phone)
            ->exists();
    }

    private function userAttributes(): array
    {
        return [
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'password' => $this->password,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'country' => $this->country,
            'district' => $this->district,
            'city' => $this->city,
        ];
    }
}
