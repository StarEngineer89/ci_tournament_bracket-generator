<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\UploadConfig;

class ParticipantsController extends BaseController
{
    protected $participantsModel;
    protected $bracketsModel;
    protected $tournamentsModel;
    protected $votesModel;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->votesModel = model('\App\Models\VotesModel');
    }

    public function getParticipants() {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $participant_name = $this->request->getPost('participant'); // Get the posted data
            if ($participant_name) {
                $participants = $this->participantsModel->like('name', $participant_name)->findAll();
            } else {
                $participants = $this->participantsModel->findAll();
            }
            
            if ($participants) {
                $newList = [];
                foreach ($participants as $participant) {
                    $brackets = $this->bracketsModel->where(['winner' => $participant['id']])->findAll();
                    $participant['brackets_won'] = ($brackets) ? count($brackets) : 0;

                    $finalBrackets = $this->bracketsModel->where(['winner' => $participant['id'], 'final_match' => 1])->findAll();
                    $participant['tournaments_won'] = ($finalBrackets) ? count($finalBrackets) : 0;
                    
                    $tournament_list = [];
                    /** Get the tournaments list of the participant was the final winner in. */
                    if ($finalBrackets) {
                        foreach ($finalBrackets as $f_bracket) {
                            $tournament = $this->tournamentsModel->find($f_bracket['tournament_id']);
                            if ($tournament) {
                                $tournament_list[] = ['name' => $tournament['name'], 'id' => $tournament['id']];
                            }
                        }
                    }

                    /** Get all the tournaments participated */
                    // $tournament_ids = $this->bracketsModel->whereLike('teamnames', '')

                    $participant['tournaments_list'] = $tournament_list;

                    $scores = $this->calculateScores($participant['id'], $brackets);
                    $participant['top_score'] = $scores['top_score'];
                    $participant['accumulated_score'] = $scores['total_score'];

                    $votes = $this->votesModel->where('participant_id', $participant['id'])->findAll();
                    $participant['votes'] = ($votes) ? count($votes) : 0;

                    if (!$this->request->getPost('tournament')) {
                        $newList[] = $participant;
                    } else {
                        $tournament_name = $this->request->getPost('tournament');
                        $inTournamentList = false;
                        if (count($tournament_list) > 0) {
                            foreach ($tournament_list as $t) {
                                if (str_contains($t['name'], $tournament_name)) {
                                    $inTournamentList = true;
                                }
                            }
                        }
                        if ($inTournamentList) {
                            $newList[] = $participant;
                        }
                    }
                }

                $keys = array_column($newList, 'tournaments_won');
                array_multisort($keys, SORT_DESC, $newList);

                // $keys = array_column($newList, 'top_score');
                // array_multisort($keys, SORT_DESC, $newList);

                $participants = $newList;
            }
            
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON($participants);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function addParticipant($names = null)
    {
        if (!$names) {
            $names = $this->request->getPost('name');
        }

        $tournament_id = $this->request->getPost('tournament_id') ? $this->request->getPost('tournament_id') : 0;
        $user_id = $this->request->getPost('user_id') ? $this->request->getPost('user_id') : 0;
        
        $hash = $this->request->getPost('hash');
        
        $participants = []; $inserted_count = 0;
        if ($names) {
            $userProvider = auth()->getProvider();
            foreach ($names as $name) {
                if ($name) {
                    $participant = new \App\Entities\Participant([
                        'name' => $name,
                        'user_id' => $user_id,
                        'tournament_id' => $tournament_id,
                        'active' => 1,
                        'sessionid' => $hash
                    ]);
                    if ($name[0] == '@') {
                        $name = trim($name, '@');
                        $user = $userProvider->where('username', $name)->first();
                        if ($user) {
                            $participant->registered_user_id = $user->id;
                        }
                    }

                    $this->participantsModel->insert($participant);
                    $participant->id = $this->participantsModel->getInsertID();
                    $participants[] = $participant;
                    $inserted_count++;
                }
            }
        }

        $participants = $this->participantsModel->where(['user_id' => $user_id, 'tournament_id' => $tournament_id, 'sessionid' => $hash])->findAll();

        return json_encode(array('result' => 'success', 'participants' => $participants, 'count' => $inserted_count));
    }

    public function updateParticipant($id)
    {
        $participant = $this->participantsModel->find($id);
        
        if($this->request->getPost('name')) {
            $participant['name'] = $this->request->getPost('name');
        }

        if ($participant['name'][0] == '@') {
            $name = trim($participant['name'], '@');
            $user = auth()->getProvider()->where('username', $name)->first();
            if ($user) {
                $participant['registered_user_id'] = $user->id;
            }
        }

        $uploadConfig = new UploadConfig();
        
		$file = $this->request->getFile('image');
        if($file){
            $filepath = '';
            if (! $file->hasMoved()) {
                $filepath = '/uploads/' . $file->store($uploadConfig->participantImagesUploadPath);
                $participant['image'] = $filepath;

                $brackets = $this->bracketsModel->where(['tournament_id'=> $participant['tournament_id']])->findAll();
                foreach($brackets as $bracket){
                    $teamnames = json_decode($bracket['teamnames'], true);
                    $temp = [];
                    if ($teamnames) {
                        foreach ($teamnames as $teamname) {

                            if ($teamname && $teamname['id'] == $participant['id']) {
                                $teamname['image'] = $filepath;
                            }
                            $temp[] = $teamname;
                        }
                        $new_bracket = $bracket;
                        $new_bracket['teamnames'] = json_encode($temp);
                        $this->bracketsModel->update($new_bracket['id'], $new_bracket);
                    }
                }
            }
        }
        
        if($this->request->getPost('action') == 'removeImage'){
            $participant['image'] = '';
            $brackets = $this->bracketsModel->where(['tournament_id'=> $participant['tournament_id']])->findAll();
            foreach($brackets as $bracket){
                $teamnames = json_decode($bracket['teamnames'], true);
                $temp = [];
                foreach($teamnames as $teamname){

                    if($teamname && $teamname['id'] == $participant['id']){
                        $teamname['image'] = '';
                    }
                    $temp[] = $teamname;
                }
                $new_bracket = $bracket;
                $new_bracket['teamnames'] = json_encode($temp);
                $this->bracketsModel->update($new_bracket['id'], $new_bracket);
            }

        }
        $this->participantsModel->update($id, $participant);

        return json_encode(array('result' => 'success', 'data' => $participant));
    }

    public function deleteParticipant($id)
    {
        $this->participantsModel->where('id', $id)->delete();

        return json_encode(array('result' => 'success'));
    }
    
    public function deleteParticipants()
    {
        if ($participant_ids = $this->request->getPost('p_ids')) {
            $this->participantsModel->whereIn('id', $participant_ids)->delete();
        } else {
            return json_encode(array('result' => 'failed', 'msg' => 'There is not participant selected'));
        }

        $user_id = auth()->user() ? auth()->user()->id : 0;
        if ($user_id) {
            $participants = $this->participantsModel->where(['tournament_id' => 0, 'user_id' => $user_id])->findAll();
        } else {
            $participants = $this->participantsModel->where(['tournament_id' => 0, 'sessionid' => $this->request->getPost('hash')])->findAll();
        }

        return json_encode(array('result' => 'success', 'count' => count($participants), 'participants' => $participants));
    }
    
    public function clearParticipants()
    {
        if ($tournament_id = $this->request->getPost('t_id')) {
            $this->participantsModel->where(['user_id' => auth()->user()->id, 'tournament_id' => $tournament_id])->delete();
        } else {
            if (auth()->user()) {
                $this->participantsModel->where(['user_id' => auth()->user()->id, 'tournament_id' => 0])->delete();
            } else {
                $hash = $this->request->getPost('hash');
                $this->participantsModel->where(['sessionid' => $hash, 'tournament_id' => 0])->delete();
            }
        }

        return json_encode(array('result' => 'success'));
    }
    
    public function importParticipants()
    {
        $validationRule = [
            'file' => [
                'label' => 'CSV File',
                'rules' => [
                    'uploaded[file]',
                    'ext_in[file,csv]',
                ],
                'errors' => [
                    'uploaded' => 'Please upload a file.',
                    'ext_in' => 'The uploaded file must be a valid CSV.',
                ],
            ],
        ];
        
        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];
            
            return $this->response->setJSON($data);
        }

        $uploadConfig = new UploadConfig();

		$file = $this->request->getFile('file');
        $filepath = '';
        if (! $file->hasMoved()) {
            $filepath = WRITEPATH . 'uploads/' . $file->store($uploadConfig->csvUploadPath);
        }
        
        if (!file_exists($filepath)) {
            return $this->response->setJSON(['errors' => "Imported file was not saved correctly"]);
        }

		$arr_file 		= explode('.', $filepath);
		$extension 		= end($arr_file);
		if('csv' == $extension) {
			$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Csv();
		} else {
			$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		}
		$spreadsheet 	= $reader->load($filepath);
		$sheet_data 	= $spreadsheet->getActiveSheet()->toArray();
        
		$data 			= [];
		foreach($sheet_data as $key => $val) {
			if($key != 0) {
                $data[] = $val[0];
			}
		}
        
        return $this->response->setJSON(['result' => 'success', 'names' => $data]);
    }

    public function calculateScores($participant_id, $brackets) {
        $totalScore = 0;
        $topScore = 0;
        $tournamentSettings = [];
        $scores_by_tournaments = [];

        if ($brackets) {
            foreach ($brackets as $bracket) {
                $bracket_score = 0;
                $increment_score = 0;
                $increment_score_type = 0;

                if (!isset($tournamentSettings[$bracket['tournament_id']])) {
                    $tournamentSettings[$bracket['tournament_id']] = $this->tournamentsModel->find($bracket['tournament_id']);
                }

                if ($tournamentSettings[$bracket['tournament_id']]['type'] == TOURNAMENT_TYPE_KNOCKOUT) {
                    if ($bracket['knockout_final']) {
                        continue;
                    }
                } else {
                    if ($bracket['final_match']) {
                        continue;
                    }
                }
                
                $bracket_score = ($tournamentSettings[$bracket['tournament_id']]['score_enabled']) ? $tournamentSettings[$bracket['tournament_id']]['score_bracket'] : 0;
                $increment_score = ($tournamentSettings[$bracket['tournament_id']]['increment_score_enabled']) ? $tournamentSettings[$bracket['tournament_id']]['increment_score'] : 0;
                $increment_score_type = $tournamentSettings[$bracket['tournament_id']]['increment_score_type'];

                if (!isset($scores_by_tournaments[$bracket['tournament_id']])) {
                    $scores_by_tournaments[$bracket['tournament_id']] = 0;
                }

                if ($increment_score_type == TOURNAMENT_SCORE_INCREMENT_PLUS) {
                    $scores_by_tournaments[$bracket['tournament_id']] += $bracket_score + $increment_score * ($bracket['roundNo'] - 1);
                }

                if ($increment_score_type == TOURNAMENT_SCORE_INCREMENT_MULTIPLY) {
                    if ($bracket['roundNo'] == 1) {
                        $scores_by_tournaments[$bracket['tournament_id']] = $bracket_score;
                    } else {
                        $scores_by_tournaments[$bracket['tournament_id']] += $scores_by_tournaments[$bracket['tournament_id']] * $increment_score;
                    }
                }
            }
        }

        $totalScore = array_sum($scores_by_tournaments);
        $topScore = ($scores_by_tournaments) ? max($scores_by_tournaments) : 0;

        return ['total_score' => $totalScore, 'top_score' => $topScore];
    }
    
    public function export(){
        $participants = $this->participantsModel->findAll();
        
        $filename = 'participants' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        fputcsv($output, ['ID', 'Participant Name', 'Brackets Won', 'Tournaments Won', 'Participated Tournaments', 'Accumulated Score', 'Votes']);

        // Fetch the data and write it to the CSV
        if ($participants) {
            $newList = [];

            foreach ($participants as $participant) {
                $brackets = $this->bracketsModel->where(['winner' => $participant['id']])->findAll();
                $participant['brackets_won'] = ($brackets) ? count($brackets) : 0;

                $finalBrackets = $this->bracketsModel->where(['winner' => $participant['id'], 'final_match' => 1])->findAll();
                $participant['tournaments_won'] = ($finalBrackets) ? count($finalBrackets) : 0;
                
                $tournament_list = [];
                if ($finalBrackets) {
                    foreach ($finalBrackets as $f_bracket) {
                        $tournament = $this->tournamentsModel->find($f_bracket['tournament_id']);
                        if ($tournament) {
                            $tournament_list[] = $tournament['name'];
                        }
                    }
                }

                $participant['tournaments_list'] = (count($tournament_list)) ? implode(', ', $tournament_list) : '';

                $scores = $this->calculateScores($participant['id'], $brackets);
                $participant['accumulated_score'] = $scores['total_score'];

                $votes = $this->votesModel->where('participant_id', $participant['id'])->findAll();
                $participant['votes'] = ($votes) ? count($votes) : 0;

                $newList[] = $participant;
            }
            
            $keys = array_column($newList, 'tournaments_won');
            array_multisort($keys, SORT_DESC, $newList);

            foreach ($newList as $participant) {
                fputcsv($output, [
                    $participant['id'],
                    $participant['name'],
                    $participant['brackets_won'],
                    $participant['tournaments_won'],
                    $participant['tournaments_list'],
                    $participant['accumulated_score'],
                    $participant['votes']
                ]);
            }
        }

        fclose($output);
        exit;
    }
}