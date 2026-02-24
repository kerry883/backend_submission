<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Create an income transaction.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => TransactionType::Income,
        ]);
    }

    /**
     * Create an expense transaction.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => TransactionType::Expense,
        ]);
    }
}
