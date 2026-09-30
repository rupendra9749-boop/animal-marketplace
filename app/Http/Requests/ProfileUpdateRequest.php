<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Locations;
use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => Phone::mobile($this->input('phone')) ?? $this->input('phone')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['required', 'string', 'regex:/^\+91 [6-9]\d{4} \d{5}$/', Rule::unique(User::class)->ignore($this->user()->id)],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
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
