<?php

namespace Modules\Listing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['nullable', 'string', 'size:3'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'country' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['nullable', 'string', 'in:active,pending,sold'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Listing title is required.',
            'title.min' => 'Title must be at least 5 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category is invalid.',
            'price.numeric' => 'Price must be a valid number.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image must be less than 5MB.',
            'images.max' => 'Maximum 10 images allowed.',
        ];
    }
}
