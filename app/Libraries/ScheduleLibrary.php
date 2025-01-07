<?php

namespace App\Libraries;

class ScheduleLibrary
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;
    protected $votesModel;
    protected $schedulesModel;
    
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->schedulesModel = model('\App\Models\SchedulesModel');
    }

    public function scheduleRoundUpdate($tournament_id)
    {
        $tournamentSettings = $this->tournamentsModel->find($tournament_id);
        $maxRounds = $this->bracketsModel->where(['tournament_id' => $tournament_id])->selectMax('roundNo')->get()->getRow();

        if ($tournamentSettings['availability']) {
            $startDate = new \DateTime($tournamentSettings['available_start']);
            $endDate = new \DateTime($tournamentSettings['available_end']);

            $tournamentDuration = $endDate->diff($startDate);
            
            $roundDuration = ($maxRounds && $maxRounds->roundNo) ? floor(intval($tournamentDuration->days) / $maxRounds->roundNo) : intval($tournamentDuration->days);

            for ($i = 1; $i < $maxRounds->roundNo; $i++) {
                $scheduleTime = $startDate->modify("+$roundDuration days")->format('Y-m-d 00:00:00');

                $this->registerSchedule($tournament_id, $i, $scheduleTime);
            }

            $scheduleTime = $endDate->format('Y-m-d 00:00:00');
            $this->registerSchedule($tournament_id, $maxRounds->roundNo, $scheduleTime);
        }
        
        return true;
    }

    public function registerSchedule($tournament_id, $round_no, $time) {
        $schedule = $this->schedulesModel->where(['schedule_name' => SCHEDULE_NAME_ROUNDUPDATE, 'tournament_id' => $tournament_id, 'round_no' => $round_no])->first();
        if ($schedule) {
            $schedule['schedule_time'] = $time;
            $schedule['result'] = 0;
        } else {
            $schedule = new \App\Entities\Schedule();
            $schedule->schedule_name = SCHEDULE_NAME_ROUNDUPDATE;
            $schedule->tournament_id = $tournament_id;
            $schedule->round_no = $round_no;
            $schedule->schedule_time = $time;
            $schedule->result = 0;
        }

        $this->schedulesModel->save($schedule);
    }

    public function unregisterSchedule($tournament_id) {
        $this->schedulesModel->where(['schedule_name' => SCHEDULE_NAME_ROUNDUPDATE, 'tournament_id' => $tournament_id])->delete();
    }
}