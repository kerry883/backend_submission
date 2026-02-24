<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles API operations for Transactions.
 *
 * Provides endpoints to add transactions (income/expense)
 * to a wallet and list all transactions for a wallet.
 */
class TransactionController extends Controller
{
    /**
     * List all transactions for a specific wallet.
     *
     * Returns transactions in reverse chronological order (newest first).
     */
    public function index(Wallet $wallet): AnonymousResourceCollection
    {
        $transactions = $wallet->transactions()
            ->latest()
            ->get();

        return TransactionResource::collection($transactions);
    }

    /**
     * Add a new transaction (income or expense) to a wallet.
     */
    public function store(Request $request, Wallet $wallet): JsonResponse
    {
        $transaction = $wallet->transactions()->create($request->all());

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }
}
