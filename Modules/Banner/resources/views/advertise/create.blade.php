@extends('site::layouts.app')

@section('title', 'Advertise on HajurKoAfno')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-900 rounded-lg border border-gray-200 overflow-hidden">
        <div class="p-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Advertise with Us</h1>
            <p class="text-gray-600 dark:text-gray-400 mb-8 text-lg">
                Want your business or listing featured on our homepage? Submit your ad below and our team will review it within 24 hours.
            </p>

            <form action="{{ route('advertise.store') }}" method="POST" enctype="multipart/form-data" 
                  x-data="adForm()" class="space-y-6">
                @csrf

                <!-- Ad Title -->
                <div>
                    <label for="ad_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ad Title <span class="text-red-500">*</span></label>
                    <input type="text" name="ad_title" id="ad_title" required value="{{ old('ad_title') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-colors">
                    @error('ad_title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ad Image -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ad Image <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Recommended size: 1200x675px, max 5MB</p>
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md dark:border-gray-600 transition-colors">
                        <div class="space-y-1 text-center">
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="mx-auto max-h-48 object-contain mb-4 rounded shadow-sm" />
                            </template>
                            <template x-if="!imageUrl">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </template>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <label for="image" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500 transition-colors">
                                    <span>Upload a file</span>
                                    <input id="image" name="image" type="file" accept="image/*" class="sr-only" required @change="fileChosen">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WEBP up to 5MB</p>
                        </div>
                    </div>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Desired Link Type -->
                <div>
                    <label for="desired_link_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Where should it link to? <span class="text-red-500">*</span></label>
                    <select id="desired_link_type" name="desired_link_type" x-model="linkType" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-colors">
                        <option value="none">No link</option>
                        <option value="category">Category</option>
                        <option value="listing">My Listing</option>
                        <option value="external">External Website</option>
                    </select>
                    @error('desired_link_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Conditional Link Value Fields -->
                <div x-show="linkType === 'category'" style="display: none;" x-transition>
                    <label for="category_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Category <span class="text-red-500">*</span></label>
                    <select id="category_link" name="desired_link_value" x-bind:required="linkType === 'category'" x-bind:disabled="linkType !== 'category'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-colors">
                        <option value="">Select a category...</option>
                        @isset($categories)
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" @if(old('desired_link_value') == $category->slug) selected @endif>{{ $category->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div x-show="linkType === 'listing'" style="display: none;" x-transition>
                    <label for="listing_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select My Listing <span class="text-red-500">*</span></label>
                    <select id="listing_link" name="desired_link_value" x-bind:required="linkType === 'listing'" x-bind:disabled="linkType !== 'listing'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-colors">
                        <option value="">Select a listing...</option>
                        @isset($listings)
                            @foreach($listings as $listing)
                                <option value="{{ $listing->slug }}" @if(old('desired_link_value') == $listing->slug) selected @endif>{{ $listing->title }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div x-show="linkType === 'external'" style="display: none;" x-transition>
                    <label for="external_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">External URL <span class="text-red-500">*</span></label>
                    <input type="url" id="external_link" name="desired_link_value" placeholder="https://example.com"
                           x-bind:required="linkType === 'external'" x-bind:disabled="linkType !== 'external'" value="{{ old('desired_link_value') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-colors">
                </div>

                @error('desired_link_value')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <!-- Message to Admin -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional message to admin (Optional)</label>
                    <textarea id="message" name="message" rows="4" placeholder="Any specific requirements or notes?"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm transition-colors">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Submit for Review
                    </button>
                </div>
            </form>
            
            <div class="mt-8 text-center border-t border-gray-200 dark:border-gray-700 pt-6">
                <a href="{{ route('advertise.mine') }}" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 transition-colors inline-flex items-center gap-1">
                    View my submitted requests 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adForm', () => ({
            linkType: '{{ old('desired_link_type', 'none') }}',
            imageUrl: null,
            fileChosen(event) {
                this.fileToDataUrl(event, src => this.imageUrl = src)
            },
            fileToDataUrl(event, callback) {
                if (!event.target.files.length) return
                
                let file = event.target.files[0],
                    reader = new FileReader()

                reader.readAsDataURL(file)
                reader.onload = e => callback(e.target.result)
            }
        }))
    })
</script>
@endsection
