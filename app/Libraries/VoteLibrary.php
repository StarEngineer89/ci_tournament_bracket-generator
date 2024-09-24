<?php

namespace App\Libraries;

class VoteLibrary
{
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
    }

    public function markWinParticipant($voteData)
    {
        $bracketsModel = model('\App\Models\BracketModel');
        $participantsModel = model('\App\Models\ParticipantModel');
        $currentBracket = $bracketsModel->find($voteData['bracket_id']);
        $currentBracket['winner'] = $voteData['participant_id'];
        $bracketsModel->save($currentBracket);
        
        $brackets = $bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'nextGame' => $currentBracket['nextGame'], 'roundNo' => $voteData['round_no']])->findAll();
        $index = 0;
        if ($brackets) {
            foreach ($brackets as $key => $bracket) {
                if ($bracket['id'] == $voteData['bracket_id']) {
                    $index = $key;
                }
            }
        }

        $nextBracket = $bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'bracketNo' => $currentBracket['nextGame']])->first();
        $participant = $participantsModel->find($voteData['participant_id']);
        $teams = json_decode($nextBracket['teamnames']);
        $teams[$index] = ['id' => $participant['id'], 'name' => $participant['name'], 'image' => $participant['image']];
        $nextBracket['teamnames'] = json_encode($teams);
        $bracketsModel->save($nextBracket);
        
        return "Hello, ";
    }

    public function add($a, $b)
    {
        return $a + $b;
    }
}