<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'package_id' => ['required', 'integer', Rule::exists('packages', 'id')],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after:departure_date'],
            'departure_airport' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'open', 'full', 'closed', 'completed', 'cancelled'])],
        ];
    }
}
