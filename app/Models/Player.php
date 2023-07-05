<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $table = 'player';

    protected $fillable = [
        'game_match_id',
        'name',
        'color',
        'money',
        'jumps',
        'cards',
        'on_question'
    ];

    public static function getActualPlayer($matchId)
    {
        return Player::where(['game_match_id'=>$matchId, 'loose'=>false, 'one_million'=>false])->orderBy('id')->get()->first();
    }
}
