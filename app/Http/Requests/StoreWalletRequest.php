<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Create a new wallet for a user.
 *
 * Creates a financial wallet/account that can hold transactions.
 * Each user can have multiple wallets (e.g., Personal, Business).
 *
 * @group Wallets
 */
class StoreWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * No authentication required.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A wallet name is required.',
            'name.max' => 'The wallet name must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 1000 characters.',
        ];
    }
}
