<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles API operations for User accounts.
 *
 * Provides endpoints to create users and view user profiles
 * with wallet balances.
 */
class UserController extends Controller
{
    /**
     * Create a new user account.
     *
     * No authentication is required for user creation.
     */
    public function store(Request $request): JsonResponse
    {
        $user = User::query()->create($request->all());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a user's profile.
     *
     * Includes all wallets with their individual balances
     * and the user's total balance across all wallets.
     */
    public function show(User $user): UserResource
    {
        $user->load('wallets.transactions');

        return new UserResource($user);
    }
}
