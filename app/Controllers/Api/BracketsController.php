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
        $brackets = $this->bracketsModel->orderBy('bracketNo')->findAll();

        $rounds = array();
        if (count($brackets) > 0) {
            foreach ($brackets as $bracket) {
                $rounds[$bracket->roundNo][] = $bracket;
            }
        }

        return json_encode($rounds);
    }

    public function updateBracket()
    {
        $list = json_decode($this->request->getPost('list'));

        if (count($list) > 0) {
            foreach ($list as $item) {
                $data = [
                    'order' => $item->order
                ];

                $this->bracketsModel->update($item->id, $data);
            }
        }

        return json_encode(array('result' => 'success'));
    }

    public function deleteBracket($id)
    {
        $this->bracketsModel->where('id', $id)->delete();

        return json_encode(array('result' => 'success'));
    }

    public function generateBrackets($type) {
        $participants = $this->participantsModel->where('user_by', auth()->user()->id)->orderBy('order')->findColumn('id', 'name');

        $knownBrackets = array(2,4,8,16,32);

        $this->_base = count($participants);

        $closest = current(array_filter($knownBrackets, function($e) { return $e >= $this->_base; }));
        $byes = $closest - $this->_base;

        if($byes > 0)	$this->_base = $closest;
    
        for ($i = 1; $i <= $byes; $i++) {
            $participants[] = null;
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

        // $bracket = array(
        //     'lastGames' => ($round == 1) ? null : json_encode([$last[0]['game'], $last[1]['game']]),
        //     'nextGame' => ($nextInc+$i >= $this->_base) ? null : $nextInc + i,
        //     'teamnames' => json_encode( ($round == 1) ? [$participants[$teamMark], $participants[$teamMark+1]] : [null, null] ),
        //     'bracketNo' => $i,
        //     'roundNo' => $round,
        //     'bye' => $isBye,
        //     'final_match' => true,
        //     'user_by' => auth()->user()->id
        // );        

        // array_push($brackets, $bracket);
        // $bracket_id = $this->bracketsModel->insert($bracket);
            
        return json_encode(array('result' => 'success'));
    }
}
