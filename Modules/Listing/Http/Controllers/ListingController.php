<?php

namespace Modules\Listing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Category\Models\Category;
use Modules\Conversation\App\Models\Conversation;
use Modules\Favorite\App\Models\FavoriteSearch;
use Modules\Listing\Http\Requests\ListingRequest;
use Modules\Listing\Models\Listing;
use Modules\Listing\Support\ListingCustomFieldSchemaBuilder;
use Modules\Location\Models\Country;
use Modules\Theme\Support\ThemeManager;

class ListingController extends Controller
{
    private const NEPAL_DISTRICTS = [
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
        'Nawalpur',
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
        'Rukum East',
        'Rukum West',
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
        'Taplejung',
        'Terhathum',
        'Udayapur',
    ];

    public function __construct(private ThemeManager $themes) {}

    public function index()
    {
        $search = trim((string) request('search', ''));

        $categoryId = request()->integer('category');
        $categoryId = $categoryId > 0 ? $categoryId : null;

        $countryId = request()->integer('country');
        $countryId = $countryId > 0 ? $countryId : null;

        $cityId = request()->integer('city');
        $cityId = $cityId > 0 ? $cityId : null;

        $sellerUserId = request()->integer('user');
        $sellerUserId = $sellerUserId > 0 ? $sellerUserId : null;

        $minPriceInput = trim((string) request('min_price', ''));
        $maxPriceInput = trim((string) request('max_price', ''));
        $minPrice = is_numeric($minPriceInput) ? max((float) $minPriceInput, 0) : null;
        $maxPrice = is_numeric($maxPriceInput) ? max((float) $maxPriceInput, 0) : null;

        $dateFilter = (string) request('date_filter', 'all');
        $allowedDateFilters = ['all', 'today', 'week', 'month'];
        if (! in_array($dateFilter, $allowedDateFilters, true)) {
            $dateFilter = 'all';
        }

        $sort = (string) request('sort', 'smart');
        $allowedSorts = ['smart', 'newest', 'oldest', 'price_asc', 'price_desc'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'smart';
        }

        $locationSelection = Country::browseSelection($countryId, $cityId);
        $countryId = $locationSelection['country_id'];
        $cityId = $locationSelection['city_id'];
        $countries = $locationSelection['countries'];
        $cities = $locationSelection['cities'];
        $selectedCountryName = $locationSelection['selected_country_name'];
        $selectedCityName = $locationSelection['selected_city_name'];

        $listingDirectory = Category::listingDirectory($categoryId);

        $browseFilters = [
            'search' => $search,
            'country' => $selectedCountryName,
            'city' => $selectedCityName,
            'user_id' => $sellerUserId,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'date_filter' => $dateFilter,
        ];

        $allListingsTotal = Listing::query()
            ->active()
            ->forBrowseFilters($browseFilters)
            ->count();

        $listingsQuery = Listing::query()
            ->active()
            ->with(['category:id,name', 'user:id,name,username'])
            ->forBrowseFilters([
                ...$browseFilters,
                'category_ids' => $listingDirectory['filterIds'],
            ])
            ->applyBrowseSort($sort);

        $filteredListingsTotal = (clone $listingsQuery)->count();

        $listings = $listingsQuery
            ->paginate(16)
            ->withQueryString();

        $categories = $listingDirectory['categories'];
        $selectedCategory = $listingDirectory['selectedCategory'];

        $favoriteListingIds = [];
        $isCurrentSearchSaved = false;
        $conversationListingMap = [];

        if (auth()->check()) {
            $userId = (int) auth()->id();

            $favoriteListingIds = auth()->user()->favoriteListingIds();
            $conversationListingMap = Conversation::listingMapForBuyer($userId);

            $isCurrentSearchSaved = FavoriteSearch::isSavedForUser(auth()->user(), [
                'search' => $search,
                'category' => $categoryId,
            ]);
        }

        return view($this->themes->view('listing', 'index'), compact(
            'listings',
            'search',
            'categoryId',
            'countryId',
            'cityId',
            'sellerUserId',
            'minPriceInput',
            'maxPriceInput',
            'dateFilter',
            'sort',
            'countries',
            'cities',
            'selectedCategory',
            'categories',
            'favoriteListingIds',
            'isCurrentSearchSaved',
            'conversationListingMap',
            'allListingsTotal',
            'filteredListingsTotal',
        ));
    }

    public function show(Listing $listing)
    {
        $listing->trackViewBy(auth()->id());

        $listing->loadMissing([
            'user' => fn ($query) => $query
                ->select(['id', 'name', 'email', 'username', 'avatar', 'first_name', 'last_name', 'created_at'])
                ->withCount('listings'),
            'category:id,name,parent_id,slug',
            'category.parent:id,name,parent_id,slug',
            'category.parent.parent:id,name,parent_id,slug',
            'videos' => fn ($query) => $query->published()->ordered(),
        ]);
        $presentableCustomFields = ListingCustomFieldSchemaBuilder::presentableValues(
            $listing->category_id ? (int) $listing->category_id : null,
            $listing->custom_fields ?? [],
        );
        $gallery = $listing->galleryImageData();
        $listingVideos = $listing->getRelation('videos');
        $relatedListings = $listing->relatedSuggestions(12);
        $themePillCategories = Category::themePills(10);
        $breadcrumbCategories = $listing->category
            ? $listing->category->breadcrumbTrail()
            : collect();

        $isListingFavorited = false;
        $isSellerFavorited = false;
        $detailConversation = null;

        if (auth()->check()) {
            $userId = (int) auth()->id();

            $isListingFavorited = in_array((int) $listing->getKey(), auth()->user()->favoriteListingIds(), true);

            if ($listing->user_id) {
                $isSellerFavorited = auth()->user()
                    ->favoriteSellers()
                    ->whereKey($listing->user_id)
                    ->exists();
            }

            if ($listing->user_id && (int) $listing->user_id !== $userId) {
                $detailConversation = Conversation::detailForBuyerListing(
                    (int) $listing->getKey(),
                    $userId,
                );
            }
        }

        return view($this->themes->view('listing', 'show'), compact(
            'listing',
            'isListingFavorited',
            'isSellerFavorited',
            'presentableCustomFields',
            'detailConversation',
            'gallery',
            'listingVideos',
            'relatedListings',
            'themePillCategories',
            'breadcrumbCategories',
        ));
    }

    public function create(): RedirectResponse|View
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        return view('listing::create', [
            'categories' => Category::listingFormTree(),
            'districts' => self::NEPAL_DISTRICTS,
            'user' => auth()->user(),
        ]);
    }

    public function store(ListingRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validated();

        $listing = Listing::createFromFrontend([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'currency' => $validated['currency'] ?? 'NPR',
            'category_id' => $validated['category_id'],
            'status' => 'active',
            'country' => 'Nepal',
            'district' => $validated['district'] ?? null,
            'city' => $validated['city'] ?? null,
            'contact_phone' => $this->filledOrFallback($validated['contact_phone'] ?? null, $user->phone),
            'contact_email' => $this->filledOrFallback($validated['contact_email'] ?? null, $user->email),
            'expires_at' => now()->addDays(30),
        ], $user->getKey());

        $listing->attachUploadedImages((array) $request->file('images', []));

        return redirect()
            ->route('listings.show', $listing)
            ->with('success', 'Listing created successfully!');
    }

    public function edit(Listing $listing): RedirectResponse|View
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $listing->assertOwnedBy(auth()->user());

        return view('listing::edit', [
            'listing' => $listing,
            'categories' => Category::listingFormTree(),
            'districts' => self::NEPAL_DISTRICTS,
            'existingImages' => $listing->getMedia('listing-images'),
        ]);
    }

    public function update(ListingRequest $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $listing->assertOwnedBy($user);

        $validated = $request->validated();

        $listing->updateFromPanel([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'currency' => $validated['currency'] ?? 'NPR',
            'category_id' => $validated['category_id'],
            'status' => $validated['status'] ?? $listing->statusValue(),
            'country' => 'Nepal',
            'district' => $validated['district'] ?? null,
            'city' => $validated['city'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
        ]);

        $listing->attachUploadedImages((array) $request->file('images', []));

        return redirect()
            ->route('listings.show', $listing)
            ->with('success', 'Listing updated successfully!');
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $listing->assertOwnedBy($user);
        $listing->deleteWithImages();

        return redirect()
            ->route('listings.index')
            ->with('success', 'Listing deleted successfully.');
    }

    public function deleteImage(Request $request, Listing $listing, int $mediaId): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $listing->assertOwnedBy($user);

        if ($listing->deleteListingImage($mediaId)) {
            return response()->json([
                'success' => true,
                'message' => 'Image deleted.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Image not found.',
        ], 404);
    }

    private function filledOrFallback(?string $value, ?string $fallback): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }
}
