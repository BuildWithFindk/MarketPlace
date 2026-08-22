@extends('site::layouts.app')

@section('title', 'My Ad Requests')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-5xl">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Ad Requests</h1>
        @if($requests->isNotEmpty())
            <a href="{{ route('advertise.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                + New Ad Request
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    @if($requests->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="mx-auto w-24 h-24 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 7M4 11h16" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Ad Requests Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't submitted any ad requests yet.</p>
            <a href="{{ route('advertise.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                Submit Your First Ad
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($requests as $request)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col sm:flex-row hover:shadow-md transition-shadow">
                    <!-- Thumbnail -->
                    <div class="sm:w-48 h-48 sm:h-auto flex-shrink-0 bg-gray-100 dark:bg-gray-700 relative">
                        <img src="{{ $request->getImageUrl() }}" alt="{{ $request->ad_title }}" class="w-full h-full object-cover">
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-2 gap-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white line-clamp-2">{{ $request->ad_title }}</h3>
                            
                            <!-- Status Badge -->
                            <div class="shrink-0 mt-1">
                                @if($request->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800/50">
                                        Pending Review
                                    </span>
                                @elseif($request->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50">
                                        Approved ✓
                                    </span>
                                @elseif($request->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800/50">
                                        Rejected
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Submitted {{ $request->created_at->diffForHumans() }}
                        </div>

                        <!-- Spacer to push reason to bottom if needed -->
                        <div class="flex-1"></div>

                        <!-- Rejection Reason -->
                        @if($request->status === 'rejected' && $request->admin_notes)
                            <div class="mt-4 p-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30">
                                <p class="text-sm text-red-800 dark:text-red-400">
                                    <strong class="font-medium">Reason:</strong> {{ $request->admin_notes }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
