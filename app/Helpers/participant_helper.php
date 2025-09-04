<?php
use CodeIgniter\CodeIgniter;

if (!function_exists('getParticipantsAndReusedGroupsInTournament')) {
    /**
     *
     * @param int $tournament_id
     * @return array
     */
    function getParticipantsAndReusedGroupsInTournament ($tournament_id, $hash = null)
    {
        $tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $groupMembersModel = model('\App\Models\GroupMembersModel');
        $userSettingService = service('userSettings');

        $participants = [];
        if ($tournament_id) {
            $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->participantInfo()->findAll();
        } else {
            $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => 0, 'tournament_members.hash' => $hash])->participantInfo()->findAll();
        }

        $filteredParticipants = [];
        $notAllowdParticipants = [];
        $reusedGroups = [];
        if ($participants) {
            foreach ($participants as $participant) {
                if (!$participant || !$participant['name']) {
                    continue;
                }
                
                if (isset($participant['group_id']) && $participant['group_id'] && !in_array($participant['group_id'], $reusedGroups)) {
                    if (count($groupMembersModel->where('group_id', $participant['group_id'])->groupBy('tournament_id')->findAll()) > 1) {
                        $reusedGroups[] = intval($participant['group_id']);
                    }
                }

                /** Check if the registered user allows the invitations */
                if ($participant['name'][0] == '@' && $participant['registered_user_id']) {
                    if ($userSettingService->get('disable_invitations', $participant['registered_user_id'])) {
                        $participant['invitation_disabled'] = true;
                        $notAllowdParticipants[] = $participant;
                        continue;
                    }
                }

                $filteredParticipants[] = $participant;
            }
        }

        // Remove the participants who refused the invitation in the profile setting
        if ($notAllowdParticipants) {
            foreach ($notAllowdParticipants as $participant) {
                if ($tournament_id) {
                    $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id, 'participant_id' => $participant['id']])->delete();
                } else {
                    $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => 0, 'tournament_members.hash' => $hash, 'participant_id' => $participant['id']])->delete();
                }
            }
        }
        
        return ['participants' => $filteredParticipants, 'notAllowed' => $notAllowdParticipants, 'reusedGroups' => $reusedGroups];
    }
}

if (!function_exists('checkAvailabilityAddToTournament')) {
    /**
     *
     * @param int $user_id
     * @return boolean
     */
    function checkAvailabilityAddToTournament ($user_id)
    {
        $userSettingsService = service('userSettings');

        if ($userSettingsService->get('disable_invitations', $user_id)) {
            return false;
        } 
        
        return true;
    }
}

if (!function_exists('advanceParticipantInFFABracket')) {
    /**
     *
     * @param int $user_id
     * @return boolean
     */
    function advanceParticipantInFFABracket ($tournament_id, $bracket_id, $round_no, $participant_id)
    {
        $tournamentsModel = model('\App\Models\TournamentModel');
        $bracketsModel = model('\App\Models\BracketModel');
        $roundRankingsModel = model('\App\Models\TournamentRoundRankingsModel');

        $metrics = $roundRankingsModel->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket_id, 'round_no' => $round_no, 'participant_id' => $participant_id])->asObject()->first();

        if (!$metrics) {
            return false;
        }

        $tournamentSettings = $tournamentsModel->asObject()->find($tournament_id);

        if ($tournamentSettings->advance_count < $metrics->ranking) {
            return false;
        }
        
        $bracket = $bracketsModel->asObject()->find($bracket_id);
        $bracketsInRound = $bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no])->asObject()->findAll();
        $nextBrackets = $bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no + 1])->asObject()->findAll();
        
        $participants = json_decode($bracket->teamnames);
        
        $advanced = false;

        $bracketIndex = 0;
        if ($bracketsInRound) {
            foreach ($bracketsInRound as $i => $b) {
                if ($b->id == $bracket_id) {
                    $bracketIndex = $i;
                }
            }
        }
        
        if ($nextBrackets) {
            foreach ($nextBrackets as $ni => $nextBracket) {
                if ($advanced) {
                    continue;
                }
                
                $nextParticipants = json_decode($nextBracket->teamnames);
                if (($bracketIndex * $tournamentSettings->advance_count + $metrics->ranking) <= ($ni + 1) * count($nextParticipants)) {
                    $order = 1;
                    foreach ($participants as $pi => $participant) {
                        if (!$participant) {
                            continue;
                        }
                        
                        if ($participant->id == $metrics->participant_id) {
                            $order = $participant->order;
                        }
                    }

                    $participantIndex = $bracketIndex * $tournamentSettings->advance_count + $metrics->ranking - 1;
                    if ($participantIndex >= count($nextParticipants)) {
                        $participantIndex = $participantIndex % count($nextParticipants);
                    }

                    $nextParticipants[$participantIndex] = ['id' => $participant_id, 'order' => $order];
                    $nextBracket->teamnames = json_encode($nextParticipants);

                    $bracketsModel->save($nextBracket);
                    $advanced = true;
                }
            }
        }
        
        return true;
    }
}