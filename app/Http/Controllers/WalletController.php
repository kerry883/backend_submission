<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles API operations for Wallets.
 *
 * Provides endpoints to create wallets for a user,
 * list a user's wallets, and view a single wallet with transactions.
 *
 * @group Wallets
 */
class WalletController extends Controller
{
    /**
     * List all wallets for a user.
     *
     * Returns all wallets belonging to the specified user,
     * each with its computed balance.
     *
     * @group Wallets
     */
    public function index(User $user): AnonymousResourceCollection
    {
        $wallets = $user->wallets()->with('transactions')->get();

        return WalletResource::collection($wallets);
    }

    /**
     * Create a new wallet for a user.
     *
     * Creates a financial wallet/account under the specified user.
     * A user can have multiple wallets (e.g., Personal, Business).
     *
     * @group Wallets
     */
    public function store(StoreWalletRequest $request, User $user): JsonResponse
    {
        $wallet = $user->wallets()->create($request->validated());

        /** Reload with transactions so the resource can compute balance */
        $wallet->load('transactions');

        return (new WalletResource($wallet))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * View a single wallet.
     *
     * Returns the wallet details including its computed balance
     * and all associated transactions.
     *
     * @group Wallets
     */
    public function show(Wallet $wallet): WalletResource
    {
        $wallet->load('transactions');

        return new WalletResource($wallet);
    }
}
