<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateBoardRequest;

class BoardController extends Controller
{

    public function fetchBoards()
    {
        $boards = Board::select('id', 'board_cols', 'board_rows')->get();
        return response()->json($boards);
    }

    public function index()
    {
        return Board::get();
    }   
}
