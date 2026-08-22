@extends('app::layouts.app')

@section('title', 'My Ad Requests')

@section('content')
<main class="mx-auto max-w-[1120px] px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-950">My Ad Requests</h1>
            <p class="text-sm text-slate-500">Track the status of your advertisement requests.</p>
        </div>
        <a href="{{ route('ad-requests.create') }}" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
            New Request
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($adRequests->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm">
            <p class="text-slate-500">You haven't submitted any ad requests yet.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50/50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Image</th>
                        <th class="px-6 py-4 font-semibold">Title / Link</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($adRequests as $request)
                        <tr>
                            <td class="px-6 py-4">
                                <img src="{{ $request->getImageUrl() }}" alt="Ad Image" class="h-16 w-24 rounded object-cover shadow-sm">
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $request->title }}</div>
                                @if($request->link_url)
                                    <a href="{{ $request->link_url }}" target="_blank" class="text-xs text-blue-600 hover:underline">{{ str($request->link_url)->limit(30) }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($request->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                                @elseif($request->status === 'approved')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Approved</span>
                                @elseif($request->status === 'rejected')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Rejected</span>
                                    @if($request->rejection_reason)
                                        <p class="mt-1 text-xs text-red-600">Reason: {{ $request->rejection_reason }}</p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $request->created_at->format('M j, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</main>
@endsection
