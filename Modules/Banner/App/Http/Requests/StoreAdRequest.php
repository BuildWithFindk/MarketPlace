<?php

namespace Modules\Banner\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ad_title' => ['required', 'string', 'max:150'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'desired_link_type' => ['required', 'in:category,listing,external,none'],
            'desired_link_value' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ad_title.required' => 'An ad title is required.',
            'image.required' => 'Please upload an image for your ad.',
            'image.image' => 'The file must be a valid image.',
            'image.max' => 'The image must not exceed 5MB.',
            'desired_link_type.in' => 'Please select a valid desired link type.',
        ];
    }
}
