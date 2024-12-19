<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::get();
    }

    public function show(Request $request, $user)
    {
        $page = $request->query('page', 1); 
        $itemsPerPage = $request->query('itemsPerPage', 10);
        $type = $request->query('type', null);

        $offset = ($page - 1) * $itemsPerPage;

        $query = Transaction::where('user_id', $user);

        if ($type) {
            $query->where('type', $type);
        }

        $totalTransactions = $query->count();

        $transactions = $query->orderBy('transaction_datetime', 'desc')
            ->skip($offset)
            ->take($itemsPerPage)
            ->get();

        return response()->json([
            'transactions' => $transactions,
            'total' => $totalTransactions,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'result_user' => $user,
        ]);
    }



    public function getTransactions(Request $request)
    {
        $userId = $request->user()->id;
        $page = $request->query('page', 1); 
        $itemsPerPage = $request->query('itemsPerPage', 10);
        $type = $request->query('type', null);

        $offset = ($page - 1) * $itemsPerPage;

        $query = Transaction::where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        $totalTransactions = $query->count();

        $transactions = $query->orderBy('transaction_datetime', 'desc')
            ->skip($offset)
            ->take($itemsPerPage)
            ->get();

        return response()->json([
            'transactions' => $transactions,
            'total' => $totalTransactions,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }


    public function store(StoreTransactionRequest $request)
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['user_id'] = $userId;
        $data['transaction_datetime'] = Carbon::parse($data['transaction_datetime'])->format('Y-m-d H:i:s');

        $transaction = Transaction::create($data);

        $request->user()->increment('brain_coins_balance', $data['brain_coins']);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data' => $transaction,
        ], 201);
    }


}
