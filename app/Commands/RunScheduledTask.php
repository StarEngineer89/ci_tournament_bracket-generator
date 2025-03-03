<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunScheduledTask extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Tasks';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'task:run';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Run the scheduled tasks.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'task:run [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $voteLibrary = new \App\Libraries\VoteLibrary();
        $schedulesModel = model('\App\Models\SchedulesModel');
        $schedules = $schedulesModel->where(['result' => 0])->findAll();

        $host_id = auth()->user() ? auth()->user()->id : 0;
        
        if ($schedules) {
            foreach($schedules as $schedule) {
                $schedule_time = new \DateTime($schedule['schedule_time']);
                $current_time = new \DateTime();

                if (($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART || $schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) && $current_time >= $schedule_time) {
                    $participantsModel = model('\App\Models\ParticipantModel');
                    $registeredUsers = $participantsModel->where(['tournament_id' => $schedule['tournament_id']])->where('registered_user_id Is Not Null')->findColumn('registered_user_id');

                    if ($registeredUsers) {
                        $userProvider = auth()->getProvider();
                        $userSettingService = service('userSettings');
                        $notificationService = service('notification');

                        $tournamentsModel = model('\App\Models\TournamentModel');
                        $tournament = $tournamentsModel->find($schedule['tournament_id']);
                        $tournament = new \App\Entities\Tournament($tournament);
                        foreach ($registeredUsers as $user_id) {
                            $user = $userProvider->findById($user_id);

                            if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART) {
                                $message = "The tournament \"$tournament->name\" has started!";
                                $notificationService->addNotification(['user_id' => $host_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_STARTED, 'link' => "tournaments/$tournament->id/view"]);
                            }
                            if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) {
                                $message = "The tournament \"$tournament->name\" has completed!";
                                $notificationService->addNotification(['user_id' => $host_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_COMPLETED, 'link' => "tournaments/$tournament->id/view"]);
                            }

                            if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                                $creator = $userProvider->findById($tournament->user_id);
                                $email = service('email');
                                $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                                $email->setTo($user->email);
                                if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART) {
                                    $email->setSubject(lang('Emails.tournamentStartedEmailSubject', [$tournament->name]));
                                    $email->setMessage(view(
                                        'email/tournament-started',
                                        ['username' => $user->username, 'tournament' => $tournament, 'creator' => $creator, 'role' => 'Participant', 'tournamentCreatorName' => setting('Email.fromName')],
                                        ['debug' => false]
                                    ));
                                }

                                if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) {
                                    $email->setSubject(lang('Emails.tournamentCompletedEmailSubject', [$tournament->name]));
                                    $email->setMessage(view(
                                        'email/tournament-completed',
                                        ['username' => $user->username, 'tournament' => $tournament, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName')],
                                        ['debug' => false]
                                    ));
                                }

                                if ($email->send(false) === false) {
                                    $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                                }

                                $email->clear();
                            }
                        }
                    }

                    if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART) {
                        $schedulesModel->update($schedule['id'], ['result' => 1]);
                    }
                }
                
                if (($schedule['schedule_name'] == SCHEDULE_NAME_ROUNDUPDATE || $schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) && $current_time >= $schedule_time) {
                    $voteLibrary->finalizeRound($schedule['tournament_id'], $schedule['round_no']);

                    $schedulesModel->update($schedule['id'], ['result' => 1]);
                }
            }
        }

        /** Remove expired tournaments */
        $tournamentsModel = model('\App\Models\TournamentModel');
        $tournamentLibrary = new \App\Libraries\TournamentLibrary();

        $tournaments = $tournamentsModel->where(['user_id' => 0])->findAll();
        foreach ($tournaments as $tournament) {
            if(time() - strtotime($tournament['created_at']) > 86400){
                /** Remove expired temp tournaments from cookie value */
                $tournamentLibrary->deleteTournament($tournament['id']);
            }
        }

        return true;
    }
}