<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BracketsController extends BaseController
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;
    protected $votesModel;
    protected $tournamentRoundSettingsModel;
    protected $_base;
    protected $_bracketNo;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->tournamentRoundSettingsModel = model('\App\Models\TournamentRoundSettingsModel');
    }

    public function getBrackets($id)
    {
        $tournament_settings = $this->tournamentsModel->find($id);
        $brackets = $this->bracketsModel->where('tournament_id', $id)->orderBy('bracketNo')->findAll();

        $uuid = $this->request->getGet('uuid');

        $rounds = array();
        if (count($brackets) > 0) {
            foreach ($brackets as $bracket) {
                /** Get the counts of votes and assign to the teamnames */
                $teams = json_decode($bracket['teamnames'], true);
                // Check if user voted for the bracket
                if (auth()->user()) {
                    $vote_in_bracket = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'bracket_id' => $bracket['bracketNo'], 'user_id' => auth()->user()->id])->first();
                } else {
                    $vote_in_bracket = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'bracket_id' => $bracket['bracketNo'], 'uuid' => $uuid])->first();
                }
                
                if ($bracket['is_double'] == 0) {
                    $bracket['is_double'] = null;
                }

                $round_no = $bracket['roundNo'];
                $r_list = [];
                for ($r = 1; $r <= $round_no; $r++) {
                    $r_list[] = $r;
                }
                
                if ($teams[0]) {
                    $isDouble = (isset($teams[0]['is_double'])) ? 1 : null;
                    $votes_in_round = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $teams[0]['id'], 'is_double' => $isDouble, 'round_no' => $bracket['roundNo']])->findAll();
                    
                    if ($tournament_settings['voting_retain']) {
                        $votes_0 = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $teams[0]['id'], 'is_double' => $isDouble])->whereIn('round_no', $r_list)->findAll();
                        if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['knockout_final']) {
                            $votes_0 = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $teams[0]['id']])->findAll();
                        }
                    } else {
                        $votes_0 = $votes_in_round;
                    }
                    $teams[0]['votes'] = count($votes_0);
                    $teams[0]['votes_in_round'] = count($votes_in_round);
                    $teams[0]['voted'] = (auth()->user() && $vote_in_bracket && $vote_in_bracket['participant_id'] == $teams[0]['id']) ? true : false;

                    if (!isset($teams[0]['order'])) {
                        $participant = $this->participantsModel->find($teams[0]['id']);
                        $teams[0]['order'] = $participant['order'];
                    }
                }

                if ($teams[1]) {
                    $isDouble = (isset($teams[1]['is_double'])) ? 1 : null;
                    $votes_in_round = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $teams[1]['id'], 'is_double' => $isDouble, 'round_no' => $bracket['roundNo']])->findAll();
                    
                    if ($tournament_settings['voting_retain']) {
                        $votes_1 = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $teams[1]['id'], 'is_double' => $isDouble])->whereIn('round_no', $r_list)->findAll();
                        if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['knockout_final']) {
                            $votes_1 = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $teams[1]['id']])->findAll();
                        }
                    } else {
                        $votes_1 = $votes_in_round;
                    }
                    $teams[1]['votes'] = count($votes_1);
                    $teams[1]['votes_in_round'] = count($votes_in_round);
                    $teams[1]['voted'] = (auth()->user() && $vote_in_bracket && $vote_in_bracket['participant_id'] == $teams[1]['id']) ? true : false;

                    if (!isset($teams[1]['order'])) {
                        $participant = $this->participantsModel->find($teams[1]['id']);
                        $teams[1]['order'] = $participant['order'];
                    }
                }
                
                $bracket['teamnames'] = json_encode($teams);
                
                /** Get round name */
                $roundSetting = $this->tournamentRoundSettingsModel->where(['tournament_id' => $bracket['tournament_id'], 'round_no' => $bracket['roundNo'], 'knockout_second' => $bracket['is_double']])->first();
                if ($roundSetting) {
                    $bracket['round_name'] = $roundSetting['round_name'];
                }

                /** Add final bracket id in knockout brackets */
                if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['final_match']) {
                    $nextBracket = $this->bracketsModel->where(['tournament_id' => $id, 'knockout_final' => 1])->first();
                    $bracket['next_id'] = $nextBracket['id'];
                }
                
                $rounds[] = $bracket;
            }
        }

        return json_encode($rounds);
    }

    public function updateBracket($id)
    {
        $req = $this->request->getJSON();
        $result = array();
        $bracket = $this->bracketsModel->find($id);

        $nextBracket = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame']])->findAll();
        if (count($nextBracket) == 1) {
            $nextBracket = $nextBracket[0];
        } else {
            $nextBracket = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame'], 'is_double' => $bracket['is_double']])->first();
        }

        $teamnames = json_decode($bracket['teamnames']);
        $original = $teamnames;
        $tournament = $this->tournamentsModel->find($bracket['tournament_id']);
        
        if(isset($req->name) && $req->name){
            $participant = $this->participantsModel->where(['name' => $req->name, 'tournament_id' => $bracket['tournament_id']])->first();
            if (isset($req->index)) {
                if (!isset($req->participant))  {
                    if ($participant) {
                        $participant_id = $participant['id'];
                    } else {
                        $userId = (auth()->user()) ? auth()->user()->id : 0;
                        $entity = new \App\Entities\Participant([
                            'name' => $req->name,
                            'user_id' => $userId,
                            'tournament_id' => $bracket['tournament_id'],
                            'order' => $bracket['bracketNo'] * 2 - $req->index,
                            'image' => null,
                            'active' => 1
                        ]);
                        $participant_id = $this->participantsModel->insert($entity);
                        $participant = $this->participantsModel->find($participant_id);
                    }  
                } else {
                    $participant_id = $req->participant;
                }

                $teamnames[$req->index] = (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER) ? null : ['id' => $participant_id, 'name' => $req->name, 'image'=> $participant['image'], 'order' => $req->order];

                $bracket['teamnames'] = json_encode($teamnames);

                $result['participant'] = $participant;
            }

            $this->bracketsModel->save($bracket);
        }

        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_MARK_WINNER) {
            if ($bracket['final_match'] == 1 && $tournament && $tournament['type'] == TOURNAMENT_TYPE_KNOCKOUT) {
                $final_bracket_ids = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'final_match' => 1])->findColumn('id');
                $this->bracketsModel->update($final_bracket_ids, ['winner' => null]);
            }
            
            $bracket['winner'] = $req->winner;
            $bracket['win_by_host'] = 1;
            $this->bracketsModel->save($bracket);

            /** Update next bracket */
            if ($nextBracket) {
                $participant = $this->participantsModel->find($req->winner);
                $teamnames = json_decode($nextBracket['teamnames'], true);
                $teamnames[$req->index] = ['id' => $req->winner, 'name' => $participant['name'], 'image'=> $participant['image'], 'order' => $req->order];
                
                if (isset($teamnames[0]['is_double'])) {
                    $teamnames[$req->index]['is_double'] = 1;
                }

                $nextBracket['teamnames'] = json_encode($teamnames);
                if (isset($req->is_final) && $req->is_final) {
                    $nextBracket['winner'] = $req->winner;
                }
                $this->bracketsModel->save($nextBracket);
            }
        }

        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER) {
            $bracket['winner'] = null;
            $bracket['win_by_host'] = 0;
            $this->bracketsModel->save($bracket);

            if ($nextBracket) {
                $participant = $this->participantsModel->find($req->participant);
                $teamnames = json_decode($nextBracket['teamnames']);
                $teamnames[$req->index] = null;
                $nextBracket['teamnames'] = json_encode($teamnames);
                $nextBracket['winner'] = null;
                $this->bracketsModel->save($nextBracket);
            }
            
        }
        /** Change the tournament status
         *  If mark as winner in final, set status to completed
         *  If unmark a winner, set status to progress
         */
        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_MARK_WINNER && isset($req->is_final) && $req->is_final) {
            $tournament = $this->tournamentsModel->find($bracket['tournament_id']);
            $tournament['status'] = TOURNAMENT_STATUS_COMPLETED;
            $this->tournamentsModel->save($tournament);
        }
        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER && isset($req->is_final) && $req->is_final) {
            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->find($bracket['tournament_id']);
            $tournament['status'] = TOURNAMENT_STATUS_INPROGRESS;
            $tournamentModel->save($tournament);
        }

        /** Update tournament searchable data  */
        $tournament = $this->tournamentsModel->find($bracket['tournament_id']);
        $brackets = $this->bracketsModel->where(array('tournament_id'=> $bracket['tournament_id']))->findAll();
        
        $participant_names_string = '';
        if ($brackets) {
            foreach ($brackets as $brck) {
                $teams = json_decode($brck['teamnames']);
                foreach ($teams as $team) {
                    if ($team) {
                        $participant_names_string .= $team->name .',';
                    }
                }
            }
        }
        $tournament['searchable'] = $tournament['name'] . ',' . $participant_names_string;
        $this->tournamentsModel->save($tournament);

        /**
         * Log User Actions to update brackets such as Mark as Winner, Add Participant, Change Participant, Delete Bracket.
         */
        if (isset($req->action_code) && $req->action_code) {
            $logActionsModel = model('\App\Models\LogActionsModel');
            $insert_data = ['tournament_id' => $bracket['tournament_id'], 'action' => $req->action_code];
            if (auth()->user()) {
                $insert_data['user_id'] = auth()->user()->id;
            } else {
                $insert_data['user_id'] = 0;
            }

            $data = [];
            $data['bracket_no'] = $bracket['bracketNo'];
            $data['round_no'] = $bracket['roundNo'];

            if ($req->action_code == BRACKET_ACTIONCODE_MARK_WINNER) {
                $participant = $this->participantsModel->find($req->winner);
                $data['participants'] = [$participant['name']];
            }

            if ($req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER) {
                $participant = $this->participantsModel->find($req->participant);
                $data['participants'] = [$participant['name']];
            }

            $original = json_decode($bracket['teamnames']);
            if ($req->action_code == BRACKET_ACTIONCODE_CHANGE_PARTICIPANT) {
                $data['participants'] = $original[$req->index] ? [$original[$req->index]->name, $req->name] : [null, $req->name];
            }

            if ($req->action_code == BRACKET_ACTIONCODE_ADD_PARTICIPANT) {
                $data['participants'] = [$req->name];
            }

            $insert_data['params'] = json_encode($data);

            $db = \Config\Database::connect();
            $dbDriver = $db->DBDriver;
            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 0;');
            }
            
            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = OFF');
            }

            $logActionsModel->insert($insert_data);
            
            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 1;');
            }
            
            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = ON');
            }
        }

        return json_encode(array('result' => 'success', 'data' => $result));
    }

    public function deleteBracket($id)
    {
        $bracket = $this->bracketsModel->find($id);
        $teamnames = $bracket['teamnames'];

        /** Delete a bracket - Delete the participants in a bracket */
        $bracket['teamnames'] = json_encode([null, null]);
        $bracket['deleted_by'] = (auth()->user()) ? auth()->user()->id : 0;
        $this->bracketsModel->update($id, $bracket);

        /**
         * Update tournament searchable data
         */
        $tournament_model = model('\App\Models\TournamentModel');
        $tournament = $tournament_model->find($bracket['tournament_id']);
        $brackets = $this->bracketsModel->where(array('tournament_id'=> $bracket['tournament_id']))->findAll();
        
        $participant_names_string = '';
        if ($brackets) {
            foreach ($brackets as $bracket) {
                $teams = json_decode($bracket['teamnames']);
                foreach ($teams as $team) {
                    if ($team) {
                        $participant_names_string .= $team->name .',';
                    }
                }
            }
        }
        $tournament['searchable'] = $tournament['name'] . ',' . $participant_names_string;
        $tournament_model->save($tournament);

        /**
         * Prepare the log data
         */
        $logActionsModel = model('\App\Models\LogActionsModel');
        $insert_data = ['tournament_id' => $bracket['tournament_id'], 'action' => BRACKET_ACTIONCODE_DELETE];
        if (auth()->user()) {
            $insert_data['user_id'] = auth()->user()->id;
        } else {
            $insert_data['user_id'] = 0;
        }

        $data = [];
        $data['bracket_no'] = $bracket['bracketNo'];
        $data['round_no'] = $bracket['roundNo'];

        $original = json_decode($teamnames);

        $participants_in_bracket = [];
        if ($original[0]) {
            $participants_in_bracket[] = $original[0]->name;
        } else {
            $participants_in_bracket[] = null;
        }
        if ($original[1]) {
            $participants_in_bracket[] = $original[1]->name;
        } else {
            $participants_in_bracket[] = null;
        }

        $data['participants'] = $participants_in_bracket;
        $insert_data['params'] = json_encode($data);

        /** Record a delete action log */
        $logActionsModel->insert($insert_data);

        return json_encode(array('result' => 'success'));
    }

    public function clearBrackets($tournament_id)
    {
        $this->bracketsModel->where(['tournament_id' => $tournament_id, 'user_id' => auth()->user()->id])->delete();
        
        /**
         * Update tournament searchable data
         */
        $tournament_model = model('\App\Models\TournamentModel');
        $tournament = $tournament_model->find($tournament_id);
        $tournament['searchable'] = $tournament['name'];
        $tournament_model->save($tournament);

        /** Remove vote history */
        $this->votesModel->where(['tournament_id' => $tournament_id])->delete();

        /**
         * Log User Actions to update brackets such as Mark as Winner, Add Participant, Change Participant, Delete Bracket.
         */
        $logActionsModel = model('\App\Models\LogActionsModel');
        $insert_data = ['tournament_id' => $tournament['id'], 'action' => BRACKET_ACTIONCODE_CLEAR];
        if (auth()->user()) {
            $insert_data['user_id'] = auth()->user()->id;
        } else {
            $insert_data['user_id'] = 0;
        }
        $data = [];
        $insert_data['params'] = json_encode($data);

        $logActionsModel->insert($insert_data);

        return json_encode(array('result' => 'success'));
    }

    public function generateBrackets()
    {
        $list = $this->request->getPost('list');
        $participant_names_string = '';

        if (count($list) > 0) {
            foreach ($list as $item) {
                $participant = $this->participantsModel->find($item['id']);
                if ($participant) {
                    $participant['order'] = $item['order'];
                    $participant['tournament_id'] = $this->request->getPost('tournament_id');
                } else {
                    $participant = new \App\Entities\Participant(['name' => $item['name'], 'tournament_id' => $this->request->getPost('tournament_id'), 'order' => $item['order'], 'user_id' => auth()->user()->id]);
                }

                $this->participantsModel->save($participant);

                $participant_names_string .= $item['name'] .',';
            }
        }
        
        $user_id = (auth()->user()) ? auth()->user()->id : 0;
        $participants = $this->participantsModel->select(['id', 'name', 'image', 'order'])->where(['tournament_id' => $this->request->getPost('tournament_id'), 'user_id' => $user_id])->orderBy('order')->findAll();

        $brackets_type = $this->request->getPost('type');

        $brackets = array();
        if ($brackets_type == TOURNAMENT_TYPE_SINGLE) {
            $brackets = $this->createBrackets($participants, 's');
        }

        if ($brackets_type == TOURNAMENT_TYPE_DOUBLE) {
            $brackets = $this->createBrackets($participants, 'd');
        }

        if ($brackets_type == TOURNAMENT_TYPE_KNOCKOUT) {
            $brackets = $this->createKnockoutBrackets($participants);
        }

        /** Fill the Searchable field into tournament */
        $tournamentModel = model('\App\Models\TournamentModel');
        if ($this->request->getPost('tournament_id')) {
            $tournament = $tournamentModel->find($this->request->getPost('tournament_id'));
            $tournament['searchable'] = $tournament['name'] . ',' . $participant_names_string;
            $tournamentModel->save($tournament);
        }

        /** Add a schedule to update rounds */
        if ($this->request->getPost('tournament_id')) {
            if ($tournament['availability'] && $tournament['evaluation_method'] == EVALUATION_METHOD_VOTING && $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND) {
                $scheduleLibrary = new \App\Libraries\ScheduleLibrary();
                $scheduleLibrary->scheduleRoundUpdate($tournament['id']);
            }
        }

        return json_encode(array('result' => 'success', 'brackets' => $brackets, 'request' => $this->request->getPost()));
    }

    public function switchBrackets()
    {
        $brackets_type = $this->request->getPost('type');
        $tournament_id = $this->request->getPost('tournament_id');

        $this->bracketsModel->where(array('tournament_id' => $tournament_id))->delete();

        if ($brackets_type == TOURNAMENT_TYPE_SINGLE) {
            $brackets = $this->createBrackets('s');

            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->update($tournament_id, ['type' => 1]);
        }

        if ($brackets_type == TOURNAMENT_TYPE_DOUBLE) {
            $brackets = $this->createBrackets('d');

            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->update($tournament_id, ['type' => 2]);
        }

        return json_encode(array('result' => $brackets_type));
    }

    public function createBrackets($participants, $type = 's')
    {
        $user_id = (auth()->user()) ? auth()->user()->id : 0;
        
        $knownBrackets = array(2, 4, 8, 16, 32);

        $this->_base = count($participants);

        // $closest = current(array_filter($knownBrackets, function ($e) {
        //     return $e >= $this->_base;
        // }));

        $closest = pow(2, ceil(log($this->_base, 2)));
        
        $byes = $closest - $this->_base;

        if ($byes > 0)    $this->_base = $closest;

        for ($i = 1; $i <= $byes; $i++) {
            $participants[] = null;
        }

        $participants_count = count($participants);

        if ($type == 'd') {
            $participants = array_merge($participants, $participants);
            $this->_base = count($participants);
        }

        $brackets     = array();
        $round         = 1;
        $baseT         = $this->_base / 2;
        $baseC         = $this->_base / 2;
        $teamMark    = 0;
        $nextInc        = $this->_base / 2;
        $isBye = false;

        for ($i = 1; $i <= ($this->_base - 1); $i++) {
            $baseR = $i / $baseT;
            $isBye = false;
            $this->_bracketNo = $i;

            if ($byes > 0 && ($i % 2 != 0 || $byes >= ($baseT - $i))) {
                $isBye = true;
                $byes--;
            }

            $last = array();
            $last = array_map(
                function ($b) {
                    return array('game' => $b['bracketNo'], 'teams' => $b['teamnames']);
                },
                array_values(array_filter($brackets, function ($b) {
                    return $b['nextGame'] == $this->_bracketNo;
                }))
            );

            if (!isset($participants[$teamMark + 1])) {
                $participants[$teamMark + 1] = null;
            }

            // Check if bracket is double
            $is_double = null;
            if ($type == 'd') {
                if ($i > ($baseT - $baseC / 2) && $i <= $baseT) {
                    $is_double = 1;
                }
            }
            // End check if bracket is double

            // Check if bracket type is knockout
            if ($type == 'k') {
                $is_double = 1;
            }
            //End check if bracket type is knockout

            if ($round == 1) {
                $team1 = $participants[$teamMark];
                $team2 = $participants[$teamMark + 1];
                if ($type == 'd' && $is_double == 1) {
                    if ($team1) {
                        $team1['is_double'] = 1;
                    }
                    if ($team2) {
                        $team2['is_double'] = 1;
                    }
                }
            }

            $bracket = array(
                'lastGames' => ($round == 1) ? null : json_encode([$last[0]['game'], $last[1]['game']]),
                'nextGame' => ($nextInc + $i) > $this->_base ? null : $nextInc + $i,
                'teamnames' => json_encode(($round == 1) ? [$team1, $team2] : [null, null]),
                'bracketNo' => $i,
                'roundNo' => $round,
                'bye' => $isBye,
                'user_id' => $user_id,
                'tournament_id' => $this->request->getPost('tournament_id'),
                'is_double' => $is_double
            );

            $bracketEntity = new \App\Entities\Bracket($bracket);

            array_push($brackets, $bracket);
            $bracket_id = $this->bracketsModel->insert($bracketEntity);

            $teamMark += 2;
            if ($i % 2 != 0)    $nextInc--;
            while ($baseR >= 1) {
                $round++;
                $baseC /= 2;
                $baseT = $baseT + $baseC;
                $baseR = $i / $baseT;
            }
        }

        if (!isset($participants[$teamMark + 1])) {
            $participants[$teamMark + 1] = null;
        }

        $bracket = array(
            'lastGames' => $i - 1,
            'nextGame' => ($nextInc + $i >= $this->_base) ? null : $nextInc + $i,
            'teamnames' => json_encode([null, null]),
            'bracketNo' => $i,
            'roundNo' => $round,
            'bye' => $isBye,
            'final_match' => 1,
            'user_id' => $user_id,
            'tournament_id' => $this->request->getPost('tournament_id'),
            'is_double' => ($type == 'k') ? 1 : null
        );

        $bracketEntity = new \App\Entities\Bracket($bracket);

        array_push($brackets, $bracket);
        $bracket_id = $this->bracketsModel->insert($bracketEntity);

        if ($type == 'k') {
            $bracket = array(
                'lastGames' => null,
                'nextGame' => null,
                'teamnames' => json_encode([null, null]),
                'bracketNo' => $i + 1,
                'roundNo' => $round + 1,
                'bye' => 0,
                'final_match' => 1,
                'user_id' => $user_id,
                'tournament_id' => $this->request->getPost('tournament_id'),
                'is_double' => 1,
                'knockout_final' => 1
            );

            $bracketEntity = new \App\Entities\Bracket($bracket);

            array_push($brackets, $bracket);
            $bracket_id = $this->bracketsModel->insert($bracketEntity);

            $final_brackets = $this->bracketsModel->where(['tournament_id' => $this->request->getPost('tournament_id'), 'final_match' => 1])->findColumn('id');
            $this->bracketsModel->update($final_brackets, ['nextGame' => $i + 1]);
        }

        return $brackets;
    }

    public function createKnockoutBrackets($participants) {
        $list_1 = [];
        $list_2 = [];

        foreach ($participants as $index => $participant) {
            if ($index % 2 === 0) {
                $list_1[] = $participant; // Even index (0, 2, 4, ...)
            } else {
                $list_2[] = $participant; // Odd index (1, 3, 5, ...)
            }
        }

        if (count($list_1) > count($list_2)) {
            while (count($list_1) > count($list_2)) {
                $list_2[] = null;
            }
        }

        $brackets_1 = $this->createBrackets($list_1);
        $brackets_2 = $this->createBrackets($list_2, 'k');

        return array_merge($brackets_1, $brackets_2);
    }

    public function saveRoundSettings() {
        if ($this->request->isAJAX()) {
            $tournament_id = $this->request->getPost('tournament_id');
            $round_no = $this->request->getPost('round_no');
            $is_knockout_second = $this->request->getPost('knockout_second') ? 1 : null;
            
            $setting = $this->tournamentRoundSettingsModel->where(['tournament_id' => $tournament_id, 'round_no' => $round_no, 'knockout_second' => $is_knockout_second])->first();

            if ($setting) {
                if ($this->request->getPost('round_name')) {
                    $setting['round_name'] = $this->request->getPost('round_name');
                }

                $setting['user_id'] = (auth()->user()) ? auth()->user()->id : 0;
            } else {
                $setting = new \App\Entities\TournamentRoundSetting();
                $setting->user_id = (auth()->user()) ? auth()->user()->id : 0;
                $setting->tournament_id = $tournament_id;
                $setting->round_no = $round_no;
                $setting->round_name = $this->request->getPost('round_name');
                $setting->knockout_second = $is_knockout_second;
            }
            
            $db = \Config\Database::connect();
            $dbDriver = $db->DBDriver;
            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 0;');
            }

            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = OFF');
            }

            if ($this->tournamentRoundSettingsModel->save($setting)) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                      ->setJSON(['status' => 'success', 'message' => 'Round settings was saved successfully', 'setting' => $setting]);
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                      ->setJSON(['status' => 'error', 'message' => 'Failed to save settings']);
            }

            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 1;');
            }

            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('PRAGMA foreign_keys = ON');
            }
        }
        
        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
}