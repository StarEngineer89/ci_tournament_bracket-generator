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
    }

    public function getBrackets($id)
    {
        $brackets = $this->bracketsModel->where('tournament_id', $id)->orderBy('bracketNo')->findAll();

        $rounds = array();
        if (count($brackets) > 0) {
            foreach ($brackets as $bracket) {
                $rounds[$bracket['roundNo']][] = $bracket;
            }
        }

        return json_encode($brackets);
    }

    public function updateBracket($id)
    {
        $req = $this->request->getJSON();
        $result = array();
        $bracket = $this->bracketsModel->find($id);

        if (isset($req->index)) {
            $teamnames = json_decode($bracket['teamnames']);

            if (!isset($req->participant)) {
                $participant = $this->participantsModel->where(array('name' => $req->name, 'user_by' => auth()->user()->id))->first();

                if (!$participant) {
                    $participant_id = $this->participantsModel->insert(
                        array('name' => $req->name, 'user_by' => auth()->user()->id,)
                    );
                } else {
                    $participant_id = $participant['id'];
                }
            } else {
                $participant_id = $req->participant;
            }

            $teamnames[$req->index] = ['id' => $participant_id, 'name' => $req->name];

            $insert_data = array('teamnames' => json_encode($teamnames));

            $result['participant_id'] = $participant_id;;
        }

        if (!isset($insert_data)) {
            $insert_data = $req;
        }

        $this->bracketsModel->update($id, $insert_data);

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
         * Log User Actions to update brackets such as Mark as Winner, Add Participant, Change Participant, Delete Bracket.
         */
        if (isset($req->action_code) && $req->action_code) {
            $logActionsModel = model('\App\Models\LogActionsModel');
            $insert_data = ['tournament_id' => $bracket['tournament_id'], 'action' => $req->action_code];
            if (auth()->user()) {
                $insert_data['user_by'] = auth()->user()->id;
            } else {
                $insert_data['user_by'] = 0;
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
                $data['participants'] = [$original[$req->index]->name, $req->name];
            }

            if ($req->action_code == BRACKET_ACTIONCODE_ADD_PARTICIPANT) {
                $data['participants'] = [$req->name];
            }

            $insert_data['params'] = json_encode($data);

            $logActionsModel->insert($insert_data);
        }

        return json_encode(array('result' => 'success', 'data' => $result));
    }

    public function deleteBracket($id)
    {
        $bracket = $this->bracketsModel->find($id);

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
            $insert_data['user_by'] = auth()->user()->id;
        } else {
            $insert_data['user_by'] = 0;
        }

        $data = [];
        $data['bracket_no'] = $bracket['bracketNo'];
        $data['round_no'] = $bracket['roundNo'];

        $original = json_decode($bracket['teamnames']);

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
        $this->bracketsModel->where(['tournament_id' => $tournament_id, 'user_by' => auth()->user()->id])->delete();
        
        /**
         * Update tournament searchable data
         */
        $tournament_model = model('\App\Models\TournamentModel');
        $tournament = $tournament_model->find($tournament_id);
        $tournament['searchable'] = $tournament['name'];
        $tournament_model->save($tournament);

        return json_encode(array('result' => 'success'));
    }

    public function generateBrackets()
    {

        $list = $this->request->getPost('list');
        $participant_names_string = '';

        if (count($list) > 0) {
            foreach ($list as $item) {
                $data = [
                    'order' => $item['order']
                ];

                $this->participantsModel->update($item['id'], $data);

                $participant_names_string .= $item['name'] .',';
            }
        }

        $brackets_type = $this->request->getPost('type');

        $brackets = array();
        if ($brackets_type == 'Single') {
            $brackets = $this->createBrackets('s');
        }

        if ($brackets_type == 'Double') {
            $brackets = $this->createBrackets('d');
        }

        /** Fill the Searchable field into tournament */
        $tournamentModel = model('\App\Models\TournamentModel');
        if ($this->request->getPost('tournament_id')) {
            $tournament = $tournamentModel->find($this->request->getPost('tournament_id'));
            $tournament['searchable'] = $tournament['name'] . ',' . $participant_names_string;
            $tournamentModel->save($tournament);
        }

        return json_encode(array('result' => 'success', 'brackets' => $brackets, 'request' => $this->request->getPost()));
    }

    public function switchBrackets()
    {
        $brackets_type = $this->request->getPost('type');
        $tournament_id = $this->request->getPost('tournament_id');

        $this->bracketsModel->where(array('tournament_id' => $tournament_id))->delete();

        if ($brackets_type == 'Single') {
            $brackets = $this->createBrackets('s');

            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->update($tournament_id, ['type' => 1]);
        }

        if ($brackets_type == 'Double') {
            $brackets = $this->createBrackets('d');

            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->update($tournament_id, ['type' => 2]);
        }

        return json_encode(array('result' => $brackets_type));
    }

    public function createBrackets($type = 's')
    {
        $participants = $this->participantsModel->select(['id', 'name'])->where('user_by', auth()->user()->id)->orderBy('order')->findAll();

        $knownBrackets = array(2, 4, 8, 16, 32);

        $this->_base = count($participants);

        $closest = current(array_filter($knownBrackets, function ($e) {
            return $e >= $this->_base;
        }));
        $byes = $closest - $this->_base;

        if ($byes > 0)    $this->_base = $closest;

        for ($i = 1; $i <= $byes; $i++) {
            $participants[] = null;
        }

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

            $bracket = array(
                'lastGames' => ($round == 1) ? null : json_encode([$last[0]['game'], $last[1]['game']]),
                'nextGame' => ($nextInc + $i) > $this->_base ? null : $nextInc + $i,
                'teamnames' => json_encode(($round == 1) ? [$participants[$teamMark], $participants[$teamMark + 1]] : [null, null]),
                'bracketNo' => $i,
                'roundNo' => $round,
                'bye' => $isBye,
                'user_by' => auth()->user()->id,
                'tournament_id' => $this->request->getPost('tournament_id')
            );

            array_push($brackets, $bracket);
            $bracket_id = $this->bracketsModel->insert($bracket);

            $teamMark += 2;
            if ($i % 2 != 0)    $nextInc--;
            while ($baseR >= 1) {
                $round++;
                $baseC /= 2;
                $baseT = $baseT + $baseC;
                $baseR = $i / $baseT;
            }
        }

        $bracket = array(
            'lastGames' => $i - 1,
            'nextGame' => ($nextInc + $i >= $this->_base) ? null : $nextInc + $i,
            'teamnames' => json_encode(($round == 1) ? [$participants[$teamMark], $participants[$teamMark + 1]] : [null, null]),
            'bracketNo' => $i,
            'roundNo' => $round,
            'bye' => $isBye,
            'final_match' => 1,
            'user_by' => auth()->user()->id,
            'tournament_id' => $this->request->getPost('tournament_id')
        );

        array_push($brackets, $bracket);
        $bracket_id = $this->bracketsModel->insert($bracket);

        return $brackets;
    }
}