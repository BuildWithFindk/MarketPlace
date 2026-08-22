<?php

namespace Modules\User\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^98\d{8}$/'],
            'country_code' => ['nullable', 'string'],
            'gender' => ['required', 'in:male,female,other'],
            'dob' => ['required', 'date', 'before:-18 years', 'after:1924-01-01'],
            'country' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'password' => ['required', Password::defaults()],
            'terms' => ['accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }

    public function fullName(): string
    {
        return trim($this->string('first_name')->toString().' '.$this->string('last_name')->toString());
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must start with 98 and be 10 digits long.',
            'dob.before' => 'You must be at least 18 years old to register.',
            'gender.required' => 'Please select your gender.',
            'gender.in' => 'Please select a valid gender option.',
        ];
    }
}
