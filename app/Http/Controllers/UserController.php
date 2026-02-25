<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Handles API operations for User accounts.
 *
 * Provides endpoints to create users and view user profiles
 * with wallet balances.
 *
 * @group Users
 */
class UserController extends Controller
{
    /**
     * Create a new user account.
     *
     * Registers a new user with name and email.
     * No authentication is required for user creation.
     *
     * @group Users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::query()->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a user's profile.
     *
     * Returns the user along with all their wallets,
     * each wallet's balance, and the total balance across all wallets.
     *
     * @group Users
     */
    public function show(User $user): UserResource
    {
        $user->load('wallets.transactions');

        return new UserResource($user);
    }
}
