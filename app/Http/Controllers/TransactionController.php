<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles API operations for Transactions.
 *
 * Provides endpoints to add transactions (income/expense)
 * to a wallet and list all transactions for a wallet.
 *
 * @group Transactions
 */
class TransactionController extends Controller
{
    /**
     * List transactions for a wallet.
     *
     * Returns all transactions for the specified wallet
     * in reverse chronological order (newest first).
     *
     * @group Transactions
     */
    public function index(Wallet $wallet): AnonymousResourceCollection
    {
        $transactions = $wallet->transactions()
            ->latest()
            ->get();

        return TransactionResource::collection($transactions);
    }

    /**
     * Add a transaction to a wallet.
     *
     * Records an income or expense transaction.
     * Income adds to the wallet balance, expense subtracts from it.
     * The amount must be a positive number.
     *
     * @group Transactions
     */
    public function store(StoreTransactionRequest $request, Wallet $wallet): JsonResponse
    {
        $transaction = $wallet->transactions()->create($request->validated());

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }
}
