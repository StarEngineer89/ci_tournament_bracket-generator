<?php

namespace App\Libraries;

class VoteLibrary
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;

    protected $votesModel;
    
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->votesModel = model('\App\Models\VotesModel');
    }

    public function markWinParticipant($voteData)
    {
        $currentBracket = $this->bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'bracketNo' => $voteData['bracket_id'], 'roundNo' => $voteData['round_no']])->first();
        $currentBracket['winner'] = $voteData['participant_id'];
        $this->bracketsModel->save($currentBracket);
        
        $brackets = $this->bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'nextGame' => $currentBracket['nextGame'], 'roundNo' => $voteData['round_no']])->findAll();
        $index = 0;
        if ($brackets) {
            foreach ($brackets as $key => $bracket) {
                if ($bracket['id'] == $voteData['bracket_id']) {
                    $index = $key;
                }
            }
        }

        $nextBracket = $this->bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'bracketNo' => $currentBracket['nextGame']])->first();
        $participant = $this->participantsModel->find($voteData['participant_id']);
        $teams = json_decode($nextBracket['teamnames']);
        $teams[$index] = ['id' => $participant['id'], 'name' => $participant['name'], 'image' => $participant['image']];
        $nextBracket['teamnames'] = json_encode($teams);
        $this->bracketsModel->save($nextBracket);
        
        return true;
    }

    public function finalizeRound($tournament_id, $round_no)
    {
        $tournament_settings = $this->tournamentsModel->find($tournament_id);
        if ($tournament_settings['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE) {
            return false;
        }

        $brackets = $this->bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no])->findAll();
        foreach ($brackets as $bracket) {
            $teams = json_decode($bracket['teamnames'], true);

            /** Get the vote count per participants */
            $pa_votes = 0;
            $pb_votes = 0;
            if ($teams[0]) {
                $pa_votes = $this->votesModel->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $teams[0]['id'], 'round_no' => $round_no])->countAllResults();
            }
            if ($teams[1]) {
                $pb_votes = $this->votesModel->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $teams[1]['id'], 'round_no' => $round_no])->countAllResults();
            }

            /** Compare vote counts and decide who is a winner */
            if ($teams[0] && $pa_votes > $pb_votes) {
                $this->markWinParticipant(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $teams[0]['id'], 'round_no' => $round_no]);
            } else {
                if ($teams[1]) {
                    $this->markWinParticipant(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $teams[0]['id'], 'round_no' => $round_no]);
                }
            }
        }
        
        return true;
    }
}