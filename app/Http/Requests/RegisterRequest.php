<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Locations;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Store every phone in one format, and check duplicates against that format.
        $this->merge(['phone' => Phone::mobile($this->input('phone')) ?? $this->input('phone')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'regex:/^\+91 [6-9]\d{4} \d{5}$/', 'unique:'.User::class],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'account_type' => ['nullable', 'in:buyer,seller'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid 10-digit Indian mobile number.'),
            'phone.unique' => __('This phone number is already registered.'),
            'city.required' => __('Please pick your city from the list.'),
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $validator->errors()->hasAny(['state', 'city']) && ! Locations::find($this->input('state'), $this->input('city'))) {
                    $validator->errors()->add('city', __('Please pick your city from the list.'));
                }
            },
        ];
    }
}
