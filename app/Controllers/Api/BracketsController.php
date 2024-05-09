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
    
    public function getBrackets()
    {
        $brackets = $this->bracketsModel->where('user_by', auth()->user()->id)->orderBy('bracketNo')->findAll();

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
        $data = $this->request->getJSON();
        $result = array();

        if (isset($data->index)) {
            $bracket = $this->bracketsModel->find($id);
            $teamnames = json_decode($bracket['teamnames']);
            
            if (!isset($data->participant)) {
                $participant = $this->participantsModel->where(array('name' => $data->name, 'user_by' => auth()->user()->id))->first();

                if (!$participant) {
                    $participant_id = $this->participantsModel->insert(
                        array('name' => $data->name, 'user_by' => auth()->user()->id, )
                    );
                } else {
                    $participant_id = $participant['id'];
                }
            } else {
                $participant_id = $data->participant;
            }

            $teamnames[$data->index] = ['id' => $participant_id, 'name' => $data->name];

            $data = array('teamnames' => json_encode($teamnames));
            
            $result['participant_id'] = $participant_id;;
        }

        $this->bracketsModel->update($id, $data);

        return json_encode(array('result' => 'success', 'data' => $result));
    }

    public function deleteBracket($id)
    {
        $this->bracketsModel->where('id', $id)->delete();

        return json_encode(array('result' => 'success'));
    }

    public function clearBrackets($tournament_id)
    {
        $this->bracketsModel->where(['tournament_id' => $tournament_id, 'user_by' => auth()->user()->id])->delete();

        return json_encode(array('result' => 'success'));
    }

    public function generateBrackets() {
        $brackets_type = $this->request->getPost('type');

        $brackets = array();
        if ($brackets_type == 'Single') {
            $brackets = $this->createBrackets('s');
        }

        if ($brackets_type == 'Double') {
            $brackets = $this->createBrackets('d');
        }

        return json_encode(array('result' => 'success', 'brackets' => $brackets ));
    }

    public function switchBrackets() {
        $brackets_type = $this->request->getPost('type');

        $this->bracketsModel->where(array('user_by' => auth()->user()->id))->delete();
        
        if ($brackets_type == 'Single') {
            $brackets = $this->createBrackets('s');
        }

        if ($brackets_type == 'Double') {
            $brackets = $this->createBrackets('d');
        }

        return json_encode(array('result' => $brackets_type));
    }

    public function createBrackets($type = 's') {
        $participants = $this->participantsModel->select(['id', 'name'])->where('user_by', auth()->user()->id)->orderBy('order')->findAll();

        $knownBrackets = array(2,4,8,16,32);

        $this->_base = count($participants);

        $closest = current(array_filter($knownBrackets, function($e) { return $e >= $this->_base; }));
        $byes = $closest - $this->_base;

        if($byes > 0)	$this->_base = $closest;
    
        for ($i = 1; $i <= $byes; $i++) {
            $participants[] = null;
        }

        if ($type == 'd') {
            $participants = array_merge($participants, $participants);
            $this->_base = count($participants);
        }

        $brackets 	= array();
        $round 		= 1;
        $baseT 		= $this->_base/2;
        $baseC 		= $this->_base/2;
        $teamMark	= 0;
        $nextInc		= $this->_base/2;

        for($i = 1; $i <= ($this->_base - 1); $i++) {
            $baseR = $i/$baseT;
            $isBye = false;
            $this->_bracketNo = $i;
                
            if($byes>0 && ($i%2!=0 || $byes>=($baseT-$i))) {
                $isBye = true;
                $byes--;
            }
            
            $last = array();
            $last = array_map(
                        function($b) { return array('game' => $b['bracketNo'], 'teams' => $b['teamnames']); },
                        array_values(array_filter($brackets, function($b) { return $b['nextGame'] == $this->_bracketNo; }))
                    );

            $bracket = array(
                'lastGames' => ($round == 1) ? null : json_encode([$last[0]['game'], $last[1]['game']]),
                'nextGame' => ($nextInc + $i) > $this->_base ? null : $nextInc + $i,
                'teamnames' => json_encode( ($round == 1) ? [$participants[$teamMark], $participants[$teamMark+1]] : [null, null] ),
                'bracketNo' => $i,
                'roundNo' => $round,
                'bye' => $isBye,
                'user_by' => auth()->user()->id
            );

            array_push($brackets, $bracket);
            $bracket_id = $this->bracketsModel->insert($bracket);

            $teamMark += 2;
            if($i%2 != 0)	$nextInc--;
            while($baseR>=1) {
                $round++;
                $baseC/= 2;
                $baseT = $baseT + $baseC;
                $baseR = $i/$baseT;
            }
        }

        $bracket = array(
            'lastGames' => $i - 1,
            'nextGame' => ($nextInc+$i >= $this->_base) ? null : $nextInc + i,
            'teamnames' => json_encode( ($round == 1) ? [$participants[$teamMark], $participants[$teamMark+1]] : [null, null] ),
            'bracketNo' => $i,
            'roundNo' => $round,
            'bye' => $isBye,
            'final_match' => 1,
            'user_by' => auth()->user()->id
        );        

        array_push($brackets, $bracket);
        $bracket_id = $this->bracketsModel->insert($bracket);
            
        return $brackets;
    }
}
