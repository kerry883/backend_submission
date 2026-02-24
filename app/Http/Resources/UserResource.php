<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a User model into a JSON-ready array.
 *
 * Includes all wallets with individual balances and the total balance across all wallets.
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'wallets' => WalletResource::collection($this->whenLoaded('wallets')),
            'total_balance' => $this->when(
                $this->relationLoaded('wallets'),
                fn () => $this->calculateTotalBalance()
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Calculate the total balance across all of the user's wallets.
     *
     * Income adds to balance, expense subtracts from balance.
     */
    private function calculateTotalBalance(): string
    {
        $totalBalance = $this->wallets->sum(function ($wallet) {
            $income = $wallet->transactions
                ->where('type', TransactionType::Income)
                ->sum('amount');

            $expense = $wallet->transactions
                ->where('type', TransactionType::Expense)
                ->sum('amount');

            return $income - $expense;
        });

        return number_format($totalBalance, 2, '.', '');
    }
}
