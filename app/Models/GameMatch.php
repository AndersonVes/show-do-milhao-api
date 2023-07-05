<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'match';

    protected $fillable = [
        'trivia_token'
    ];

    public function player() {
        return $this->hasMany(Player::class);
    }

    public function question() {
        return $this->hasMany(Question::class);
    }

    public static function getToken($matchId)
    {
        return self::findOrFail($matchId)->trivia_token;
    }

    public function isFinished()
    {
        return self::player()
        ->where(function ($query) {
            $query->where('loose', true)
                  ->orWhere('one_million', true);
        })
        ->count() === self::player()->count();
    }

    public function getBoard()
    {
        return self::player()->select('id','name','money','color')->orderByDesc('money')->get();
    }

    
}
