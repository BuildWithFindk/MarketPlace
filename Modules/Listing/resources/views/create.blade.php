@extends('app::layouts.app')

@section('title', 'Post a Free Ad')
@section('simple_page', '1')

@php
    $formUser = $user ?? auth()->user();
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600';
    $inputErrorClass = 'w-full rounded-lg border border-red-400 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700';
    $errorClass = 'mt-1.5 text-xs font-medium text-red-600';
    $cardClass = 'rounded-lg border border-gray-200 bg-white p-5 md:p-6';
    $submitButtonClass = 'inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-red-600 px-6 text-sm font-bold text-white transition hover:bg-red-700';
    $selectedCategoryId = (string) old('category_id', '');
    $selectedDistrict = (string) old('district', '');
@endphp

@section('content')
<main class="mx-auto max-w-[1120px] px-4 py-8">
    <div class="mb-6 flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-slate-950">Post a Free Ad</h1>
        <p class="text-sm text-slate-500">Fill in the details to reach buyers across Nepal.</p>
    </div>

    <form
        method="POST"
        action="{{ route('listings.store') }}"
        enctype="multipart/form-data"
        id="create-listing-form"
    >
        @csrf

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
            {{-- LEFT COLUMN --}}
            <div class="space-y-5 lg:col-span-7">

                {{-- Basic Information --}}
                <section class="{{ $cardClass }}">
                    <h2 class="mb-4 text-base font-semibold text-slate-950">Basic Information</h2>
                    <div class="space-y-4">
                        <label class="block">
                            <span class="{{ $labelClass }}">Title <span class="text-rose-500">*</span></span>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="What are you selling?"
                                class="{{ $errors->has('title') ? $inputErrorClass : $inputClass }}"
                                required
                            >
                            @error('title')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="{{ $labelClass }}">Category <span class="text-rose-500">*</span></span>
                            <select
                                name="category_id"
                                id="category-select"
                                class="{{ $errors->has('category_id') ? $inputErrorClass : $inputClass }}"
                                required
                            >
                                <option value="">Choose a category</option>
                                @foreach($categories as $category)
                                    @if($category->children->isNotEmpty())
                                        <optgroup label="{{ $category->name }}">
                                            @foreach($category->children as $child)
                                                <option value="{{ $child->id }}" @selected($selectedCategoryId === (string) $child->id)>{{ $child->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @else
                                        <option value="{{ $category->id }}" @selected($selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="{{ $labelClass }}">Description</span>
                            <textarea
                                name="description"
                                rows="5"
                                maxlength="5000"
                                placeholder="Describe your item..."
                                class="{{ $errors->has('description') ? $inputErrorClass : $inputClass }}"
                                id="description-field"
                            >{{ old('description') }}</textarea>
                            <div class="mt-1.5 flex items-center justify-between">
                                @error('description')
                                    <span class="{{ $errorClass }}">{{ $message }}</span>
                                @enderror
                                <span
                                    id="description-counter"
                                    class="ml-auto text-xs text-slate-400"
                                >5000 characters remaining</span>
                            </div>
                        </label>
                    </div>
                </section>

                {{-- Pricing --}}
                <section class="{{ $cardClass }}">
                    <h2 class="mb-4 text-base font-semibold text-slate-950">Price</h2>
                    <div class="space-y-4">
                        <div>
                            <span class="{{ $labelClass }}">Price</span>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">NPR</span>
                                <input
                                    type="number"
                                    name="price"
                                    id="price-field"
                                    step="0.01"
                                    min="0"
                                    max="99999999"
                                    value="{{ old('price') }}"
                                    placeholder="0.00"
                                    class="{{ $errors->has('price') ? $inputErrorClass : $inputClass }} pl-14"
                                >
                            </div>
                            @error('price')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </div>

                        <label class="inline-flex cursor-pointer items-center gap-2.5">
                            <input
                                type="checkbox"
                                id="free-toggle"
                                class="h-4.5 w-4.5 rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            <span class="text-sm font-medium text-slate-700">This item is free</span>
                        </label>
                    </div>
                    <input type="hidden" name="currency" value="NPR">
                </section>

                {{-- Location --}}
                <section class="{{ $cardClass }}">
                    <h2 class="mb-4 text-base font-semibold text-slate-950">Location</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <label class="block">
                            <span class="{{ $labelClass }}">Country</span>
                            <input
                                type="text"
                                name="country"
                                value="Nepal"
                                readonly
                                class="{{ $inputClass }} cursor-not-allowed bg-slate-50"
                            >
                        </label>

                        <label class="block">
                            <span class="{{ $labelClass }}">District</span>
                            <select
                                name="district"
                                class="{{ $errors->has('district') ? $inputErrorClass : $inputClass }}"
                            >
                                <option value="">Select District</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district }}" @selected($selectedDistrict === $district)>{{ $district }}</option>
                                @endforeach
                            </select>
                            @error('district')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="{{ $labelClass }}">City</span>
                            <input
                                type="text"
                                name="city"
                                value="{{ old('city') }}"
                                placeholder="Enter your city"
                                class="{{ $errors->has('city') ? $inputErrorClass : $inputClass }}"
                            >
                            @error('city')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </section>

                {{-- Contact --}}
                <section class="{{ $cardClass }}">
                    <h2 class="mb-4 text-base font-semibold text-slate-950">Contact Information</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="{{ $labelClass }}">Phone</span>
                            <input
                                type="text"
                                name="contact_phone"
                                value="{{ old('contact_phone', $formUser?->phone) }}"
                                placeholder="+977 9XXXXXXXXX"
                                class="{{ $errors->has('contact_phone') ? $inputErrorClass : $inputClass }}"
                            >
                            @error('contact_phone')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="{{ $labelClass }}">Email</span>
                            <input
                                type="email"
                                name="contact_email"
                                value="{{ old('contact_email', $formUser?->email) }}"
                                class="{{ $errors->has('contact_email') ? $inputErrorClass : $inputClass }}"
                            >
                            <span class="mt-1 block text-xs text-slate-400">Buyers will use this to contact you</span>
                            @error('contact_email')
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </section>

                {{-- Submit (mobile: visible, desktop: hidden — desktop submit is below sidebar) --}}
                <div class="lg:hidden">
                    <button
                        type="submit"
                        class="{{ $submitButtonClass }}"
                    >
                        Post Your Ad Free
                    </button>
                </div>
            </div>

            {{-- RIGHT COLUMN (sidebar) --}}
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-24 space-y-5">
                    {{-- Photos --}}
                    <section class="{{ $cardClass }}">
                        <h2 class="text-base font-semibold text-slate-950">Photos</h2>
                        <p class="mt-1 text-xs text-slate-400">Add up to 10 photos (max 5MB each)</p>

                        <div
                            id="drop-zone"
                            class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center transition hover:border-gray-400 hover:bg-gray-100"
                        >
                            <svg class="mb-3 h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16V4m0 0L8 8m4-4l4 4M4 14v4a2 2 0 002 2h12a2 2 0 002-2v-4"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-600">Drag & drop your photos here</p>
                            <p class="mt-1 text-xs text-slate-400">or click to browse</p>
                            <input
                                type="file"
                                name="images[]"
                                id="image-input"
                                multiple
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                            >
                        </div>

                        <p id="image-count" class="mt-3 hidden text-xs font-semibold text-slate-600"></p>

                        @error('images')
                            <span class="{{ $errorClass }}">{{ $message }}</span>
                        @enderror
                        @foreach($errors->get('images.*') as $imageErrors)
                            @foreach($imageErrors as $message)
                                <span class="{{ $errorClass }}">{{ $message }}</span>
                            @endforeach
                        @endforeach

                        <div id="preview-grid" class="mt-4 grid grid-cols-3 gap-2.5 empty:hidden"></div>
                    </section>

                    {{-- Submit (desktop only) --}}
                    <div class="hidden lg:block">
                        <button
                            type="submit"
                            class="{{ $submitButtonClass }}"
                        >
                            Post Your Ad Free
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const descField = document.getElementById('description-field');
        const counter = document.getElementById('description-counter');
        const maxChars = 5000;

        const updateCounter = () => {
            const remaining = maxChars - descField.value.length;
            counter.textContent = remaining + ' characters remaining';

            if (remaining < 100) {
                counter.classList.add('text-rose-500');
                counter.classList.remove('text-slate-400');
            } else {
                counter.classList.remove('text-rose-500');
                counter.classList.add('text-slate-400');
            }
        };

        descField.addEventListener('input', updateCounter);
        updateCounter();

        const freeToggle = document.getElementById('free-toggle');
        const priceField = document.getElementById('price-field');

        freeToggle.addEventListener('change', () => {
            if (freeToggle.checked) {
                priceField.dataset.prevPrice = priceField.value;
                priceField.value = '0';
                priceField.disabled = true;
                priceField.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                priceField.value = priceField.dataset.prevPrice || '';
                priceField.disabled = false;
                priceField.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });

        const dropZone = document.getElementById('drop-zone');
        const imageInput = document.getElementById('image-input');
        const previewGrid = document.getElementById('preview-grid');
        const imageCount = document.getElementById('image-count');
        let selectedFiles = [];

        const formatSize = (bytes) => {
            if (bytes < 1024) {
                return bytes + ' B';
            }

            if (bytes < 1048576) {
                return (bytes / 1024).toFixed(1) + ' KB';
            }

            return (bytes / 1048576).toFixed(1) + ' MB';
        };

        const syncFileInput = () => {
            const dt = new DataTransfer();
            selectedFiles.forEach((f) => dt.items.add(f));
            imageInput.files = dt.files;

            if (selectedFiles.length > 0) {
                imageCount.textContent = selectedFiles.length + ' photo' + (selectedFiles.length !== 1 ? 's' : '') + ' selected';
                imageCount.classList.remove('hidden');
            } else {
                imageCount.classList.add('hidden');
            }
        };

        const renderPreviews = () => {
            previewGrid.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const card = document.createElement('div');
                card.className = 'relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100';

                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'h-24 w-full object-cover';
                img.onload = () => URL.revokeObjectURL(img.src);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'absolute right-1 top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/80';
                removeBtn.innerHTML = '<svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                removeBtn.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    syncFileInput();
                    renderPreviews();
                });

                const info = document.createElement('div');
                info.className = 'px-1.5 py-1';
                info.innerHTML =
                    '<p class="truncate text-[10px] font-medium text-slate-600">' + file.name + '</p>' +
                    '<p class="text-[10px] text-slate-400">' + formatSize(file.size) + '</p>';

                card.appendChild(img);
                card.appendChild(removeBtn);
                card.appendChild(info);
                previewGrid.appendChild(card);
            });
        };

        const addFiles = (files) => {
            const remaining = 10 - selectedFiles.length;
            const toAdd = Array.from(files).slice(0, remaining);
            selectedFiles = selectedFiles.concat(toAdd);
            syncFileInput();
            renderPreviews();
        };

        dropZone.addEventListener('click', () => imageInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-gray-400', 'bg-gray-100');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-gray-400', 'bg-gray-100');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-gray-400', 'bg-gray-100');
            if (e.dataTransfer.files.length) {
                addFiles(e.dataTransfer.files);
            }
        });

        imageInput.addEventListener('change', () => {
            if (imageInput.files.length) {
                addFiles(imageInput.files);
            }
        });
    });
</script>
@endsection
