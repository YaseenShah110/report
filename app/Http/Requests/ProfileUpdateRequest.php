<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Profile Update Request
 * 
 * Validates profile update form data.
 * Ensures unique email (except for current user).
 * Password validation uses Laravel's default password rules.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Only validate password if it was actually provided
        if (!$this->filled('password')) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}