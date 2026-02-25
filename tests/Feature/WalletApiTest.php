<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

/*
|--------------------------------------------------------------------------
| Wallet API Endpoint Tests
|--------------------------------------------------------------------------
|
| GET  /api/users/{user}/wallets  → index
| POST /api/users/{user}/wallets  → store
| GET  /api/wallets/{wallet}      → show
|
*/

describe('GET /api/users/{user}/wallets', function () {
    it('returns all wallets for a user', function () {
        $user = User::factory()->create();
        Wallet::factory()->count(3)->for($user)->create();

        $response = $this->getJson("/api/users/{$user->id}/wallets");

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'name', 'description', 'balance', 'transactions', 'created_at', 'updated_at'],
                ],
            ]);
    });

    it('returns an empty array when user has no wallets', function () {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}/wallets");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns 404 for a non-existent user', function () {
        $response = $this->getJson('/api/users/99999/wallets');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Resource not found.');
    });
});

describe('POST /api/users/{user}/wallets', function () {
    it('creates a wallet with valid data', function () {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/wallets", [
            'name' => 'Personal Savings',
            'description' => 'My savings account',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Personal Savings')
            ->assertJsonPath('data.description', 'My savings account')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.balance', '0.00');

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'name' => 'Personal Savings',
        ]);
    });

    it('creates a wallet without a description', function () {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/wallets", [
            'name' => 'Business Account',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Business Account')
            ->assertJsonPath('data.description', null);
    });

    it('returns 422 when name is missing', function () {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/wallets", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('returns 422 when name exceeds max length', function () {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/wallets", [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('returns 422 when description exceeds max length', function () {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/wallets", [
            'name' => 'Valid Name',
            'description' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    });

    it('returns 404 when creating wallet for non-existent user', function () {
        $response = $this->postJson('/api/users/99999/wallets', [
            'name' => 'Some Wallet',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Resource not found.');
    });
});

describe('GET /api/wallets/{wallet}', function () {
    it('returns a wallet with its transactions and balance', function () {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->income()->for($wallet)->create(['amount' => 1000.00]);
        Transaction::factory()->expense()->for($wallet)->create(['amount' => 250.00]);

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'name', 'description', 'balance', 'transactions', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.balance', '750.00');
    });

    it('returns a wallet with zero balance when it has no transactions', function () {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertOk()
            ->assertJsonPath('data.balance', '0.00')
            ->assertJsonPath('data.transactions', []);
    });

    it('returns 404 for a non-existent wallet', function () {
        $response = $this->getJson('/api/wallets/99999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Resource not found.');
    });
});
