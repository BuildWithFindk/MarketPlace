<?php

namespace Modules\User\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\User\App\Models\User;
use Throwable;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['show']),
        ];
    }

    public function show(string $username): View
    {
        $profileUser = User::query()
            ->with([
                'socialLinks',
                'listings' => fn ($query) => $query->latest(),
            ])
            ->withCount([
                'listings',
                'favoriteListings as saved_listings_count',
            ])
            ->where('username', $username)
            ->firstOrFail();

        $isOwner = auth()->check() && (int) auth()->id() === (int) $profileUser->getKey();

        return view('user::profile.show', [
            'user' => $profileUser,
            'profileUser' => $profileUser,
            'socialLinks' => $profileUser->socialLinks,
            'listings' => $profileUser->listings,
            'listingCount' => (int) $profileUser->listings_count,
            'listingsCount' => (int) $profileUser->listings_count,
            'savedCount' => (int) $profileUser->saved_listings_count,
            'savedListingsCount' => (int) $profileUser->saved_listings_count,
            'isOwner' => $isOwner,
        ]);
    }

    public function edit(): JsonResponse
    {
        $user = User::query()
            ->with('socialLinks')
            ->findOrFail($this->currentUser()->getKey());

        return response()->json([
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'bio' => $user->bio,
                'username' => $user->username,
                'phone' => $user->phone,
                'country' => $user->country,
                'district' => $user->district,
                'city' => $user->city,
                'dob_visibility' => $user->dob_visibility,
                'avatar_url' => $user->getAvatarUrl(),
                'cover_url' => $user->getCoverUrl(),
            ],
            'socialLinks' => $user->socialLinks->map(fn ($link): array => [
                'platform' => $link->platform,
                'username' => $link->username,
                'profile_url' => $link->getProfileUrl(),
            ])->values(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:200'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->getKey()),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'dob_visibility' => ['nullable', Rule::in(['public', 'private'])],
        ]);

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);

        $user->fill([
            ...$validated,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($firstName.' '.$lastName),
            'bio' => $validated['bio'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'district' => $validated['district'] ?? null,
            'city' => $validated['city'] ?? null,
            'dob_visibility' => $validated['dob_visibility'] ?? 'private',
        ])->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $this->currentUser($request);

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        try {
            $file = $request->file('avatar');
            $oldAvatar = $user->avatar;
            $path = $file->storeAs(
                'avatars',
                $user->getKey().'_'.now()->timestamp.'.'.$file->extension(),
                'public',
            );

            if (! is_string($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Avatar upload failed.',
                ], 500);
            }

            if ($oldAvatar !== $path) {
                $this->deletePublicFile($oldAvatar);
            }

            $user->forceFill(['avatar' => $path])->save();

            return response()->json([
                'success' => true,
                'url' => $user->getAvatarUrl(),
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar upload failed.',
            ], 500);
        }
    }

    public function uploadCover(Request $request): JsonResponse
    {
        $user = $this->currentUser($request);

        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        try {
            $file = $request->file('cover');
            $oldCover = $user->cover_photo;
            $path = $file->storeAs(
                'covers',
                $user->getKey().'_'.now()->timestamp.'.'.$file->extension(),
                'public',
            );

            if (! is_string($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cover upload failed.',
                ], 500);
            }

            if ($oldCover !== $path) {
                $this->deletePublicFile($oldCover);
            }

            $user->forceFill(['cover_photo' => $path])->save();

            return response()->json([
                'success' => true,
                'url' => $user->getCoverUrl(),
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Cover upload failed.',
            ], 500);
        }
    }

    public function updateSocialLinks(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);

        $validated = $request->validate([
            'links' => ['nullable', 'array'],
            'links.*.platform' => ['required', Rule::in(['facebook', 'instagram', 'tiktok']), 'distinct'],
            'links.*.username' => ['nullable', 'string', 'max:100'],
        ]);

        $user->socialLinks()->delete();

        foreach ($validated['links'] ?? [] as $link) {
            $username = trim((string) ($link['username'] ?? ''));

            if ($username === '') {
                continue;
            }

            $user->socialLinks()->create([
                'platform' => $link['platform'],
                'username' => $username,
            ]);
        }

        return back()->with('success', 'Social links updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $this->currentUser($request);

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function currentUser(?Request $request = null): User
    {
        $user = ($request ?? request())->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    private function deletePublicFile(?string $path): void
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
