<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Entities\Group;
use App\Entities\GroupedParticipant;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class GroupsController extends BaseController
{
    protected $participantsModel;
    protected $groupsModel;
    protected $group_participantsModel;
    
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->groupsModel = model('\App\Models\GroupsModel');
        $this->group_participantsModel = model('\App\Models\GroupedParticipantsModel');
    }

    public function getList()
    {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $groups = $this->groupsModel->findAll();

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', 'groups' => $groups]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function save()
    {   
        if ($this->request->getPost('tournament_id')) {
            $tournament_id = $this->request->getPost('tournament_id');
        } else {
            $tournament_id = 0;
        }

        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $groupEntity = new Group();
            $group_id = $this->request->getPost('group_id');

            if ($this->request->getPost('group_name')) {
                $groupEntity->group_name = $this->request->getPost('group_name');
                $groupEntity->image_path = $this->request->getPost('image_path');
                
                $this->groupsModel->save($groupEntity);
                $group_id = $this->groupsModel->getInsertID();
            }

            if ($group_id) {
                $participants = $this->request->getPost('participants');
                if ($participants && is_array($participants)) {
                    foreach ($participants as $participant) {
                        $entity = new GroupedParticipant();
                        $entity->group_id = $group_id;
                        $entity->participant_id = $participant;

                        $this->group_participantsModel->save($entity);
                    }
                }
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'Group Id is not valid']);
            }

            if ($user = auth()->user()) {
                $participants = $this->participantsModel->where(['tournament_id' => $tournament_id, 'user_id' => $user->id])->withGroupInfo()->findAll();
            } else {
                $participants = $this->participantsModel->where(['tournament_id' => $tournament_id, 'sessionid' => $this->request->getPost('hash')])->withGroupInfo()->findAll();
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', 'participants' => $participants]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
}