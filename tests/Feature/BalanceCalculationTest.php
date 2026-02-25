<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

/*
|--------------------------------------------------------------------------
| Balance Calculation Tests
|--------------------------------------------------------------------------
|
| Verifies that wallet balances and user total balances
| are computed correctly from income and expense transactions.
|
*/

describe('wallet balance', function () {
    it('calculates balance as income minus expenses', function () {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->income()->for($wallet)->create(['amount' => 1000.00]);
        Transaction::factory()->income()->for($wallet)->create(['amount' => 500.00]);
        Transaction::factory()->expense()->for($wallet)->create(['amount' => 300.00]);

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertOk()
            ->assertJsonPath('data.balance', '1200.00');
    });

    it('returns zero balance when no transactions exist', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertOk()
            ->assertJsonPath('data.balance', '0.00');
    });

    it('returns negative balance when expenses exceed income', function () {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->income()->for($wallet)->create(['amount' => 100.00]);
        Transaction::factory()->expense()->for($wallet)->create(['amount' => 250.00]);

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertOk()
            ->assertJsonPath('data.balance', '-150.00');
    });

    it('handles decimal precision correctly', function () {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->income()->for($wallet)->create(['amount' => 100.10]);
        Transaction::factory()->income()->for($wallet)->create(['amount' => 200.25]);
        Transaction::factory()->expense()->for($wallet)->create(['amount' => 50.30]);

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertOk()
            ->assertJsonPath('data.balance', '250.05');
    });

    it('updates balance after adding a new transaction', function () {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->income()->for($wallet)->create(['amount' => 500.00]);

        $this->getJson("/api/wallets/{$wallet->id}")
            ->assertJsonPath('data.balance', '500.00');

        $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'expense',
            'amount' => 200.00,
        ])->assertStatus(201);

        $this->getJson("/api/wallets/{$wallet->id}")
            ->assertJsonPath('data.balance', '300.00');
    });
});

describe('user total balance', function () {
    it('calculates total balance across multiple wallets', function () {
        $user = User::factory()->create();

        $wallet1 = Wallet::factory()->for($user)->create();
        Transaction::factory()->income()->for($wallet1)->create(['amount' => 1000.00]);
        Transaction::factory()->expense()->for($wallet1)->create(['amount' => 200.00]);

        $wallet2 = Wallet::factory()->for($user)->create();
        Transaction::factory()->income()->for($wallet2)->create(['amount' => 500.00]);
        Transaction::factory()->expense()->for($wallet2)->create(['amount' => 100.00]);

        $response = $this->getJson("/api/users/{$user->id}");

        // Wallet 1: 1000 - 200 = 800 | Wallet 2: 500 - 100 = 400 | Total: 1200
        $response->assertOk()
            ->assertJsonPath('data.total_balance', '1200.00');
    });

    it('returns zero total balance when user has no wallets', function () {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.total_balance', '0.00');
    });

    it('returns zero total balance when wallets have no transactions', function () {
        $user = User::factory()->create();
        Wallet::factory()->count(2)->for($user)->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.total_balance', '0.00');
    });

    it('handles mixed positive and negative wallet balances', function () {
        $user = User::factory()->create();

        $wallet1 = Wallet::factory()->for($user)->create();
        Transaction::factory()->income()->for($wallet1)->create(['amount' => 500.00]);

        $wallet2 = Wallet::factory()->for($user)->create();
        Transaction::factory()->expense()->for($wallet2)->create(['amount' => 300.00]);

        $response = $this->getJson("/api/users/{$user->id}");

        // Wallet 1: +500 | Wallet 2: -300 | Total: 200
        $response->assertOk()
            ->assertJsonPath('data.total_balance', '200.00');
    });

    it('reflects individual wallet balances correctly in user response', function () {
        $user = User::factory()->create();

        $wallet = Wallet::factory()->for($user)->create(['name' => 'Main']);
        Transaction::factory()->income()->for($wallet)->create(['amount' => 750.00]);
        Transaction::factory()->expense()->for($wallet)->create(['amount' => 250.00]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.wallets.0.balance', '500.00')
            ->assertJsonPath('data.total_balance', '500.00');
    });
});
