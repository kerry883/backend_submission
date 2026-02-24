<?php

namespace App\Http\Controllers;

use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles API operations for Wallets.
 *
 * Provides endpoints to create wallets for a user,
 * list a user's wallets, and view a single wallet with transactions.
 */
class WalletController extends Controller
{
    /**
     * List all wallets belonging to a specific user.
     *
     * Each wallet includes its computed balance.
     */
    public function index(User $user): AnonymousResourceCollection
    {
        $wallets = $user->wallets()->with('transactions')->get();

        return WalletResource::collection($wallets);
    }

    /**
     * Create a new wallet for a specific user.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $wallet = $user->wallets()->create($request->all());

        /** Reload with transactions so the resource can compute balance */
        $wallet->load('transactions');

        return (new WalletResource($wallet))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a single wallet with its balance and all transactions.
     */
    public function show(Wallet $wallet): WalletResource
    {
        $wallet->load('transactions');

        return new WalletResource($wallet);
    }
}
