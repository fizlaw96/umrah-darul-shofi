<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class UpdateImageFocusRequest extends FormRequest
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
            'path' => ['required', 'string', 'max:255'],
            'position_x' => ['required', 'integer', 'between:0,100'],
            'position_y' => ['required', 'integer', 'between:0,100'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('path')) {
                    return;
                }

                $path = $this->string('path')->toString();

                if (! Storage::disk('public')->exists($path)) {
                    $validator->errors()->add('path', 'Gambar yang dipilih tidak lagi ditemui dalam storan media.');
                }
            },
        ];
    }
}
