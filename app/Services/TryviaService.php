<?php

namespace App\Services;

use App\Models\GameMatch;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class TryviaService
{

    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function getQuestion($matchId,$difficulty, $amount)
    {
        $token = GameMatch::getToken($matchId);

        $response = $this->httpClient
        ->get(config('app.appVar.tryviaApiUrl')."/api.php?amount=$amount&type=multiple&difficulty=$difficulty&token=$token");

        return json_decode($response->getBody(), true);
    }

    static public function getNewToken()
    {
        $response = Http::get(config('app.appVar.tryviaApiUrl').'/api_token.php?command=request');

        return $response['token'];
    }
}
