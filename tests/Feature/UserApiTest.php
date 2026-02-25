<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

/*
|--------------------------------------------------------------------------
| User API Endpoint Tests
|--------------------------------------------------------------------------
|
| POST /api/users       → store
| GET  /api/users/{user} → show
|
*/

describe('POST /api/users', function () {
    it('creates a user with valid data', function () {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    it('returns 422 when name is missing', function () {
        $response = $this->postJson('/api/users', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('returns 422 when email is missing', function () {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('returns 422 when email is invalid', function () {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('returns 422 when email is already taken', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/users', [
            'name' => 'Another User',
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('returns 422 when body is empty', function () {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    });

    it('returns 422 when name exceeds max length', function () {
        $response = $this->postJson('/api/users', [
            'name' => str_repeat('a', 256),
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });
});

describe('GET /api/users/{user}', function () {
    it('returns a user with their wallets and total balance', function () {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->income()->for($wallet)->create(['amount' => 500.00]);
        Transaction::factory()->expense()->for($wallet)->create(['amount' => 150.00]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email',
                    'wallets' => [
                        '*' => ['id', 'user_id', 'name', 'description', 'balance', 'transactions'],
                    ],
                    'total_balance',
                    'created_at', 'updated_at',
                ],
            ])
            ->assertJsonPath('data.total_balance', '350.00');
    });

    it('returns a user with zero balance when they have no wallets', function () {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.total_balance', '0.00')
            ->assertJsonPath('data.wallets', []);
    });

    it('returns 404 for a non-existent user', function () {
        $response = $this->getJson('/api/users/99999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Resource not found.');
    });
});
