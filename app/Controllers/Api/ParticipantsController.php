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
        $participants = $this->participantsModel->orderBy('order')->findAll();

        return json_encode($participants);
    }

    public function addParticipant()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'user_by' => auth()->user()->id,
            'active' => 1
        ];
        
        $participant_id = $this->participantsModel->insert($data);
        $participants_array = array();

        if ($participant_id) {
            $participants_array = $this->participantsModel->where('id', $participant_id)->findAll();
        }

        return json_encode(array('result' => 'success', 'participant' => $participants_array));
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
}
