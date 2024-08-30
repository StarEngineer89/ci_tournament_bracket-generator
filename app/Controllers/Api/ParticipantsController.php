<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;


class ParticipantsController extends BaseController
{
    protected $participantsModel;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->participantsModel = model('\App\Models\ParticipantModel');
    }

    public function addParticipant($names = null)
    {
        if (!$names) {
            $names = $this->request->getPost('name');
        }

        $tournament_id = $this->request->getPost('tournament_id') ? $this->request->getPost('tournament_id') : 0;
        $user_id = $this->request->getPost('user_id') ? $this->request->getPost('user_id') : 0;
        
        $participants = []; $inserted_count = 0;
        if ($names) {
            foreach ($names as $name) {
                $participant = new \App\Entities\Participant([
                    'name' => $name,
                    'user_id' => $user_id,
                    'tournament_id' => $tournament_id,
                    'active' => 1
                ]);

                $this->participantsModel->insert($participant);
                $participant->id = $this->participantsModel->getInsertID();
                $participants[] = $participant;
                $inserted_count++;
            }
        }

        $participants = $this->participantsModel->where(['user_id' => $user_id, 'tournament_id' => $tournament_id])->findAll();

        return json_encode(array('result' => 'success', 'participants' => $participants, 'count' => $inserted_count));
    }

    public function updateParticipant($id)
    {
        $participant = $this->participantsModel->find($id);
        $participant['name'] = $this->request->getPost('name');
        $path = WRITEPATH . 'uploads/';
		$file = $this->request->getFile('image');
        if($file){
            $filepath = '';
            if (! $file->hasMoved()) {
                $filepath = '/uploads/' . $file->store();
                $participant['image'] = $filepath;
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

        $participants = $this->participantsModel->where(['tournament_id' => 0, 'user_id' => auth()->user()->id])->findAll();

        return json_encode(array('result' => 'success', 'count' => count($participants), 'participants' => $participants));
    }
    
    public function clearParticipants()
    {
        if ($tournament_id = $this->request->getGet('t_id')) {
            $this->participantsModel->where(['user_id' => auth()->user()->id, 'tournament_id' => $tournament_id])->delete();
        } else {
            $this->participantsModel->where(['user_id' => auth()->user()->id, 'tournament_id' => 0])->delete();
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
                    'mime_in[file,text/csv]',
                ],
            ],
        ];
        // if (! $this->validateData([], $validationRule)) {
        //     $data = ['errors' => $this->validator->getErrors()];

        //     return json_encode($data);
        // }

        $path = WRITEPATH . 'uploads/';
		$file = $this->request->getFile('file');
        $filepath = '';
        if (! $file->hasMoved()) {
            $filepath = $path . $file->store();
        }

        if (!file_exists($filepath)) {
            return json_encode(['errors' => "Imported file was not saved correctly"]);
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
}