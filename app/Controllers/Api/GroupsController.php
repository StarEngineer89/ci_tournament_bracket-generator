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
        helper('db_helper');

        $user_id = auth()->user() ? auth()->user()->id :0;
        if ($this->request->getPost('tournament_id')) {
            $tournament_id = $this->request->getPost('tournament_id');
        } else {
            $tournament_id = 0;
        }

        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            if (!$user_id) {
                disableForeignKeyCheck();
            }

            if ($group_id = $this->request->getPost('group_id')) {
                $group = $this->groupsModel->find($group_id);

                $groupEntity = new Group($group);
                $groupEntity->group_name = $this->request->getPost('group_name');
                $groupEntity->image_path = $this->request->getPost('image_path');
                $groupEntity->user_id = $user_id;

                $this->groupsModel->save($groupEntity);
                $participantsInGroups = $this->group_participantsModel->having('g_id', $group_id)->details()->findAll();
            } else {
                $groupEntity = new Group();

                if ($this->request->getPost('group_name')) {
                    $groupEntity->group_name = $this->request->getPost('group_name');
                    $groupEntity->image_path = $this->request->getPost('image_path');
                    $groupEntity->user_id = $user_id;

                    $this->groupsModel->save($groupEntity);

                    $group_id = $this->groupsModel->getInsertID();
                }
            }
            
            if (!$user_id) {
                enableForeignKeyCheck();
            }

            if ($group_id) {
                $participants = $this->request->getPost('participants');

                if (isset($participantsInGroups) && $participantsInGroups) {
                    foreach ($participantsInGroups as $participant) {
                        $participant = new \App\Entities\Participant($participant);
                        $participant->id = null;
                        $participant->tournament_id = $tournament_id;
                        $participant->sessionid = $this->request->getPost('hash');

                        $this->participantsModel->save($participant);
                        $insertId = $this->participantsModel->getInsertID();

                        $participants[] = $insertId;
                    }
                }
                
                if ($participants && is_array($participants)) {
                    foreach ($participants as $participant) {
                        $entity = new GroupedParticipant();
                        $entity->group_id = $group_id;
                        $entity->participant_id = $participant;
                        $entity->tournament_id = $tournament_id;

                        $this->group_participantsModel->save($entity);
                    }
                }
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                    ->setJSON(['status' => 'error', 'message' => 'Failed to save the group info.']);
            }
            
            helper('participant_helper');            
            if ($tournament_id) {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
            } else {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', "participants"=> $list['participants'],"reusedGroups"=> $list['reusedGroups']]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function reset()
    {
        $user_id = auth()->user() ? auth()->user()->id :0;
        if ($this->request->getPost('tournament_id')) {
            $tournament_id = $this->request->getPost('tournament_id');
        } else {
            $tournament_id = 0;
        }

        if ($this->request->isAJAX()) {
            if ($this->request->getPost('participants')) {
                $this->group_participantsModel->where(['group_id'=> $this->request->getPost('group_id')])->whereIn('participant_id', $this->request->getPost('participants'))->delete();
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'There is not the participants to remove.']);
            }

            helper('participant_helper');            
            if ($tournament_id) {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
            } else {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', "participants"=> $list['participants'],"reusedGroups"=> $list['reusedGroups']]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
    
    public function delete()
    {
        $user_id = auth()->user() ? auth()->user()->id :0;
        if ($this->request->getPost('tournament_id')) {
            $tournament_id = $this->request->getPost('tournament_id');
        } else {
            $tournament_id = 0;
        }

        if ($this->request->isAJAX()) {
            if ($this->request->getPost('group_id')) {
                $this->group_participantsModel->where(['group_id'=> $this->request->getPost('group_id')])->delete();
                $this->groupsModel->where(['id'=> $this->request->getPost('group_id')])->delete();
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'The group was not specified.']);
            }
            
            helper('participant_helper');            
            if ($tournament_id) {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
            } else {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', "participants"=> $list['participants'],"reusedGroups"=> $list['reusedGroups']]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
    
    public function removeParticipant()
    {
        $user_id = auth()->user() ? auth()->user()->id :0;
        if ($this->request->getPost('tournament_id')) {
            $tournament_id = $this->request->getPost('tournament_id');
        } else {
            $tournament_id = 0;
        }

        if ($this->request->isAJAX()) {
            if ($this->request->getPost('participant_id')) {
                $this->group_participantsModel->where(['group_id'=> $this->request->getPost('group_id'), 'participant_id' => $this->request->getPost('participant_id')])->delete();
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'Failed to remove the participant.']);
            }

            helper('participant_helper');            
            if ($tournament_id) {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
            } else {
                $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', "participants"=> $list['participants'],"reusedGroups"=> $list['reusedGroups']]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
}