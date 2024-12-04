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
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['user_id'] = $userId;
        $data['transaction_datetime'] = Carbon::parse($data['transaction_datetime'])->format('Y-m-d H:i:s'); // Converte para o formato MySQL

        // Criação da transação
        $transaction = Transaction::create($data);
        //decremente os brain_coins do usuário

        $request->user()->increment('brain_coins_balance', $data['brain_coins']);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data' => $transaction,
        ], 201);
    }


}
