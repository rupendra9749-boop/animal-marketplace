<?php

namespace App\Http\Requests;

use App\Support\Locations;
use App\Support\Phone;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** Validation shared by the public "list your clinic" form, the doctor panel and the admin vet form. */
class VetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::contact($this->input('phone')) ?? $this->input('phone'),
            'whatsapp' => Phone::contact($this->input('whatsapp')) ?? $this->input('whatsapp'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:70'],
            'services' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:3000'],
            'phone' => ['required', 'string', $this->phoneRule()],
            'whatsapp' => ['nullable', 'string', $this->phoneRule()],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'timings' => ['nullable', 'string', 'max:120'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /** A mobile number or a landline with its STD code. */
    private function phoneRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (Phone::contact((string) $value) === null) {
                $fail(__('Enter a valid Indian phone number.'));
            }
        };
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'categories.required' => __('Choose at least one type of animal you treat.'),
            'city.required' => __('Please pick the city from the list.'),
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $validator->errors()->hasAny(['state', 'city']) && ! Locations::find($this->input('state'), $this->input('city'))) {
                    $validator->errors()->add('city', __('Please pick the city from the list.'));
                }
            },
        ];
    }

    /**
     * The validated fields ready to store: booleans normalised and map coordinates filled in from the city.
     *
     * @return array<string, mixed>
     */
    public function vetData(): array
    {
        $data = collect($this->validated())->except(['categories', 'photo'])->all();
        $place = Locations::find($data['state'], $data['city']);

        $data['state'] = $place['state'];
        $data['city'] = $place['city'];
        $data['latitude'] = $place['lat'];
        $data['longitude'] = $place['lng'];
        $data['home_visit'] = $this->boolean('home_visit');
        $data['emergency'] = $this->boolean('emergency');

        return $data;
    }
}
