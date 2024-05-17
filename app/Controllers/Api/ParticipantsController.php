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

    public function getParticipants()
    {
        $participants = $this->participantsModel->where('user_by', auth()->user()->id)->findAll();

        return json_encode($participants);
    }

    public function addParticipant($names = null)
    {
        if (!$names) {
            $names = $this->request->getPost('name');
        }

        $duplicated = [];
        if ($names) {
            foreach ($names as $name) {
                $data = [
                    'name' => $name,
                    'user_by' => auth()->user()->id,
                    'active' => 1
                ];

                $record = $this->participantsModel->where($data)->findAll();

                $inserted_id = $this->participantsModel->insert($data);

                if (count($record)) {
                    $data['id'] = $inserted_id;
                    $duplicated[] = $data;
                }
            }
        }

        $participants = $this->participantsModel->where(['user_by' => auth()->user()->id])->findAll();

        return json_encode(array('result' => 'success', 'participants' => $participants, 'duplicated' => $duplicated));
    }

    public function updateParticipant($id)
    {
        $this->participantsModel->update($id, $this->request->getPost());

        return json_encode(array('result' => 'success', 'data' => $this->request->getPost()));
    }

    public function updateParticipants()
    {
        $list = json_decode($this->request->getPost('list'));

        if (count($list) > 0) {
            foreach ($list as $item) {
                $data = [
                    'order' => $item->order
                ];

                $this->participantsModel->update($item->id, $data);
            }
        }

        // $userModel
        //     ->whereIn('id', [1, 2, 3])
        //     ->set(['active' => 1])
        //     ->update();
        
        return json_encode(array('result' => 'success'));
    }

    public function deleteParticipant($id)
    {
        $this->participantsModel->where('id', $id)->delete();

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
        if (! $this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return json_encode($data);
        }

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
        
        if (count($data)) {
            $result = $this->addParticipant($data);

            return $result;
        }

        return json_encode(['result' => 'success']);
    }

    public function removeDuplicates()
    {
        $rows = $this->request->getPost('names');

        if ($rows) {
            $ids = [];
            foreach ($rows as $row) {
                $ids[] = $row['id'];
            }

            if (count($ids)) {
                $this->participantsModel->whereIn('id', $ids)->delete();
            }
        }

        $participants = $this->participantsModel->where(['user_by' => auth()->user()->id])->findAll();

        return json_encode(['result' => 'success', 'participants' => $participants]);
    }
    
}
