<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Player;
use App\Services\TryviaService;
use Illuminate\Http\Request;

class GameMatchController extends Controller
{
    public function startGame(Request $request)
    {
        if ($request->players !== null && is_array($request->players) && !empty($request->players)) {
            if(sizeof($request->players) > 3)
                return response()->json(['error' => '3 players max.'], 400);
            foreach ($request->players as $item) {
                if (!is_array($item) || !isset($item['color']) || !isset($item['name'])) {
                    return response()->json(['error' => 'Invalid object format'], 400);
                }
            }
        } else {
            return response()->json(['error' => 'Invalid parameter'], 400);
        }

        

        $token = TryviaService::getNewToken();

        $match = new GameMatch();
        $match->trivia_token = $token;
        $match->save();

        $players = $request->players;
        $matchId = $match->id;
        
        foreach ($players as &$player) {
            $player['game_match_id'] = $matchId;
            $player['created_at'] = now();
            $player['updated_at'] = now();
        }
        Player::insert($players);

        return response()->json(['matchId' => $matchId]);
    }
}
