<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'question';

    protected $fillable = [
        'difficulty',
        'answered',
        'game_match_id',
        'question'
    ];

    public static function deleteByMatch($matchId)
    {
        self::where('game_match_id', $matchId)->delete();
    }
}
