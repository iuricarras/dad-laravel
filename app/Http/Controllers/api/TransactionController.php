<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::get();
    }

    public function show(Transaction $transaction)
    {
        return $transaction;
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();
        $data['transaction_datetime'] = Carbon::parse($data['transaction_datetime'])->format('Y-m-d H:i:s');
        $transaction = Transaction::create($data);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data' => $transaction,
        ], 201);
    }


}
