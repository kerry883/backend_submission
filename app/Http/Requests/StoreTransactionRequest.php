<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add a transaction to a wallet.
 *
 * Records an income or expense transaction against a specific wallet.
 * Income transactions add to the wallet balance, expenses subtract from it.
 * The amount must always be a positive number.
 *
 * @group Transactions
 */
class StoreTransactionRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::enum(TransactionType::class)],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
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
            'type.required' => 'A transaction type is required.',
            'type.Illuminate\Validation\Rules\Enum' => 'The transaction type must be either "income" or "expense".',
            'amount.required' => 'A transaction amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.gt' => 'The amount must be greater than zero.',
            'amount.max' => 'The amount exceeds the maximum allowed value.',
            'description.max' => 'The description must not exceed 255 characters.',
        ];
    }
}
