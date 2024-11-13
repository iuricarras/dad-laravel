<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

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

    public function store(StoreUpdateTransactionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $transaction = Transaction::create($data);
        return $transaction;
    }

}
