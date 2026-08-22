@php
    $currentListing = $listing ?? null;
    $formUser = $user ?? auth()->user();
    $inputClass = 'w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200';
    $labelClass = 'mb-2 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-2 text-sm text-rose-600';
    $selectedCategoryId = (string) old('category_id', $currentListing?->category_id ?? '');
    $selectedDistrict = (string) old('district', $currentListing?->district ?? '');
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <label class="block md:col-span-2">
        <span class="{{ $labelClass }}">Title</span>
        <input type="text" name="title" value="{{ old('title', $currentListing?->title) }}" class="{{ $inputClass }}" required>
        @error('title')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block md:col-span-2">
        <span class="{{ $labelClass }}">Description</span>
        <textarea name="description" rows="7" class="{{ $inputClass }}">{{ old('description', $currentListing?->description) }}</textarea>
        @error('description')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="{{ $labelClass }}">Category</span>
        <select name="category_id" class="{{ $inputClass }}" required>
            <option value="">Choose a category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected($selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
                @foreach($category->children as $child)
                    <option value="{{ $child->id }}" @selected($selectedCategoryId === (string) $child->id)>-- {{ $child->name }}</option>
                @endforeach
            @endforeach
        </select>
        @error('category_id')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="{{ $labelClass }}">Price</span>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $currentListing?->price) }}" class="{{ $inputClass }}">
        @error('price')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="{{ $labelClass }}">Currency</span>
        <input type="text" name="currency" maxlength="3" value="{{ old('currency', $currentListing?->currency ?? 'NPR') }}" class="{{ $inputClass }}">
        @error('currency')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="{{ $labelClass }}">District</span>
        <select name="district" class="{{ $inputClass }}">
            <option value="">Choose a district</option>
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
        <input type="text" name="city" value="{{ old('city', $currentListing?->city) }}" class="{{ $inputClass }}">
        @error('city')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="{{ $labelClass }}">Phone</span>
        <input type="text" name="contact_phone" value="{{ old('contact_phone', $currentListing?->contact_phone ?? $formUser?->phone) }}" class="{{ $inputClass }}">
        @error('contact_phone')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="{{ $labelClass }}">Email</span>
        <input type="email" name="contact_email" value="{{ old('contact_email', $currentListing?->contact_email ?? $formUser?->email) }}" class="{{ $inputClass }}">
        @error('contact_email')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
    </label>

    <label class="block md:col-span-2">
        <span class="{{ $labelClass }}">Images</span>
        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="{{ $inputClass }}">
        @error('images')
            <span class="{{ $errorClass }}">{{ $message }}</span>
        @enderror
        @foreach($errors->get('images.*') as $imageErrors)
            @foreach($imageErrors as $message)
                <span class="{{ $errorClass }}">{{ $message }}</span>
            @endforeach
        @endforeach
    </label>
</div>
