<?php

namespace Modules\Banner\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Banner\App\Http\Requests\StoreAdRequest;
use Modules\Banner\App\Models\AdRequest;
use Modules\Category\Models\Category;
use Modules\Listing\Models\Listing;

class AdRequestController extends Controller
{
    public function create()
    {
        $categories = Category::all();
        $listings = Listing::where('user_id', auth()->id())->get();

        return view('banner::advertise.create', compact('categories', 'listings'));
    }

    public function store(StoreAdRequest $request)
    {
        $validated = $request->validated();

        $path = $request->file('image')->store('banners/requests', 'public');

        AdRequest::create([
            'user_id' => auth()->id(),
            'ad_title' => $validated['ad_title'],
            'image_path' => $path,
            'desired_link_type' => $validated['desired_link_type'],
            'desired_link_value' => $validated['desired_link_value'] ?? null,
            'contact_phone' => auth()->user()->phone,
            'contact_email' => auth()->user()->email,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('advertise.mine')->with(
            'success',
            'Your ad request was submitted! We will review it shortly.'
        );
    }

    public function myRequests()
    {
        $requests = AdRequest::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('banner::advertise.mine', compact('requests'));
    }
}
