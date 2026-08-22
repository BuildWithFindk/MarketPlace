@extends('app::layouts.app')

@section('title', 'Request an Advertisement')
@section('simple_page', '1')

@section('content')
<main class="mx-auto max-w-3xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-950">Advertise Here</h1>
        <p class="text-sm text-slate-500">Submit your banner ad request. We will review it shortly.</p>
    </div>

    <form method="POST" action="{{ route('ad-requests.store') }}" enctype="multipart/form-data" class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm md:p-6 space-y-5">
        @csrf

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Image <span class="text-rose-500">*</span></span>
            <input type="file" name="image" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100" accept="image/jpeg,image/png,image/webp">
            <p class="mt-1 text-xs text-slate-400">Recommended size: aspect ratio 16:9. Max 2MB.</p>
            @error('image') <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Title <span class="text-rose-500">*</span></span>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Your business or campaign name" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
            @error('title') <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Link URL</span>
            <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://yourwebsite.com" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200">
            @error('link_url') <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Message (Optional)</span>
            <textarea name="message" rows="4" placeholder="Any specific instructions or context for the admin?" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200">{{ old('message') }}</textarea>
            @error('message') <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
        </label>

        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-red-600 px-6 text-sm font-bold text-white transition hover:bg-red-700">
            Submit Request
        </button>
    </form>
</main>
@endsection
