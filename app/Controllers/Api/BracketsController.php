<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BracketsController extends BaseController
{
    protected $bracketsModel;
    protected $tournamentsModel;
    protected $tournamentMembersModel;
    protected $participantsModel;
    protected $groupsModel;
    protected $groupMembersModel;
    protected $votesModel;
    protected $tournamentRoundSettingsModel;
    protected $rankingsModel;
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
        $this->tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $this->groupsModel = model('\App\Models\GroupsModel');
        $this->groupMembersModel = model('\App\Models\GroupMembersModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->tournamentRoundSettingsModel = model('\App\Models\TournamentRoundSettingsModel');
        $this->rankingsModel = model('\App\Models\TournamentRoundRankingsModel');
    }

    public function getBrackets($id)
    {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $userProvider = auth()->getProvider();
            $userSettingService = service('userSettings');

            $tournament_settings = $this->tournamentsModel->find($id);
            $brackets = $this->bracketsModel->where('tournament_id', $id)->orderBy('bracketNo')->findAll();

            $uuid = $this->request->getGet('uuid');

            $schedulesModel = model('\App\Models\SchedulesModel');
            $schedules = $schedulesModel->where('tournament_id', $id)->findAll();

            $rounds = array();
            $roundSettings = [];
            
            if (count($brackets) > 0) {
                foreach ($brackets as $bracket) {
                    $r_list = [];
                    $round_no = $bracket['roundNo'];
                    for ($r = 1; $r <= $round_no; $r++) {
                        $r_list[] = $r;
                    }

                    /** Get the counts of votes and assign to the teamnames */
                    $teams = json_decode($bracket['teamnames'], true);  
                    if ($teams) {
                        $array = [];
                        foreach ($teams as $index => $team) {
                            if (!$team) {
                                $array[$index] = null;
                                continue;
                            }

                            $array[$index] = $this->participantsModel->find($team['id']);

                            if (!$array[$index]) {
                                continue;
                            }

                            if (isset($team['is_group'])) {
                                $array[$index]['members'] = null;
                                $members = [];
                                if (isset($team['members']) && $team['members']) {
                                    foreach ($team['members'] as $member) {
                                        $members[] = $member['id'];
                                    }
                                }

                                if ($members) {
                                    $array[$index]['members'] = $this->participantsModel->whereIn('id', $members)->findAll();
                                }
                            } else {
                                if ($array[$index]['registered_user_id'] && !$userSettingService->get('hide_email_participant', $array[$index]['registered_user_id'])) {
                                    $user = $userProvider->findById($array[$index]['registered_user_id']);
                                    if ($user) {
                                        $array[$index]['email'] = $user->email;
                                    }
                                }
                            }

                            if (isset($team['is_double'])) {
                                $array[$index]['is_double'] = $team['is_double'];
                            }

                            $array[$index]['order'] = $team['order'];

                            // calculate the count of vote
                            $isDouble = (isset($team['is_double'])) ? 1 : null;
                            $votes_in_round = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $team['id'], 'is_double' => $isDouble, 'round_no' => $bracket['roundNo']])->findAll();

                            if ($tournament_settings['voting_retain'] || $bracket['knockout_final'] || ($tournament_settings['type'] != TOURNAMENT_TYPE_KNOCKOUT && $bracket['final_match'])) {
                                $votes = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $team['id'], 'is_double' => $isDouble])->whereIn('round_no', $r_list)->findAll();
                                if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['knockout_final']) {
                                    $votes = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'participant_id' => $team['id']])->findAll();
                                }
                            } else {
                                $votes = $votes_in_round;
                            }
                            $array[$index]['votes'] = count($votes);
                            $array[$index]['votes_in_round'] = count($votes_in_round);

                            // Check if user voted for the bracket
                            if (auth()->user()) {
                                $vote_in_bracket = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'bracket_id' => $bracket['bracketNo'], 'user_id' => auth()->user()->id])->first();
                            } else {
                                $vote_in_bracket = $this->votesModel->where(['tournament_id' => $bracket['tournament_id'], 'bracket_id' => $bracket['bracketNo'], 'uuid' => $uuid])->first();
                            }
                            $array[$index]['voted'] = (auth()->user() && ($tournament_settings['type'] != TOURNAMENT_TYPE_FFA) && $vote_in_bracket && $vote_in_bracket['participant_id'] == $team['id']) ? true : false;

                            // Set the ranking in the bracket
                            $ranking = $this->rankingsModel->where(['tournament_id' => $id, 'bracket_id'=> $bracket['id'], 'participant_id' => $team['id'], 'round_no' => $bracket['roundNo']])->asObject()->first();
                            $array[$index]['ranking'] = $ranking ? $ranking->ranking : null;
                            $array[$index]['score'] = $ranking ? $ranking->score : null;
                            $array[$index]['time'] = $ranking ? $ranking->time : null;
                        }
                        
                        $teams = $array;
                    }

                    if ($bracket['is_double'] == 0) {
                        $bracket['is_double'] = null;
                    }

                    $bracket['teamnames'] = json_encode($teams);

                    /** Get round name */
                    if (!isset($roundSettings[$bracket['roundNo']])) {
                        $roundSettings[$bracket['roundNo']] = $this->tournamentRoundSettingsModel->where(['tournament_id' => $bracket['tournament_id'], 'round_no' => $bracket['roundNo']])->first();
                    }

                    if ($roundSettings[$bracket['roundNo']] && isset($roundSettings[$bracket['roundNo']]['round_name'])) {
                        $bracket['round_name'] = $roundSettings[$bracket['roundNo']]['round_name'];
                    }

                    /** Get the round duration for the Round mechanism */
                    if ($tournament_settings['availability'] && ($tournament_settings['round_duration_combine'] || ($tournament_settings['evaluation_method'] == EVALUATION_METHOD_VOTING && ($tournament_settings['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND || $tournament_settings['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_OPENEND)))) {
                        $bracket['start'] = $schedules[$bracket['roundNo'] - 1]['schedule_time'];
                        $bracket['end'] = $schedules[$bracket['roundNo']]['schedule_time'];
                    }

                    if (!isset($roundSettings[$bracket['roundNo']]['status'])) {
                        $roundSettings[$bracket['roundNo']]['status'] = 0;
                    }

                    $bracket['status'] = $roundSettings[$bracket['roundNo']]['status'];

                    /** Add final bracket id in knockout brackets */
                    if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['final_match']) {
                        $nextBracket = $this->bracketsModel->where(['tournament_id' => $id, 'knockout_final' => 1])->first();
                    }

                    $rounds[] = $bracket;
                }
            }

            // Check the round progress
            $advanceStatus = null;
            $roundList = $this->bracketsModel->where('tournament_id', $id)->select('roundNo')->groupBy('roundNo')->orderBy('roundNo', 'ASC')->findAll();
            if (count($roundList) > 0) {
                foreach ($roundList as $round) {
                    $countBrackets = count($this->bracketsModel->where(['tournament_id' => $id, 'roundNo' => $round['roundNo']])->findAll());
                    $countTopParticipants = count($this->rankingsModel->where(['tournament_id' => $id, 'round_no' => $round['roundNo']])->where('ranking <', $tournament_settings['advance_count'] + 1)->findAll());

                    if ($countTopParticipants && $countTopParticipants < ($countBrackets * $tournament_settings['advance_count'])) {
                        $advanceStatus = ['round' => $round['roundNo'], 'count' => $countTopParticipants, 'max' => $countBrackets * $tournament_settings['advance_count']];
                        break;
                    }
                }
            }
            
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', 'rounds' => $rounds, 'advanceStatus' => $advanceStatus]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                            ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function updateBracket($id)
    {
        $req = $this->request->getJSON();
        $result = array();
        $bracket = $this->bracketsModel->find($id);
        $user_id = (auth()->user()) ? auth()->user()->id : 0;

        $notificationService = service('notification');
        $userSettingsService = service('userSettings');

        $wsClient = new \App\Libraries\WebSocketClient();

        $nextBracket = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame']])->findAll();
        if (count($nextBracket) == 1) {
            $nextBracket = $nextBracket[0];
        } else {
            $nextBracket = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame'], 'is_double' => $bracket['is_double']])->first();
        }

        $teamnames = json_decode($bracket['teamnames'], true);
        $original = $teamnames;

        $tournament = $this->tournamentsModel->find($bracket['tournament_id']);

        helper('db');
        helper('participant');

        /** Disable foreign key check for the guest users */
        if (!$user_id) {
            disableForeignKeyCheck();
        }

        /** Change or Add Participant Action */
        if (isset($req->name) && $req->name) {
            if (isset($req->index)) {
                $order = ($req->order - 1) * 2 + $req->index;
                if ($tournament['type'] == TOURNAMENT_TYPE_KNOCKOUT) {
                    $order = (($req->order - 1) * 2 + $req->index) * 2;
                    if ($bracket['is_double']) {
                        $order += 1;
                    }
                } else {
                    $single_count = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'roundNo' => $bracket['roundNo'], 'is_double' => 1])->countAllResults();
                    if ($bracket['is_double']) {
                        $order = ($req->order - $single_count - 1) * 2 + $req->index;
                    }
                }

                if (!isset($req->participant) || intval($req->participant) == 0) {
                    $availableToAdd = true;
                    if ($req->name[0] == '@') {
                        $name = trim($req->name, '@');
                        $user = auth()->getProvider()->where('username', $name)->first();

                        $availableToAdd = checkAvailabilityAddToTournament($user->id);
                    }

                    if (!$availableToAdd) {
                        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                            ->setJSON(['result' => 'failed', "message" => "This user \"@$user->username\" declined invitations to tournaments"]);
                    }

                    if (isset($user) && $user->id) {
                        $participant = $this->participantsModel->asObject()->where('registered_user_id', $user->id)->first();
                    }

                    if (!isset($participant) || !$participant) {
                        $participant = new \App\Entities\Participant([
                            'name' => $req->name,
                            'created_by' => $user_id,
                            'image' => null,
                            'active' => 1
                        ]);

                        if ($req->name[0] == '@' && $user) {
                            $participant->registered_user_id = $user->id;
                        }

                        $participant->id = $this->participantsModel->insert($participant);
                    }

                    $participant_id = $participant->id;

                    // Add the new participant as the tournament member
                    $member = new \App\Entities\TournamentMember([
                        'participant_id' => $participant->id,
                        'tournament_id' => $tournament['id'],
                        'order' => $order,
                        'created_by' => $user_id
                    ]);
                    $this->tournamentMembersModel->insert($member);

                    /** Send the email to the registered user */
                    if ($req->name[0] == '@' && $user) {
                        $tournamentObj = new \App\Entities\Tournament($tournament);

                        $string = 'Individual Participant';
                        $message = "You've been added to tournament \"$tournamentObj->name\" ($string)!";
                        $notificationService->addNotification(['user_id' => $user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_INVITE, 'link' => "tournaments/$tournamentObj->id/view"]);

                        if (!$userSettingsService->get('email_notification', $user->id) || $userSettingsService->get('email_notification', $user->id) == 'on') {
                            $email = service('email');
                            $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                            $email->setTo($user->email);
                            $email->setSubject(lang('Emails.inviteToTournamentEmailSubject'));
                            $email->setMessage(view(
                                'email/invite-to-tournament',
                                ['username' => $user->username, 'tournament' => $tournamentObj, 'tournamentCreatorName' => setting('Email.fromName'), 'groupName' => 'Individual Participant'],
                                ['debug' => false]
                            ));

                            if ($email->send(false) === false) {
                                $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                            }

                            $email->clear();
                        }
                    }
                } else {
                    $participant_id = $req->participant;
                }

                $participant = $this->participantsModel->find($participant_id);

                $teamnames[$req->index] = ['id' => $participant_id, 'order' => $order];

                if ($participant['is_group'] && $participant['group_id']) {
                    $teamnames[$req->index]['is_group'] = 1;

                    $members = $this->groupMembersModel->where(['group_members.tournament_id' => $bracket['tournament_id'], 'group_members.group_id' => $participant['group_id']])->details()->findAll();
                    if ($members) {
                        foreach ($members as $index => $member) {
                            $teamnames[$req->index]['members'][] = ['id' => $member['id'], 'order' => $index];
                        }
                    }
                }

                if ($teamnames[$req->index] && isset($req->is_group) && $req->is_group) {
                    $teamnames[$req->index]['is_group'] = true;
                }

                $bracket['teamnames'] = json_encode($teamnames);

                $result['participant'] = $participant;
            }

            $this->bracketsModel->save($bracket);

            $wsClient->sendMessage("tournamentUpdated");
        }

        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_REMOVE_PARTICIPANT) {
            $removedParticipant = $teamnames[$req->index];
            $teamnames[$req->index] = null;
            $bracket['teamnames'] = json_encode($teamnames);
            $this->bracketsModel->save($bracket);

            $removedParticipantData = $this->participantsModel->find($removedParticipant['id']);

            /** Check if the participant exists in other brackets */
            helper('bracket');
            $participantIsExists = checkParticipantExistingInTournament($tournament['id'], $removedParticipant['id']);

            /** Remove the participant and send the email add the notification if it was removed from all the brackets */
            if (!$participantIsExists) {
                $this->participantsModel->where('id', $removedParticipant['id'])->delete();

                /** Send the notification and email if the participant was removed if it's registered user */
                if (isset($removedParticipantData['registered_user_id']) && $removedParticipantData['registered_user_id']) {
                    $user = auth()->getProvider()->findById($removedParticipantData['registered_user_id']);
                    $creator = auth()->getProvider()->findById($tournament['user_id']);
                    $tournamentObj = new \App\Entities\Tournament($tournament);

                    $notificationService = service('notification');
                    $userSettingsService = service('userSettings');
                    $message = "You've been removed from tournament \"$tournamentObj->name\"!";
                    $notificationService->addNotification(['user_id' => $user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_PARTICIPANT_REMOVED, 'link' => "tournaments/$tournamentObj->id/view"]);

                    if (!$userSettingsService->get('email_notification', $user->id) || $userSettingsService->get('email_notification', $user->id) == 'on') {
                        $email = service('email');
                        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                        $email->setTo($user->email);
                        $email->setSubject(lang('Emails.removedFromTournamentEmailSubject'));
                        $email->setMessage(view(
                            'email/removed-from-tournament',
                            ['username' => $user->username, 'tournament' => $tournamentObj, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName')],
                            ['debug' => false]
                        ));

                        if ($email->send(false) === false) {
                            $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                        }

                        $email->clear();
                    }
                }
            }

            $wsClient->sendMessage("tournamentUpdated");
        }

        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_MARK_WINNER) {
            if ($bracket['final_match'] == 1 && $tournament && $tournament['type'] == TOURNAMENT_TYPE_KNOCKOUT) {
                $final_bracket_ids = $this->bracketsModel->where(['tournament_id' => $bracket['tournament_id'], 'final_match' => 1])->findColumn('id');
                $this->bracketsModel->update($final_bracket_ids, ['winner' => null]);
            }

            $bracket['winner'] = $req->winner;
            $bracket['win_by_host'] = 1;
            $this->bracketsModel->save($bracket);

            /** Check if the participant is in double */
            $is_double = false;
            $selectedIndex = 0;
            if ($teamnames) {
                foreach ($teamnames as $index => $team) {
                    if ($team && $team['id'] == $bracket['winner'] && isset($team['is_double']) && $team['is_double']) {
                        $is_double = true;
                    }

                    if ($team && $team['id'] == $req->winner) {
                        $selectedIndex = $index;
                    }
                }
            }

            /** Update next bracket */
            if ($nextBracket) {
                $participant = $this->participantsModel->find($req->winner);
                $teamnames = json_decode($nextBracket['teamnames'], true);
                $teamnames[$req->index] = $original[$selectedIndex];

                if ($is_double) {
                    $teamnames[$req->index]['is_double'] = 1;
                }

                $nextBracket['teamnames'] = json_encode($teamnames);
                if (isset($req->is_final) && $req->is_final) {
                    $nextBracket['winner'] = $req->winner;
                }
                $this->bracketsModel->save($nextBracket);
            }

            $wsClient->sendMessage("tournamentUpdated");
        }

        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER) {
            $bracket['winner'] = null;
            $bracket['win_by_host'] = 0;
            $this->bracketsModel->save($bracket);

            if ($nextBracket) {
                $participant = $this->participantsModel->find($req->participant);
                $teamnames = json_decode($nextBracket['teamnames'], true);
                $teamnames[$req->index] = null;
                $nextBracket['teamnames'] = json_encode($teamnames);
                $nextBracket['winner'] = null;
                $this->bracketsModel->save($nextBracket);
            }

            $wsClient->sendMessage("tournamentUpdated");
        }
        /** Change the tournament status
         *  If mark as winner in final, set status to completed
         *  If unmark a winner, set status to progress
         */
        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_MARK_WINNER && isset($req->is_final) && $req->is_final) {
            $tournament = $this->tournamentsModel->find($bracket['tournament_id']);
            $tournament['status'] = TOURNAMENT_STATUS_COMPLETED;
            $this->tournamentsModel->save($tournament);

            $winnerParticipant = $this->participantsModel->find($req->winner);
            if ($winnerParticipant['registered_user_id'] && $winner = auth()->getProvider()->findById($winnerParticipant['registered_user_id'])) {
                $tournamentEntity = new \App\Entities\Tournament($tournament);
                $creator = auth()->getProvider()->findById($tournament['user_id']);

                $message = "Congratulations! You have won the tournament \"$tournamentEntity->name\"!";
                $notificationService->addNotification(['user_id' => $user_id, 'user_to' => $winner->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_COMPLETED, 'link' => "tournaments/$tournamentEntity->id/view"]);

                if (!$userSettingsService->get('email_notification', $winner->id) || $userSettingsService->get('email_notification', $winner->id) == 'on') {
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($winner->email);
                    $email->setSubject(lang('Emails.tournamentChampionWonEmailSubject'));
                    $email->setMessage(view(
                        'email/tournament-champion-won',
                        ['username' => $winner->username, 'tournament' => $tournamentEntity, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName')],
                        ['debug' => false]
                    ));

                    if ($email->send(false) === false) {
                        $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                    }

                    $email->clear();
                }
            }
        }

        if (isset($req->action_code) && $req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER && isset($req->is_final) && $req->is_final) {
            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->find($bracket['tournament_id']);
            $tournament['status'] = TOURNAMENT_STATUS_INPROGRESS;
            $tournamentModel->save($tournament);
        }

        /** Update tournament searchable data  */
        $brackets = $this->bracketsModel->where(array('tournament_id' => $bracket['tournament_id']))->findAll();

        $participant_names_string = '';
        if ($brackets) {
            foreach ($brackets as $brck) {
                $teams = json_decode($brck['teamnames']);
                foreach ($teams as $team) {
                    if ($team) {
                        $participant = $this->participantsModel->find($team->id);
                        $participant_names_string .= $participant['name'] . ',';
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
                if (isset($req->is_group)) {
                    $data['participants'] = ['name' => $participant['name'], 'type' => 'group'];
                } else {
                    $data['participants'] = ['name' => $participant['name'], 'type' => null];
                }
            }

            if ($req->action_code == BRACKET_ACTIONCODE_UNMARK_WINNER) {
                $participant = $this->participantsModel->find($req->participant);
                if (isset($req->is_group)) {
                    $data['participants'] = ['name' => $participant['name'], 'type' => 'group'];
                } else {
                    $data['participants'] = ['name' => $participant['name'], 'type' => null];
                }
            }

            if ($req->action_code == BRACKET_ACTIONCODE_CHANGE_PARTICIPANT) {
                if ($original[$req->index]) {
                    $originalParticipant = $this->participantsModel->find($original[$req->index]['id']);
                    $data['participants'] = [['name' => $originalParticipant['name'], 'type' => (isset($original[$req->index]['is_group'])) ? "group" : null], ['name' => $req->name, 'type' => (isset($req->is_group) && $req->is_group) ? "group" : null]];
                } else {
                    $data['participants'] = [null, ['name' => $req->name, 'type' => (isset($req->is_group) && $req->is_group) ? "group" : null]];
                }
            }

            if ($req->action_code == BRACKET_ACTIONCODE_ADD_PARTICIPANT) {
                $data['participants'] = ['name' => $req->name, 'type' => null];
            }

            if ($req->action_code == BRACKET_ACTIONCODE_REMOVE_PARTICIPANT) {
                $data['participants'] = ['name' => $removedParticipantData['name'], 'type' => ($original[$req->index] && isset($original[$req->index]['is_group'])) ? 'group' : null];
            }

            $insert_data['params'] = json_encode($data);

            $logActionsModel->insert($insert_data);
        }

        /** Enalbe foreign key check for the guest users */
        if (!$user_id) {
            enableForeignKeyCheck();
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['result' => 'success', 'data' => $result]);
    }

    public function deleteBracket($id)
    {
        $bracket = $this->bracketsModel->find($id);
        $teams = json_decode($bracket['teamnames']);

        $user_id = auth()->user() ? auth()->user()->id : 0;

        /** Delete a bracket - Delete the participants in a bracket */
        $bracket['teamnames'] = json_encode([null, null]);
        $bracket['deleted_by'] = $user_id;
        $this->bracketsModel->update($id, $bracket);

        /**
         * Update tournament searchable data
         */
        $tournament = $this->tournamentsModel->find($bracket['tournament_id']);
        $tournamentEntity = new \App\Entities\Tournament($tournament);
        $brackets = $this->bracketsModel->where(array('tournament_id' => $bracket['tournament_id']))->findAll();

        $participant_names_string = '';
        if ($brackets) {
            foreach ($brackets as $br) {
                $teamsInBracket = json_decode($br['teamnames']);
                foreach ($teamsInBracket as $team) {
                    if ($team) {
                        $pt = $this->participantsModel->find($team->id);
                        $teamName = $pt ? $pt['name'] : '';

                        $participant_names_string .= $teamName . ',';
                    }
                }
            }
        }

        $tournamentEntity->searchable = $tournamentEntity->name . ',' . $participant_names_string;
        // $this->tournamentsModel->save($tournamentEntity);

        /** Check if the participant exists in other brackets and send the notification */
        helper('bracket');
        $notificationService = service('notification');
        $userSettingsService = service('userSettings');
        if ($teams) {
            $teamInfo = [];
            foreach ($teams as $index => $team) {
                if ($team) {
                    $checkExist = checkParticipantExistingInTournament($tournamentEntity->id, $team->id);

                    $deletedParticipants = [];

                    $pt = $this->participantsModel->find($team->id);
                    $teamInfo[$index] = $pt;

                    if (isset($team->is_group)) {
                        $teamInfo[$index]['is_group'] = true;

                        if ($team->members) {
                            foreach ($team->members as $member) {
                                $deletedParticipants[] = $member->id;
                            }
                        }
                    } else {
                        $deletedParticipants[] = $team->id;
                    }

                    if ($checkExist) {
                        continue;
                    }

                    if ($deletedParticipants) {
                        foreach ($deletedParticipants as $ptId) {
                            $participantInfo = $this->participantsModel->find($ptId);
                            if (!$participantInfo['registered_user_id']) {
                                continue;
                            }

                            $user = auth()->getProvider()->findById($participantInfo['registered_user_id']);
                            $creator = auth()->getProvider()->findById($tournamentEntity->user_id);

                            $message = "You've been removed from tournament \"$tournamentEntity->name\"!";
                            $notificationService->addNotification(['user_id' => $user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_PARTICIPANT_REMOVED, 'link' => "tournaments/$tournamentEntity->id/view"]);

                            if (!$userSettingsService->get('email_notification', $user->id) || $userSettingsService->get('email_notification', $user->id) == 'on') {
                                $email = service('email');
                                $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                                $email->setTo($user->email);
                                $email->setSubject(lang('Emails.removedFromTournamentEmailSubject'));
                                $email->setMessage(view(
                                    'email/removed-from-tournament',
                                    ['username' => $user->username, 'tournament' => $tournamentEntity, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName')],
                                    ['debug' => false]
                                ));

                                if ($email->send(false) === false) {
                                    $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                                }

                                $email->clear();
                            }
                        }
                    }
                }
            }
        }


        /**
         * Prepare the log data
         */
        $logActionsModel = model('\App\Models\LogActionsModel');
        $insert_data = ['tournament_id' => $bracket['tournament_id'], 'action' => BRACKET_ACTIONCODE_DELETE];
        $insert_data['user_id'] = $user_id;

        $data = [];
        $data['bracket_no'] = $bracket['bracketNo'];
        $data['round_no'] = $bracket['roundNo'];

        $participants_in_bracket = [];
        if ($teamInfo) {
            foreach ($teamInfo as $tInfo) {
                if ($tInfo) {
                    $participants_in_bracket[] = (isset($tInfo['is_group']) && $tInfo['is_group']) ? '' . $tInfo['name'] . '(Group)' : $tInfo['name'];
                } else {
                    $participants_in_bracket[] = null;
                }
            }
        }

        $data['participants'] = $participants_in_bracket;
        $insert_data['params'] = json_encode($data);

        /** Disable foreign key check for the guest users */
        helper('db_helper');
        disableForeignKeyCheck();

        /** Record a delete action log */
        $logActionsModel->insert($insert_data);

        /** Enable foreign key check */
        enableForeignKeyCheck();

        return json_encode(array('result' => 'success'));
    }

    public function clearBrackets($tournament_id)
    {
        $user_id = auth()->user() ? auth()->user()->id : 0;
        $this->bracketsModel->where(['tournament_id' => $tournament_id, 'user_id' => auth()->user()->id])->delete();

        /**
         * Update tournament searchable data
         */
        $tournament = $this->tournamentsModel->asObject()->find($tournament_id);
        $tournament->searchable = $tournament->name;
        $this->tournamentsModel->save($tournament);

        /** Remove vote history */
        $this->votesModel->where(['tournament_id' => $tournament_id])->delete();

        /** Remove rankings */
        $this->rankingsModel->where(['tournament_id'=> $tournament_id])->delete();

        /**
         * Log User Actions to update brackets such as Mark as Winner, Add Participant, Change Participant, Delete Bracket.
         */
        $logActionsModel = model('\App\Models\LogActionsModel');
        $insert_data = ['tournament_id' => $tournament_id, 'action' => BRACKET_ACTIONCODE_CLEAR];
        $insert_data['user_id'] = auth()->user() ? auth()->user()->id : 0;

        $data = [];
        $insert_data['params'] = json_encode($data);

        $logActionsModel->insert($insert_data);

        /** Clear Schedules */
        $schedulesModel = model('\App\Models\SchedulesModel');
        $schedulesModel->where(['tournament_id' => $tournament_id])->delete();

        /** Send the notification and emails to the registered users */
        $registeredUsers = $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->where('registered_user_id Is Not Null')->participantInfo()->findColumn('registered_user_id');
        if ($registeredUsers) {
            $userProvider = auth()->getProvider();
            $userSettingService = service('userSettings');
            $notificationService = service('notification');

            foreach ($registeredUsers as $user_id) {
                $user = $userProvider->findById($user_id);

                if (!$user) {
                    continue;
                }

                $message = lang('Notifications.tournamentReset', [$tournament->name]);
                $notificationService->addNotification(['user_id' => $insert_data['user_id'], 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_RESET, 'link' => "tournaments/$tournament->id/view"]);

                if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                    $creator = $userProvider->findById($tournament->user_id);
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($user->email);
                    $email->setSubject(lang('Emails.tournamentResetEmailSubject'));
                    $email->setMessage(view(
                        'email/tournament-reset',
                        ['username' => $user->username, 'tournament' => $tournament, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName')],
                        ['debug' => false]
                    ));

                    if ($email->send(false) === false) {
                        $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                    }

                    $email->clear();
                }
            }
        }

        return json_encode(array('result' => 'success'));
    }

    public function generateBrackets()
    {
        $list = $this->request->getPost('list');
        $hash = $this->request->getPost('hash');

        if ($notAllowedList = $this->request->getPost('notAllowedList')) {
            foreach ($notAllowedList as $item) {
                $this->tournamentMembersModel->delete($item);
            }
        }

        $participant_names_string = '';
        $tournament_id = $this->request->getPost('tournament_id');
        $brackets_type = $this->request->getPost('type');
        $user_id = auth()->user() ? auth()->user()->id : 0;

        $tournamentObj = $this->tournamentsModel->asObject()->find($tournament_id);

        $min_count = 2;
        if ($brackets_type == TOURNAMENT_TYPE_KNOCKOUT) {
            $min_count = 4;
        }

        if (count($list) < $min_count) {
            $message = "There should be at least $min_count or more participants.";
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON(['status' => 'error', 'message' => $message]);
        }

        if (count($list) > 0) {
            $all_participants = [];
            $updatedList = [];
            foreach ($list as $item) {
                if (isset($item['is_group']) && $item['is_group'] && count($item['members'])) {
                    $participant = $this->participantsModel->where('group_id', $item['id'])->first();

                    $item['id'] = $participant['id'];

                    $all_participants = array_merge($all_participants, $item['members']);
                } else {
                    $all_participants[] = $item;
                }

                $updatedList[] = $item;
            }

            // Generate the string for searchable field
            foreach ($all_participants as $item) {
                $member = $this->tournamentMembersModel->asObject()->where(['tournament_id' => $tournament_id, 'participant_id' => $item['id']])->first();
                $member->order = $item['order'];
                $this->tournamentMembersModel->save($member);

                $participant = $this->participantsModel->asObject()->find($item['id']);
                $participant_name = $participant->name;
                if ($participant_name[0] == '@') {
                    $participant_name = trim($participant_name, '@');
                }

                $participant_names_string .= $participant_name . ',';
            }
        }

        $brackets = array();
        if ($brackets_type == TOURNAMENT_TYPE_SINGLE) {
            $brackets = $this->createBrackets($updatedList, 's');
        }

        if ($brackets_type == TOURNAMENT_TYPE_DOUBLE) {
            $brackets = $this->createBrackets($updatedList, 'd');
        }

        if ($brackets_type == TOURNAMENT_TYPE_KNOCKOUT) {
            $brackets = $this->createKnockoutBrackets($updatedList);
        }

        if ($brackets_type == TOURNAMENT_TYPE_FFA) {
            $brackets = $this->createFFABrackets($updatedList, $tournamentObj->max_group_size, $tournamentObj->advance_count);
        }

        /** Fill the Searchable field into tournament */
        $tournamentObj->searchable = $tournamentObj->name . ',' . $participant_names_string;
        $this->tournamentsModel->save($tournamentObj);

        /** Add a schedule to update rounds */
        if ($tournamentObj->availability) {
            $scheduleLibrary = new \App\Libraries\ScheduleLibrary();

            $maxRoundBracket = $this->bracketsModel->where('tournament_id', $tournamentObj->id)->selectMax('roundNo')->first() ?? 1;
            $scheduleLibrary->registerSchedule($tournamentObj->id, SCHEDULE_NAME_TOURNAMENTSTART, 1, $tournamentObj->available_start);
            $scheduleLibrary->registerSchedule($tournamentObj->id, SCHEDULE_NAME_TOURNAMENTEND, $maxRoundBracket['roundNo'], $tournamentObj->available_end);

            if ($tournamentObj->round_duration_combine || ($tournamentObj->evaluation_method == EVALUATION_METHOD_VOTING && ($tournamentObj->voting_mechanism == EVALUATION_VOTING_MECHANISM_ROUND || $tournamentObj->voting_mechanism == EVALUATION_VOTING_MECHANISM_OPENEND))) {
                $scheduleLibrary->scheduleRoundUpdate($tournamentObj->id);
            }
        }

        /** Send the notifications to the participants (registered users) */
        $notificationService = new \App\Services\NotificationService();

        $users = $this->tournamentMembersModel->where('tournament_members.tournament_id', $tournament_id)->where('registered_user_id is Not Null')->participantInfo()->findAll();
        if ($users) {
            $userProvider = auth()->getProvider();
            $userSettingsService = service('userSettings');
            foreach ($users as $user) {
                $groupName = $user['group_name'];
                $user = $userProvider->findById($user['registered_user_id']);

                if (!$user) {
                    continue;
                }

                $string = $groupName ? 'Group: "' . $groupName . '"' : 'Individual Participant';
                $message = "You've been added to tournament \"$tournamentObj->name\" ($string)!";
                $notificationService->addNotification(['user_id' => $user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_INVITE, 'link' => "tournaments/$tournament_id/view"]);

                if (!$userSettingsService->get('email_notification', $user->id) || $userSettingsService->get('email_notification', $user->id) == 'on') {
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($user->email);
                    $email->setSubject(lang('Emails.inviteToTournamentEmailSubject'));
                    $email->setMessage(view(
                        'email/invite-to-tournament',
                        ['username' => $user->username, 'tournament' => $tournamentObj, 'tournamentCreatorName' => setting('Email.fromName'), 'groupName' => $groupName],
                        ['debug' => false]
                    ));

                    if ($email->send(false) === false) {
                        $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                    }

                    $email->clear();
                }
            }
        }

        return $this->response->setJSON(['result' => 'success', 'brackets' => $brackets, 'request' => $this->request->getPost()]);
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

        $this->_base = count($participants);

        $closest = pow(2, ceil(log($this->_base, 2))); /* Find the closest number from 2, 4, 8, 16, 32, ... */

        $byes = $closest - $this->_base;

        if ($byes > 0)
            $this->_base = $closest;

        for ($i = 1; $i <= $byes; $i++) {
            $participants[] = null;
        }

        if ($type == 'd') {
            $participants = array_merge($participants, $participants);
            $this->_base = count($participants);
        }

        $brackets = array();
        $round = 1;
        $baseT = $this->_base / 2;
        $baseC = $this->_base / 2;
        $teamMark = 0;
        $nextInc = $this->_base / 2;
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
            if ($i % 2 != 0)
                $nextInc--;
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
            $final_brackets = $this->bracketsModel->where(['tournament_id' => $this->request->getPost('tournament_id'), 'final_match' => 1])->findColumn('id');
            $this->bracketsModel->update($final_brackets, ['nextGame' => $i + 1, 'final_match' => null]);

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
        }

        return $brackets;
    }

    public function createKnockoutBrackets($participants)
    {
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

    public function createFFABrackets($participants, $max_group_size, $advanceCount)
    {
        $this->_base = count($participants);

        $closest = ceil($this->_base / $max_group_size) * $max_group_size; /* Find the closest number like n * $max_group_size */

        $byes = $closest - $this->_base;

        if ($byes > 0) {
            $this->_base = $closest;
        }

        for ($i = 1; $i <= $byes; $i++) {
            $participants[] = null;
        }

        $lastBracket = $this->bracketsModel->asObject()->where('tournament_id', $this->request->getPost('tournament_id'))->orderBy('bracketNo','DESC')->first();

        $brackets = array();
        $round = 1;
        $bracketNo = $lastBracket ? $lastBracket->bracketNo : 0;
        $countInRound = 0;
        $matches = $this->_base / $max_group_size;
        $group_size = $max_group_size;
        
        if ($byes > 0 && $byes > intval($this->_base / $max_group_size)) {
            $moveCount = intval($byes / intval($this->_base / $max_group_size));
            $group_size = $max_group_size - $moveCount;
        }

        $nextInc = $this->_base / $group_size;

        $bracket = new \App\Entities\Bracket();
        $bracket->tournament_id = $this->request->getPost('tournament_id');
        $bracket->user_id = (auth()->user()) ? auth()->user()->id : 0;

        /* Create the brackets in round 1 */
        while ($matches >= 1) {
            $teams = array_fill(0, $group_size, null);
            for ($i = 0; $i < $group_size; $i++) {
                $teams[$i] = ($round == 1) ? $participants[$bracketNo * $group_size + $i] : null;
            }

            $bracketNo++;
            $countInRound++;
            $bracketEntity = $bracket;
            $bracketEntity->teamnames = json_encode($teams);
            $bracketEntity->bracketNo = $bracketNo;
            $bracketEntity->roundNo = $round;
            $bracketEntity->bye = 0;

            $bracket_id = $this->bracketsModel->insert($bracketEntity);
            array_push($brackets, $bracketEntity);

            if ($matches == 1) {
                $matches = 0;
            }

            /* Reset the variables for next round */
            if ($countInRound == $matches) {
                $round++;
                $countInRound = 0;
                $group_size = $max_group_size;

                $this->_base = $matches * $advanceCount;

                $closest = ceil($this->_base / $group_size) * $group_size;
                $byes = $closest - $this->_base;
                if ($byes > 0)
                    $this->_base = $closest;

                $matches = $this->_base / $group_size;
                
                if ($byes > 0 && $byes > intval($this->_base / $group_size)) {
                    $moveCount = intval($byes / intval($this->_base / $group_size));
                    $group_size = $group_size - $moveCount;
                }
            }
        }

        /* Create Winner */
        $bracket->teamnames = json_encode([null]);
        $bracket->bracketNo = $bracketNo;
        $bracket->roundNo = $round + 1;
        $bracket->bye = 0;
        $bracket->final_match = true;
        $this->bracketsModel->insert($bracket);

        return $brackets;
    }

    public function saveRoundSettings()
    {
        if ($this->request->isAJAX()) {
            $tournament_id = $this->request->getPost('tournament_id');
            $round_no = $this->request->getPost('round_no');

            $setting = $this->tournamentRoundSettingsModel->where(['tournament_id' => $tournament_id, 'round_no' => $round_no])->first();

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

    public function saveRanking() {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $tournament_id = $this->request->getPost('tournament_id');
            $bracket_id = $this->request->getPost('bracket_id');
            $participant_id = $this->request->getPost('participant_id');
            $round_no = $this->request->getPost('roundNo');
            $ranking = ($this->request->getPost('ranking') != '-') ? $this->request->getPost('ranking') : null;
            
            $tournament = $this->tournamentsModel->asObject()->find($tournament_id);
            
            $originalRanking = null;
            if ($rankingEntity = $this->rankingsModel->asObject()->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket_id, 'participant_id' => $participant_id, 'round_no' => $round_no])->first()) {
                $originalRanking = $rankingEntity->ranking;
                $rankingEntity->ranking = $ranking;
            } else {
                $rankingEntity = new \App\Entities\TournamentRoundRanking();
                $rankingEntity->tournament_id = $tournament_id;
                $rankingEntity->bracket_id = $bracket_id;
                $rankingEntity->participant_id = $participant_id;
                $rankingEntity->round_no = $round_no;
                $rankingEntity->ranking = $ranking;
            }
            
            $user_id = auth()->user() ? auth()->user()->id : 0;
            $rankingEntity->created_by = $user_id;

            helper('db');

            /** Disable foreign key check for the guest users */
            if (!$user_id) {
                disableForeignKeyCheck();
            }

            $this->rankingsModel->save($rankingEntity);

            /** Advance the participant to the next round */
            helper('participant');
            advanceParticipantInFFABracket($tournament_id, $bracket_id, $round_no, $participant_id);

            if (!$user_id) {
                enableForeignKeyCheck();
            }

            /** If the bracket is final match, mark the final winner */
            if (intval($ranking) == 1) {
                $brackets = $this->bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no])->findAll();
                if (count($brackets) == 1) {
                    $finalBracket = $this->bracketsModel->asObject()->where(['tournament_id' => $tournament_id, 'final_match' => 1])->first();
                    if ($finalBracket) {
                        $team = ['id' => $participant_id, 'order' => 0];
                        $finalBracket->teamnames = json_encode([$team]);
                        $finalBracket->winner = $participant_id;
                        $this->bracketsModel->save($finalBracket);

                        $tournament->status = TOURNAMENT_STATUS_COMPLETED;
                        $this->tournamentsModel->save($tournament);

                        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', 'championSelected' => true]);
                    }
                }
            }

            /** Check if the champion was decided, and remove it */
            if ($originalRanking && intval($originalRanking) == 1) {
                $finalBracket = $this->bracketsModel->asObject()->where(['tournament_id' => $tournament_id, 'final_match' => 1])->first();
                if ($finalBracket) {
                    $finalBracket->teamnames = json_encode([null]);
                    $finalBracket->winner = null;
                    $this->bracketsModel->save($finalBracket);

                    $tournament->status = TOURNAMENT_STATUS_INPROGRESS;
                    $this->tournamentsModel->save($tournament);
                }
            }

            
            $advance_count = intval($tournament->advance_count);
            $advanceParticipants = $this->rankingsModel->where(['tournament_id' => $tournament_id, 'round_no' => $round_no])->where('ranking <', $advance_count + 1)->findAll();
            $brackets = $this->bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no])->findAll();

            if (count($advanceParticipants) == count($brackets) * $advance_count) {
                $this->advanceFFABrackets($advanceParticipants);
            }
            
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success']);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function saveScore() {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $tournament_id = $this->request->getPost('tournament_id');
            $bracket_id = $this->request->getPost('bracket_id');
            $participant_id = $this->request->getPost('participant_id');
            $round_no = $this->request->getPost('roundNo');
            $score = $this->request->getPost('score');
            $time = $this->request->getPost('time');
            
            $rankingEntity = $this->rankingsModel->asObject()->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket_id, 'participant_id' => $participant_id, 'round_no' => $round_no])->first();
            if (!$rankingEntity) {
                $rankingEntity = new \App\Entities\TournamentRoundRanking();
                $rankingEntity->tournament_id = $tournament_id;
                $rankingEntity->bracket_id = $bracket_id;
                $rankingEntity->participant_id = $participant_id;
                $rankingEntity->round_no = $round_no;
            }
            
            $rankingEntity->score = $score;
            $rankingEntity->time = $time;
            
            $user_id = auth()->user() ? auth()->user()->id : 0;
            $rankingEntity->created_by = $user_id;

            helper('db');

            /** Disable foreign key check for the guest users */
            if (!$user_id) {
                disableForeignKeyCheck();
            }

            $this->rankingsModel->save($rankingEntity);

            if (!$user_id) {
                enableForeignKeyCheck();
            }
            
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success']);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function advanceFFABrackets($participants) {
        $tournament_id = $this->request->getPost('tournament_id');
        $round_no = $this->request->getPost('roundNo');

        $nextBrackets = $this->bracketsModel->asObject()->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no + 1])->findAll();

        if ($nextBrackets) {
            foreach ($nextBrackets as $bracket) {
                $teams = json_decode($bracket->teamnames, true);
                $members = array_splice($participants, 0, count($teams));

                foreach ($members as $index => $member) {
                    $teams[$index] = ['id' => $member['participant_id'], 'order'=> 0];
                }

                $bracket->teamnames = json_encode($teams);
                $this->bracketsModel->save($bracket);
            }
        }

        return true;
    }
}