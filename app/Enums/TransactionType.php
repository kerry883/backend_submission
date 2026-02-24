<?php

namespace App\Enums;

/**
 * Represents the type of a financial transaction.
 *
 * Income transactions add to the wallet balance.
 * Expense transactions subtract from the wallet balance.
 */
enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
