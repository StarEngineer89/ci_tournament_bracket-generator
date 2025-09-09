<?php

namespace App\Libraries;

class TournamentLibrary
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;
    protected $tournamentMembersModel;
    protected $votesModel;
    protected $shareSettingsModel;
    protected $audioSettingsModel;
    protected $roundSettingsModel;
    protected $roundRankingsModel;
    protected $schedulesModel;
    protected $logActionsModel;
    
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->shareSettingsModel = model('\App\Models\ShareSettingsModel');
        $this->audioSettingsModel = model('\App\Models\AudioSettingModel');
        $this->roundSettingsModel = model('\App\Models\TournamentRoundSettingsModel');
        $this->roundRankingsModel = model('\App\Models\TournamentRoundRankingsModel');
        $this->schedulesModel = model('\App\Models\SchedulesModel');
        $this->logActionsModel = model('\App\Models\LogActionsModel');
    }

    public function deleteTournament($tournament_id)
    {
        $this->shareSettingsModel->where(['tournament_id' => $tournament_id])->delete();
        $this->roundSettingsModel->where(['tournament_id' => $tournament_id])->delete();
        $this->logActionsModel->where(['tournament_id' => $tournament_id])->delete();
        $this->votesModel->where(['tournament_id' => $tournament_id])->delete();

        $registeredUsers = $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->participantInfo()->where('registered_user_id Is Not Null')->findColumn('registered_user_id');
        $participants = $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->participantInfo()->findAll();
        if ($participants) {
            foreach ($participants as $participant) {
                // Check if the participant was participated to multiple tournaments and delete it if not
                if (!$participant['id'] || count($this->tournamentMembersModel->where('tournament_members.participant_id', $participant['id'])->groupBy('tournament_id')->findAll()) > 1) {
                    continue;
                }

                if ($participant['image']) {
                    unlink(WRITEPATH . $participant['image']);
                }

                $this->participantsModel->delete($participant['id']);
            }

            $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->delete();
        }

        $audioSettings = $this->audioSettingsModel->where(['tournament_id' => $tournament_id])->findAll();
        if ($audioSettings) {
            foreach ($audioSettings as $setting) {
                if ($setting['path'] && file_exists(WRITEPATH . 'uploads/' . $setting['path'])) {
                    unlink(WRITEPATH . 'uploads/' . $setting['path']);
                }
            }
        }
        $this->audioSettingsModel->where(['tournament_id' => $tournament_id])->delete();

        $this->bracketsModel->where(['tournament_id' => $tournament_id])->delete();
        
        $this->schedulesModel->where(['tournament_id' => $tournament_id])->delete();

        $tournament = $this->tournamentsModel->find($tournament_id);
        $this->tournamentsModel->where('id', $tournament_id)->delete();

        /** Send the notification and emails to the registered users */
        $auth_user_id = auth()->user() ? auth()->user()->id : 0;
        if ($registeredUsers) {
            $userProvider = auth()->getProvider();
            $userSettingService = service('userSettings');
            $notificationService = service('notification');

            $tournamentEntity = new \App\Entities\Tournament($tournament);
            foreach ($registeredUsers as $user_id) {
                $user = $userProvider->findById($user_id);

                $message = lang('Notifications.tournamentDeleted', [$tournamentEntity->name]);
                $notificationService->addNotification(['user_id' => $auth_user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_DELETE, 'link' => "tournaments/$tournamentEntity->id/view"]);

                if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                    $creator = $userProvider->findById($tournamentEntity->user_id);
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($user->email);
                    $email->setSubject(lang('Emails.tournamentDeleteEmailSubject'));
                    $email->setMessage(view(
                        'email/tournament-delete',
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
        
        return true;
    }

    public function advanceFFABrackets($tournament_id, $round_no, $approve = false) {
        helper('db');
        helper('participant');

        $user_id = auth()->user() ? auth()->user()->id : 0;

        $tournament_settings = $this->tournamentsModel->asObject()->find($tournament_id);
        $roundSetting = $this->roundSettingsModel->where(['tournament_id' => $tournament_id, 'round_no' => $round_no])->asObject()->first();
        if (!$roundSetting) {
            $roundSetting = new \App\Entities\TournamentRoundSetting();
            $roundSetting->tournament_id = $tournament_id;
            $roundSetting->round_no = $round_no;
            $roundSetting->user_id = $user_id;
        }

        if ($tournament_settings->voting_mechanism == EVALUATION_VOTING_MECHANISM_MAXVOTE) {
            return false;
        }

        $brackets = $this->bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no])->asObject()->findAll();
        foreach ($brackets as $bi => $bracket) {
            $metricsData = $this->roundRankingsModel->where(['tournament_id' => $tournament_id, 'round_no' => $round_no, 'bracket_id' => $bracket->id])->asObject()->orderBy('score', 'DESC')->findAll();

            /** Sort the metrics data by score and time */
            if ($tournament_settings->ranking == TOURNAMENT_RANKING_BY_SCORE) {
                /** Rank the participants by tiebreaker */
                // Tiebreaker by time
                if ($tournament_settings->tiebreaker == TIEBREKER_BYTIME) {
                    usort($metricsData, function ($a, $b) {
                        // First compare by score (higher score first)
                        if ($a->score != $b->score) {
                            return $b->score <=> $a->score; // descending
                        }

                        // If score is the same, compare by time (lower/earlier time first)
                        return $a->time <=> $b->time; // ascending
                    });
                }

                // Tiebreaker by random
                if ($tournament_settings->tiebreaker == TIEBREKER_RANDOM) {
                    usort($metricsData, function ($a, $b) {
                        // Sort by score first (higher score first)
                        if ($a->score != $b->score) {
                            return $b->score <=> $a->score;
                        }

                        // If scores are equal, randomize order
                        return rand(-1, 1);
                    });
                }
            }

            /** Sort the metrics by Weighted Formula */
            if ($tournament_settings->ranking == TOURNAMENT_RANKING_BY_WEIGHTED_FORMULA) {
                $score_weight = $tournament_settings->score_weight;
                /** Rank the participants by tiebreaker */
                // Tiebreaker by time
                if ($tournament_settings->tiebreaker == TIEBREKER_BYTIME) {
                    usort($metricsData, function ($a, $b) use ($tournament_settings) {
                        // First compare by score (higher score first)
                        $scoreWeight = (float) $tournament_settings->score_weight;
                        $timeWeight  = (float) $tournament_settings->time_weight;

                        $finalA = ((float) $a->score * $scoreWeight) - ((float) $a->time * $timeWeight);
                        $finalB = ((float) $b->score * $scoreWeight) - ((float) $b->time * $timeWeight);

                        if ($finalA != $finalB) {
                            return $finalB <=> $finalA; // higher formula result first
                        }

                        return $a->time <=> $b->time; // ascending
                    });
                }

                // Tiebreaker by random
                if ($tournament_settings->tiebreaker == TIEBREKER_RANDOM) {
                    usort($metricsData, function ($a, $b) use ($tournament_settings) {
                        // Sort by score first (higher score first)
                        $scoreWeight = (float) $tournament_settings->score_weight;
                        $timeWeight  = (float) $tournament_settings->time_weight;

                        $finalA = ((float) $a->score * $scoreWeight) - ((float) $a->time * $timeWeight);
                        $finalB = ((float) $b->score * $scoreWeight) - ((float) $b->time * $timeWeight);

                        if ($finalA != $finalB) {
                            return $finalB <=> $finalA; // higher formula result first
                        }

                        // If scores are equal, randomize order
                        return rand(-1, 1);
                    });
                }

                // Tiebreaker by Host
                if ($tournament_settings->tiebreaker == TIEBREKER_HOSTDECIDE) {
                    usort($metricsData, function($a, $b) use ($tournament_settings) {
                        $scoreWeight = (float) $tournament_settings->score_weight;
                        $timeWeight  = (float) $tournament_settings->time_weight;

                        $finalA = ((float) $a->score * $scoreWeight) - ((float) $a->time * $timeWeight);
                        $finalB = ((float) $b->score * $scoreWeight) - ((float) $b->time * $timeWeight);

                        return $finalB <=> $finalA; // higher weighted formula first
                    });
                }
            }

            // Save the rankings into DB and Advance the participants to the next round
            $prevScore = 0;
            $nextScore = 0;
            $score = 0;
            foreach ($metricsData as $index => $row) {
                if ($tournament_settings->tiebreaker == TIEBREKER_HOSTDECIDE) {
                    $tiebreaker = false;
                    
                    if ($tournament_settings->ranking == TOURNAMENT_RANKING_BY_SCORE) {
                        if (isset($metricsData[$index - 1])) {
                            $prev = $metricsData[$index - 1];
                            $prevScore = $prev->score;
                        }

                        if (isset($metricsData[$index + 1])) {
                            $next = $metricsData[$index + 1];
                            $nextScore = $next->score;
                        } else {
                            $nextScore = 0;
                        }

                        $score = $row->score;
                    }

                    if ($tournament_settings->ranking == TOURNAMENT_RANKING_BY_WEIGHTED_FORMULA) {
                        if (isset($metricsData[$index - 1])) {
                            $prev = $metricsData[$index - 1];
                            $prevScore = (float)$prev->score * (float)$tournament_settings->score_weight - (float)$prev->time * (float)$tournament_settings->time_weight;
                        }

                        if (isset($metricsData[$index + 1])) {
                            $next = $metricsData[$index + 1];
                            $nextScore = (float)$next->score * (float)$tournament_settings->score_weight - (float)$next->time * (float)$tournament_settings->time_weight;
                        } else {
                            $nextScore = 0;
                        }

                        $score = (float)$row->score * (float)$tournament_settings->score_weight - (float)$row->time * (float)$tournament_settings->time_weight;
                    }

                    if (($prevScore == $score) || ($nextScore == $score)) {
                        if (intval($roundSetting->status) != TOURNAMENT_ROUND_STATUS_HOSTOVERRIDE) {
                            $roundSetting->status = TOURNAMENT_ROUND_STATUS_HOSTOVERRIDE;

                            if (!$user_id) {
                                disableForeignKeyCheck();
                            }

                            $this->roundSettingsModel->save($roundSetting);

                            if (!$user_id) {
                                enableForeignKeyCheck();
                            }
                        }

                        continue;
                    }
                }

                $row->ranking = $index + 1;
                $this->roundRankingsModel->save($row);
                
                // Advance the participants to the next round
                if ($tournament_settings->round_advance_method == AUTOMATIC || $approve) {
                    advanceParticipantInFFABracket($tournament_id, $bracket->id, $round_no, $row->participant_id);
                } else {
                    $roundSetting->status = TOURNAMENT_ROUND_STATUS_HOSTOVERRIDE;
                    $this->roundSettingsModel->save($roundSetting);
                }
            }
        }
    }
}