<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Wallet model into a JSON-ready array.
 *
 * Includes the computed balance (income minus expenses) and optionally all transactions.
 */
class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'balance' => $this->when(
                $this->relationLoaded('transactions'),
                fn () => $this->calculateBalance(),
                '0.00'
            ),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Calculate the wallet balance from its transactions.
     *
     * Balance = sum(income) - sum(expense)
     */
    private function calculateBalance(): string
    {
        $income = $this->transactions
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $expense = $this->transactions
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        return number_format($income - $expense, 2, '.', '');
    }
}
