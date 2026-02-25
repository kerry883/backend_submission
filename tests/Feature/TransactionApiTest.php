<?php

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;

/*
|--------------------------------------------------------------------------
| Transaction API Endpoint Tests
|--------------------------------------------------------------------------
|
| GET  /api/wallets/{wallet}/transactions → index
| POST /api/wallets/{wallet}/transactions → store
|
*/

describe('GET /api/wallets/{wallet}/transactions', function () {
    it('returns all transactions for a wallet', function () {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->count(5)->for($wallet)->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'wallet_id', 'type', 'amount', 'description', 'created_at', 'updated_at'],
                ],
            ]);
    });

    it('returns transactions in reverse chronological order', function () {
        $wallet = Wallet::factory()->create();
        $oldest = Transaction::factory()->for($wallet)->create(['created_at' => now()->subDays(2)]);
        $newest = Transaction::factory()->for($wallet)->create(['created_at' => now()]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids[0])->toBe($newest->id)
            ->and($ids[1])->toBe($oldest->id);
    });

    it('returns an empty array when wallet has no transactions', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns 404 for a non-existent wallet', function () {
        $response = $this->getJson('/api/wallets/99999/transactions');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Resource not found.');
    });
});

describe('POST /api/wallets/{wallet}/transactions', function () {
    it('creates an income transaction', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'income',
            'amount' => 1500.50,
            'description' => 'Salary',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.amount', '1500.50')
            ->assertJsonPath('data.description', 'Salary')
            ->assertJsonPath('data.wallet_id', $wallet->id);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'type' => TransactionType::Income->value,
            'amount' => 1500.50,
        ]);
    });

    it('creates an expense transaction', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'expense',
            'amount' => 75.25,
            'description' => 'Groceries',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'expense')
            ->assertJsonPath('data.amount', '75.25');
    });

    it('creates a transaction without a description', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'income',
            'amount' => 100.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.description', null);
    });

    it('returns 422 when type is missing', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'amount' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    it('returns 422 when type is invalid', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'transfer',
            'amount' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    it('returns 422 when amount is missing', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'income',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    it('returns 422 when amount is zero', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'income',
            'amount' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    it('returns 422 when amount is negative', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'expense',
            'amount' => -50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    it('returns 422 when amount is not numeric', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", [
            'type' => 'income',
            'amount' => 'abc',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    it('returns 422 when body is empty', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/transactions", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'amount']);
    });

    it('returns 404 when creating transaction for non-existent wallet', function () {
        $response = $this->postJson('/api/wallets/99999/transactions', [
            'type' => 'income',
            'amount' => 100,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Resource not found.');
    });
});
