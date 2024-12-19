<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Game;
use App\Models\Board;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();

        $totalGames = Game::count();

        $mostPlayedBoard = Board::withCount('games')
            ->orderBy('games_count', 'desc')
            ->first();

        $totalPurchases = DB::table('transactions')->count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_games' => $totalGames,
            'most_played_board' => $mostPlayedBoard
                ? $mostPlayedBoard->board_cols . 'x' . $mostPlayedBoard->board_rows
                : 'N/A',
            'total_purchases' => $totalPurchases,
        ]);
    }

    public function gamesPerMonth()
    {
        $gamesPerMonth = DB::table('games')
            ->selectRaw('YEAR(began_at) as year, MONTH(began_at) as month, COUNT(*) as total_games')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($gamesPerMonth);
    }

    public function purchasesPerMonth()
    {
        $purchasesPerMonth = DB::table('transactions')
            ->where ('type', 'P')
            ->selectRaw('YEAR(transaction_datetime) as year, MONTH(transaction_datetime) as month, COUNT(*) as total_purchases')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($purchasesPerMonth);
    }

    public function gamesPerWeek()
    {
        $gamesPerWeek = DB::table('games')
            ->selectRaw('YEAR(began_at) as year, WEEKOFYEAR(began_at) as week, COUNT(*) as total_games')
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get();

        return response()->json($gamesPerWeek);
    }

    public function purchasesPerWeek()
    {
        $purchasesPerWeek = DB::table('transactions')
            ->where ('type', 'P')
            ->selectRaw('YEAR(transaction_datetime) as year, WEEKOFYEAR(transaction_datetime) as week, COUNT(*) as total_purchases')
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get();

        return response()->json($purchasesPerWeek);
    }

    public function purchasesByPlayer(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string',
        ]);

        $nickname = $request->input('nickname');

        $totalPurchases = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('users.nickname', $nickname)
            ->sum('transactions.euros');

        return response()->json(['total_purchases' => $totalPurchases]);
    }

    public function paymentTypes()
    {
        $paymentTypes = DB::table('transactions')
            ->where ('type', 'P')
            ->select('payment_type', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_type')
            ->orderByDesc('total')
            ->get();

        return response()->json($paymentTypes);
    }

    public function paymentValuesByMonth(){
        $paymentValues = DB::table('transactions')
            ->where ('type', 'P')
            ->selectRaw('YEAR(transaction_datetime) as year, MONTH(transaction_datetime) as month, SUM(euros) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($paymentValues);
    }


}
