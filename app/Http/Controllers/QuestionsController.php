<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Question;
use App\Services\TryviaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionsController extends Controller
{
    public function showQuestion($matchId)
    {
        $this->createNewQuestions($matchId);

        $gameMatch = GameMatch::find($matchId);

        $end = $gameMatch->isFinished();
        $board = [];
        $question = [];
        $actualPlayer = [];
        $rewards = [];

        if ($end) {
            $board = $gameMatch->getBoard();
            Question::deleteByMatch($matchId);
        } else {
            $actualPlayer = Player::getActualPlayer($matchId);

            $onQuestion = $actualPlayer->on_question;

            $question = $this->questionFormatted($matchId, $onQuestion);
            $rewards = [
                'miss' => $actualPlayer['money'] == 0 ? 0 : round($actualPlayer['money'] / 2),
                'stop' => $actualPlayer['money'],
                'score' => nextPrize($onQuestion)
            ];
        }

        unset($actualPlayer['game_match_id'], $actualPlayer['on_question'], $actualPlayer['created_at'], $actualPlayer['updated_at'], $actualPlayer['loose'], $actualPlayer['one_million']);

        $questionRes = [
            'end' => $end,

            'player' => $actualPlayer,

            'rewards' => $rewards,

            'question' => $question,

            'board' => $board,
        ];

        return response()->json($questionRes);
    }

    private function questionFormatted($matchId, $onQuestion)
    {
        $match = GameMatch::find($matchId);

        if ($match->actual_question == 0) {
            $question = Question::where(['answered' => 0, 'game_match_id' => $matchId, 'difficulty' => getDifficulty($onQuestion)])->get()->first();
            $match->actual_question = $question->id;
            $match->save();
        } else $question = Question::find($match->actual_question);

        $question['answers'] = json_decode($question['answers']);
        if ($question->remove_answers > 0) {
            $question['answers'] = $this->removeItemsFromArray($question['answers'], $question['correct_answer'], $question->remove_answers);
        }


        unset($question['created_at'], $question['updated_at'], $question['answered'], $question['correct_answer']);

        return $question;
    }

    private function removeItemsFromArray($array, $correct, $count): array
    {
        $key = array_search($correct, $array);

        if ($key !== false) {
            unset($array[$key]);
        }

        $keysToRemove = array_rand($array, $count);
        if (!is_array($keysToRemove)) {
            $keysToRemove = [$keysToRemove];
        }

        $array = array_diff_key($array, array_flip($keysToRemove));

        array_push($array, $correct);

        return array_values($array);
    }

    private function createNewQuestions($matchId)
    {
        $questions = Question::where('game_match_id', $matchId);

        if ($questions->count() > 0)
            return;

        $playersCount = Player::where('game_match_id', $matchId)->count();

        $tryviaService = new TryviaService;
        $easyQuestions = $tryviaService->getQuestion($matchId, 'easy', 8 * $playersCount)['results'];
        $mediumQuestions = $tryviaService->getQuestion($matchId, 'medium', 8 * $playersCount)['results'];
        $hardQuestions = $tryviaService->getQuestion($matchId, 'hard', 8 * $playersCount)['results'];

        $allQuestions = array_merge($easyQuestions, $mediumQuestions, $hardQuestions);

        Log::info(json_encode($allQuestions));

        $filteredQuestions = array_map(function ($object) use ($matchId) {
            $incorrectAnswers = $object['incorrect_answers'];
            $correctAnswer = $object['correct_answer'];
            $answers = array_merge([$correctAnswer], $incorrectAnswers);
            sort($answers);

            return [
                'question' => $object['question'],
                'difficulty' => $object['difficulty'],
                'game_match_id' => $matchId,
                'answers' => json_encode($answers),
                'correct_answer' => $correctAnswer,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $allQuestions);

        Log::info(json_encode($filteredQuestions));

        Question::insert($filteredQuestions);
    }

    public function answerQuestion(Request $request, $questionId)
    {
        $question = Question::find($questionId);
        $match = GameMatch::find($question->game_match_id);

        if ($match->actual_question != $questionId && $match->actual_question != 0) {
            return response()->json(['message' => 'Ma ma ma ma é no duuuuuro?! Tá pensando que engana o Silvio?!'], 403);
        }

        if ($question->answered) {
            return response()->json(['message' => 'question alredy answered'], 503);
        }

        if ($match->actual_question == 0) {
            return response()->json(['message' => 'first read the question accessing question GET'], 503);
        }

        $givenAnswer = $request->answer;
        $givenAnswer = trim($givenAnswer);

        $correctAnswer = $question->correct_answer;
        $correctAnswer = trim($correctAnswer);

        $actualPlayer = Player::getActualPlayer($question->game_match_id);

        if ($request->stop) {
            if ($actualPlayer->on_question == 1) {
                return response()->json(['message' => 'you can\'t stop on the first question'], 503);
            }

            $question->answered = true;
            $question->save();

            $actualPlayer->loose = true;
            $actualPlayer->save();
            return response()->json(['message' => "you earned R$$actualPlayer->money"], 200);
        }

        if ($request->jump && $actualPlayer->jumps <= 0) {
            return response()->json(['message' => 'you don\'t have more jumps'], 503);
        }

        if ($request->cards) {
            if ($question->remove_answers > 0) {
                return response()->json(['message' => 'alredy used in this question'], 503);
            }

            if ($actualPlayer->cards <= 0) {
                return response()->json(['message' => 'you don\'t have more cards'], 503);
            }

            $answersRemoved = mt_rand(1, 3);
            $question->remove_answers = $answersRemoved;
            $question->save();

            $actualPlayer->decrement('cards');
            $actualPlayer->save();

            return response()->json(['message' => "$answersRemoved answers removed", 'correct' => null]);
        }

        $match->actual_question = 0;
        $match->save();

        $question->answered = true;
        $question->save();

        if ($request->jump && $actualPlayer->jumps > 0) {
            $actualPlayer->decrement('jumps');
            $actualPlayer->save();
            return response()->json(['message' => 'jumped', 'correct' => null]);
        }


        if ($givenAnswer == $correctAnswer) {
            $actualPlayer->money = nextPrize($actualPlayer->on_question);

            if ($actualPlayer->on_question < 15) {
                $actualPlayer->increment('on_question');
            } else {
                $actualPlayer->one_million = true;
            }

            $actualPlayer->save();

            return response()->json(['message' => 'correct answer', 'correct' => true]);
        } else {
            $actualPlayer->loose = true;
            $actualPlayer->money = $actualPlayer->money == 0 ? 0 : round($actualPlayer->money / 2);
            $actualPlayer->save();
            return response()->json(['message' => 'incorrect answer', 'correct' => false]);
        }
    }
}
