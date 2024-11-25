<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Game;
use App\Models\Board;
use Illuminate\Support\Facades\DB;

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
            ->selectRaw('YEAR(transaction_datetime) as year, MONTH(transaction_datetime) as month, COUNT(*) as total_purchases')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($purchasesPerMonth);
    }

}
