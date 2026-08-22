@php
    $profileModalUser = $profileUser ?? $user ?? auth()->user();
    $profileModalErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $profileSocialPlatforms = [
        'instagram' => [
            'label' => 'Instagram',
            'placeholder' => 'your.username',
            'prefix' => 'instagram.com/',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'placeholder' => 'your.username',
            'prefix' => 'facebook.com/',
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'placeholder' => 'your.username',
            'prefix' => 'tiktok.com/@',
        ],
    ];
    $profileSavedSocialLinks = collect($socialLinks ?? $profileModalUser->socialLinks ?? []);
    $profileOldSocialLinks = old('links');
    $profileModalSocialLinks = collect(is_array($profileOldSocialLinks)
        ? $profileOldSocialLinks
        : $profileSavedSocialLinks->map(fn ($link): array => [
            'platform' => (string) $link->platform,
            'username' => (string) $link->username,
        ])->values()->all())
        ->filter(fn (array $link): bool => isset($profileSocialPlatforms[(string) ($link['platform'] ?? '')]))
        ->unique(fn (array $link): string => (string) ($link['platform'] ?? ''))
        ->take(3)
        ->values();
    $profileDistricts = [
        'Achham',
        'Arghakhanchi',
        'Baglung',
        'Baitadi',
        'Bajhang',
        'Bajura',
        'Banke',
        'Bara',
        'Bardiya',
        'Bhaktapur',
        'Bhojpur',
        'Chitwan',
        'Dadeldhura',
        'Dailekh',
        'Dang',
        'Darchula',
        'Dhading',
        'Dhankuta',
        'Dhanusha',
        'Dolakha',
        'Dolpa',
        'Doti',
        'Eastern Rukum',
        'Gorkha',
        'Gulmi',
        'Humla',
        'Ilam',
        'Jajarkot',
        'Jhapa',
        'Jumla',
        'Kailali',
        'Kalikot',
        'Kanchanpur',
        'Kapilvastu',
        'Kaski',
        'Kathmandu',
        'Kavrepalanchok',
        'Khotang',
        'Lalitpur',
        'Lamjung',
        'Mahottari',
        'Makwanpur',
        'Manang',
        'Morang',
        'Mugu',
        'Mustang',
        'Myagdi',
        'Nawalparasi East',
        'Nawalparasi West',
        'Nuwakot',
        'Okhaldhunga',
        'Palpa',
        'Panchthar',
        'Parbat',
        'Parsa',
        'Pyuthan',
        'Ramechhap',
        'Rasuwa',
        'Rautahat',
        'Rolpa',
        'Rupandehi',
        'Salyan',
        'Sankhuwasabha',
        'Saptari',
        'Sarlahi',
        'Sindhuli',
        'Sindhupalchok',
        'Siraha',
        'Solukhumbu',
        'Sunsari',
        'Surkhet',
        'Syangja',
        'Tanahun',
        'Taplejung',
        'Tehrathum',
        'Udayapur',
        'Western Rukum',
    ];
    $profileSelectedDistrict = old('district', $profileModalUser->district);
    $profileInputClass = 'block h-12 w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 transition focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 disabled:bg-gray-50 disabled:text-gray-500';
    $profileSelectClass = $profileInputClass.' appearance-none pr-10';
    $profileLabelClass = 'mb-1.5 block text-sm font-medium text-gray-700';
    $profileInitialTab = old('links') ? 'social' : 'basic';
@endphp

<dialog
    id="profile-edit-modal"
    class="w-[94vw] max-w-[760px] overflow-hidden rounded-lg border border-gray-200 bg-white p-0 text-gray-900 transition backdrop:bg-slate-950/60"
    data-profile-edit-modal
    data-initial-tab="{{ $profileInitialTab }}"
    data-has-errors="{{ $profileModalErrors->any() ? 'true' : 'false' }}"
>
    <div class="flex max-h-[90vh] flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
            <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Edit Profile</h2>
            <button type="button" class="grid h-10 w-10 place-items-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-600" aria-label="Close" data-profile-modal-close>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </header>

        <nav class="flex gap-1 overflow-x-auto border-b border-gray-100 px-3 pt-2 sm:px-5" aria-label="Edit profile sections">
            <button type="button" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold transition" data-profile-modal-tab="basic" aria-selected="true">
                Basic Info
            </button>
            <button type="button" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold transition" data-profile-modal-tab="contact" aria-selected="false">
                Contact &amp; Location
            </button>
            <button type="button" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold transition" data-profile-modal-tab="social" aria-selected="false">
                Social Media
            </button>
        </nav>

        <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-6">
            <form id="profile-basic-form" method="POST" action="{{ route('profile.update') }}" class="hidden">
                @csrf
                @method('PATCH')
            </form>

            <section class="space-y-5" data-profile-modal-panel="basic">
                    <div class="grid gap-5 sm:grid-cols-[140px_1fr] sm:items-center">
                        <div class="flex justify-center sm:justify-start">
                            <form method="POST" action="{{ route('profile.avatar.upload') }}" enctype="multipart/form-data" data-profile-upload-form data-profile-upload-kind="avatar">
                                @csrf
                                <input id="profile-modal-avatar-upload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" data-profile-upload-input>
                                <label for="profile-modal-avatar-upload" class="group relative block h-28 w-28 cursor-pointer overflow-hidden rounded-full border-4 border-white bg-gray-100 ring-1 ring-gray-200">
                                    <img src="{{ $profileModalUser->getAvatarUrl() }}" alt="{{ $profileModalUser->getFullName() ?: $profileModalUser->getDisplayName() }}" class="h-full w-full object-cover" data-profile-modal-avatar-preview>
                                    <span class="absolute inset-0 grid place-items-center bg-black/45 text-white opacity-0 transition group-hover:opacity-100">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8h3l1.6-2.4A1.5 1.5 0 0 1 9.85 5h4.3a1.5 1.5 0 0 1 1.25.6L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z" />
                                            <circle cx="12" cy="13.5" r="3.2" stroke-width="1.8" />
                                        </svg>
                                    </span>
                                </label>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('profile.cover.upload') }}" enctype="multipart/form-data" data-profile-upload-form data-profile-upload-kind="cover">
                            @csrf
                            <input id="profile-modal-cover-upload" type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="hidden" data-profile-upload-input>
                            <label for="profile-modal-cover-upload" class="group relative block h-32 cursor-pointer overflow-hidden rounded-lg border border-gray-200 bg-gray-200">
                                <img
                                    data-profile-modal-cover-preview
                                    @if ($profileModalUser->getCoverUrl()) src="{{ $profileModalUser->getCoverUrl() }}" @endif
                                    alt="{{ $profileModalUser->getFullName() ?: $profileModalUser->getDisplayName() }} cover photo"
                                    class="h-full w-full object-cover {{ $profileModalUser->getCoverUrl() ? '' : 'hidden' }}"
                                >
                                <span class="absolute inset-0 grid place-items-center bg-black/30 text-sm font-semibold text-white opacity-0 transition group-hover:opacity-100">
                                    Change Cover
                                </span>
                            </label>
                        </form>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-modal-first-name" class="{{ $profileLabelClass }}">First Name</label>
                            <input id="profile-modal-first-name" form="profile-basic-form" name="first_name" type="text" value="{{ old('first_name', $profileModalUser->first_name) }}" autocomplete="given-name" required maxlength="50" class="{{ $profileInputClass }}">
                            @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="profile-modal-last-name" class="{{ $profileLabelClass }}">Last Name</label>
                            <input id="profile-modal-last-name" form="profile-basic-form" name="last_name" type="text" value="{{ old('last_name', $profileModalUser->last_name) }}" autocomplete="family-name" required maxlength="50" class="{{ $profileInputClass }}">
                            @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-3">
                            <label for="profile-modal-bio" class="block text-sm font-medium text-gray-700">Bio</label>
                            <span class="text-xs font-semibold text-gray-500" data-bio-counter>0/200</span>
                        </div>
                        <textarea id="profile-modal-bio" form="profile-basic-form" name="bio" maxlength="200" rows="4" class="block min-h-28 w-full resize-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 transition focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600" data-bio-input>{{ old('bio', $profileModalUser->bio) }}</textarea>
                        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="profile-modal-username" class="{{ $profileLabelClass }}">Username</label>
                        <div class="flex overflow-hidden rounded-lg border border-gray-300 bg-white transition focus-within:border-red-600 focus-within:ring-2 focus-within:ring-red-600">
                            <span class="hidden items-center border-r border-gray-200 bg-gray-50 px-4 text-sm font-medium text-gray-500 sm:inline-flex">hajurkoafno.com/</span>
                            <input id="profile-modal-username" form="profile-basic-form" name="username" type="text" value="{{ old('username', $profileModalUser->username) }}" autocomplete="username" required maxlength="50" class="block h-12 min-w-0 flex-1 border-0 bg-white px-4 py-3 text-base text-gray-900 focus:outline-none focus:ring-0" data-profile-username-input>
                        </div>
                        <p class="mt-1 text-xs font-medium text-gray-500 sm:hidden" data-profile-username-preview>hajurkoafno.com/{{ old('username', $profileModalUser->username) }}</p>
                        @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <span class="text-sm font-semibold text-gray-800">Show my age on profile</span>
                        <input type="hidden" form="profile-basic-form" name="dob_visibility" value="private">
                        <input type="checkbox" form="profile-basic-form" name="dob_visibility" value="public" class="peer sr-only" @checked(old('dob_visibility', $profileModalUser->dob_visibility) === 'public')>
                        <span class="relative h-7 w-12 rounded-full bg-gray-300 transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:bg-red-600 peer-checked:after:translate-x-5 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-600 peer-focus:ring-offset-2"></span>
                    </label>
                    @error('dob_visibility') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </section>

            <section class="hidden space-y-5" data-profile-modal-panel="contact">
                    <div>
                        <label for="profile-modal-phone" class="{{ $profileLabelClass }}">Phone number</label>
                        <input id="profile-modal-phone" form="profile-basic-form" name="phone" type="tel" value="{{ old('phone', $profileModalUser->phone) }}" readonly class="{{ $profileInputClass }}">
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-modal-country" class="{{ $profileLabelClass }}">Country</label>
                            <div class="relative">
                                <select id="profile-modal-country" form="profile-basic-form" name="country" class="{{ $profileSelectClass }}">
                                    <option value="Nepal" selected>Nepal</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </div>
                            @error('country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="profile-modal-district" class="{{ $profileLabelClass }}">District</label>
                            <div class="relative">
                                <select id="profile-modal-district" form="profile-basic-form" name="district" class="{{ $profileSelectClass }}">
                                    <option value="">Select district</option>
                                    @if (filled($profileSelectedDistrict) && ! in_array($profileSelectedDistrict, $profileDistricts, true))
                                        <option value="{{ $profileSelectedDistrict }}" selected>{{ $profileSelectedDistrict }}</option>
                                    @endif
                                    @foreach ($profileDistricts as $district)
                                        <option value="{{ $district }}" @selected($profileSelectedDistrict === $district)>{{ $district }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </div>
                            @error('district') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="profile-modal-city" class="{{ $profileLabelClass }}">City</label>
                        <input id="profile-modal-city" form="profile-basic-form" name="city" type="text" value="{{ old('city', $profileModalUser->city) }}" maxlength="100" class="{{ $profileInputClass }}">
                        @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
            </section>

            <form id="profile-social-form" method="POST" action="{{ route('profile.social.update') }}" class="hidden space-y-4" data-profile-modal-panel="social">
                @csrf

                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-base font-bold text-gray-900">Social links</h3>
                    <button type="button" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50" data-social-link-add>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />
                        </svg>
                        <span>Add a social link</span>
                    </button>
                </div>

                <div class="space-y-3" data-social-link-list></div>

                <p class="text-sm font-medium text-gray-500" data-social-link-empty>Add Instagram, Facebook, or TikTok usernames. One link per platform.</p>

                @error('links') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach (range(0, 2) as $linkErrorIndex)
                    @error("links.{$linkErrorIndex}.platform") <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error("links.{$linkErrorIndex}.username") <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @endforeach
            </form>
        </div>

        <footer class="flex items-center justify-end gap-3 border-t border-gray-100 bg-white px-5 py-4 sm:px-6">
            <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-600" data-profile-modal-close>
                Cancel
            </button>
            <button type="submit" form="profile-basic-form" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2" data-profile-modal-save>
                Save Changes
            </button>
        </footer>
    </div>

    <template data-social-link-template>
        <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-3 sm:grid-cols-[minmax(0,170px)_1fr_auto]" data-social-link-row>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Platform</label>
                <div class="relative">
                    <select class="block h-12 w-full rounded-lg border border-gray-300 bg-white px-3 pr-9 text-sm font-semibold text-gray-900 transition focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600" data-social-platform>
                        @foreach ($profileSocialPlatforms as $platform => $platformData)
                            <option value="{{ $platform }}">{{ $platformData['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Username</label>
                <input type="text" maxlength="100" required class="block h-12 w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 transition focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600" data-social-username>
                <p class="mt-1 text-xs font-semibold text-gray-500" data-social-preview></p>
            </div>
            <button type="button" class="mt-7 grid h-10 w-10 place-items-center rounded-full text-gray-500 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 sm:mt-7" aria-label="Remove social link" data-social-remove>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </template>
</dialog>

@once
    <script>
        (() => {
            const modal = document.querySelector('[data-profile-edit-modal]');

            if (!modal) {
                return;
            }

            const tabs = Array.from(modal.querySelectorAll('[data-profile-modal-tab]'));
            const panels = Array.from(modal.querySelectorAll('[data-profile-modal-panel]'));
            const saveButton = modal.querySelector('[data-profile-modal-save]');
            const closeButtons = modal.querySelectorAll('[data-profile-modal-close]');
            const bioInput = modal.querySelector('[data-bio-input]');
            const bioCounter = modal.querySelector('[data-bio-counter]');
            const usernameInput = modal.querySelector('[data-profile-username-input]');
            const usernamePreview = modal.querySelector('[data-profile-username-preview]');
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const socialPlatforms = @json($profileSocialPlatforms);
            const initialSocialLinks = @json($profileModalSocialLinks);
            const socialList = modal.querySelector('[data-social-link-list]');
            const socialTemplate = modal.querySelector('[data-social-link-template]');
            const socialAddButton = modal.querySelector('[data-social-link-add]');
            const socialEmpty = modal.querySelector('[data-social-link-empty]');
            const activeTabClasses = ['border-red-600', 'text-red-600'];
            const inactiveTabClasses = ['border-transparent', 'text-gray-600', 'hover:border-gray-300', 'hover:text-gray-900'];

            const setTab = (tabName) => {
                tabs.forEach((tab) => {
                    const isActive = tab.dataset.profileModalTab === tabName;

                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    tab.classList.toggle(activeTabClasses[0], isActive);
                    tab.classList.toggle(activeTabClasses[1], isActive);
                    inactiveTabClasses.forEach((className) => tab.classList.toggle(className, !isActive));
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.profileModalPanel !== tabName);
                });

                if (saveButton) {
                    saveButton.setAttribute('form', tabName === 'social' ? 'profile-social-form' : 'profile-basic-form');
                }
            };

            const updateBioCounter = () => {
                if (!bioInput || !bioCounter) {
                    return;
                }

                bioCounter.textContent = `${bioInput.value.length}/200`;
            };

            const updateUsernamePreview = () => {
                if (!usernameInput || !usernamePreview) {
                    return;
                }

                usernamePreview.textContent = `hajurkoafno.com/${usernameInput.value.trim() || 'username'}`;
            };

            const selectedPlatforms = (ignoredRow = null) => Array.from(socialList.querySelectorAll('[data-social-link-row]'))
                .filter((row) => row !== ignoredRow)
                .map((row) => row.querySelector('[data-social-platform]')?.value)
                .filter(Boolean);

            const updateSocialPreview = (row) => {
                const platform = row.querySelector('[data-social-platform]')?.value;
                const usernameInput = row.querySelector('[data-social-username]');
                const preview = row.querySelector('[data-social-preview]');
                const platformData = socialPlatforms[platform];
                const username = usernameInput?.value.trim() ?? '';

                if (usernameInput && platformData) {
                    usernameInput.placeholder = platformData.placeholder;
                }

                if (preview && platformData) {
                    preview.textContent = `${platformData.prefix}${username || platformData.placeholder}`;
                }
            };

            const reindexSocialRows = () => {
                const rows = Array.from(socialList.querySelectorAll('[data-social-link-row]'));

                rows.forEach((row, index) => {
                    const platformSelect = row.querySelector('[data-social-platform]');
                    const usernameInput = row.querySelector('[data-social-username]');
                    const unavailablePlatforms = selectedPlatforms(row);

                    if (platformSelect) {
                        platformSelect.name = `links[${index}][platform]`;

                        Array.from(platformSelect.options).forEach((option) => {
                            option.disabled = unavailablePlatforms.includes(option.value);
                        });
                    }

                    if (usernameInput) {
                        usernameInput.name = `links[${index}][username]`;
                    }

                    updateSocialPreview(row);
                });

                if (socialAddButton) {
                    socialAddButton.disabled = rows.length >= Object.keys(socialPlatforms).length;
                }

                if (socialEmpty) {
                    socialEmpty.classList.toggle('hidden', rows.length > 0);
                }
            };

            const firstAvailablePlatform = () => Object.keys(socialPlatforms).find((platform) => !selectedPlatforms().includes(platform));

            const addSocialRow = (link = {}) => {
                if (!socialList || !socialTemplate || socialList.children.length >= Object.keys(socialPlatforms).length) {
                    return;
                }

                const row = socialTemplate.content.firstElementChild.cloneNode(true);
                const platformSelect = row.querySelector('[data-social-platform]');
                const usernameInput = row.querySelector('[data-social-username]');
                const platform = socialPlatforms[link.platform] && !selectedPlatforms().includes(link.platform)
                    ? link.platform
                    : firstAvailablePlatform();

                if (!platform) {
                    return;
                }

                platformSelect.value = platform;
                usernameInput.value = link.username ?? '';
                platformSelect.addEventListener('change', () => reindexSocialRows());
                usernameInput.addEventListener('input', () => updateSocialPreview(row));
                row.querySelector('[data-social-remove]')?.addEventListener('click', () => {
                    row.remove();
                    reindexSocialRows();
                });

                socialList.appendChild(row);
                reindexSocialRows();
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => setTab(tab.dataset.profileModalTab));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => modal.close());
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.close();
                }
            });

            bioInput?.addEventListener('input', updateBioCounter);
            usernameInput?.addEventListener('input', updateUsernamePreview);

            socialAddButton?.addEventListener('click', () => addSocialRow());

            initialSocialLinks.forEach((link) => addSocialRow(link));

            document.querySelectorAll('[data-profile-upload-form]').forEach((form) => {
                const input = form.querySelector('[data-profile-upload-input]');
                const kind = form.dataset.profileUploadKind;

                if (!input) {
                    return;
                }

                input.addEventListener('change', async () => {
                    if (!input.files || input.files.length === 0) {
                        return;
                    }

                    const previewUrl = URL.createObjectURL(input.files[0]);
                    const previewTargets = kind === 'avatar'
                        ? document.querySelectorAll('[data-profile-avatar-image], [data-profile-modal-avatar-preview]')
                        : document.querySelectorAll('[data-profile-cover-image], [data-profile-modal-cover-preview]');
                    const previousImages = Array.from(previewTargets).map((image) => ({
                        image,
                        src: image.getAttribute('src'),
                        hidden: image.classList.contains('hidden'),
                    }));
                    const restorePreviousImages = () => {
                        previousImages.forEach(({ image, src, hidden }) => {
                            if (src === null) {
                                image.removeAttribute('src');
                            } else {
                                image.src = src;
                            }

                            image.classList.toggle('hidden', hidden);
                        });
                    };

                    previewTargets.forEach((image) => {
                        image.src = previewUrl;
                        image.classList.remove('hidden');
                    });

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: new FormData(form),
                        });
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            const errors = Object.values(data.errors ?? {}).flat();
                            restorePreviousImages();
                            window.alert(data.message ?? errors[0] ?? 'Upload failed.');
                            return;
                        }

                        previewTargets.forEach((image) => {
                            image.src = data.url;
                            image.classList.remove('hidden');
                        });
                    } catch (error) {
                        restorePreviousImages();
                        window.alert('Upload failed.');
                    } finally {
                        URL.revokeObjectURL(previewUrl);
                        input.value = '';
                    }
                });
            });

            setTab(modal.dataset.initialTab || 'basic');
            updateBioCounter();
            updateUsernamePreview();
            reindexSocialRows();

            if (modal.dataset.hasErrors === 'true') {
                modal.showModal();
            }
        })();
    </script>
@endonce
