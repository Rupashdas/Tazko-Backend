<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /**
     * No `exists:users,email` rule. The old app had one, and its message
     * ("Email address not found") told anyone which addresses had accounts.
     */
    public function rules(): array {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void {
        $this->merge(['email' => Str::lower(trim((string) $this->email))]);
    }
}
