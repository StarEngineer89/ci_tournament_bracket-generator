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
        $participantsModel = model('\App\Models\ParticipantModel');
        $groupMembersModel = model('\App\Models\GroupedParticipantsModel');
        $userSettingService = service('userSettings');

        $participants = [];
        if ($tournament_id) {
            $participants = $participantsModel->where(['participants.tournament_id' => $tournament_id])->withGroupInfo()->findAll();
        } else {
            $participants = $participantsModel->where(['participants.tournament_id' => $tournament_id, 'sessionid' => $hash])->withGroupInfo()->findAll();
        }

        $filteredParticipants = [];
        $notAllowdParticipants = [];
        $reusedGroups = [];
        if ($participants) {
            foreach ($participants as $participant) {
                if (isset($participant['g_id']) && $participant['g_id'] && !in_array($participant['g_id'], $reusedGroups)) {
                    if (count($groupMembersModel->where('group_id', $participant['g_id'])->groupBy('tournament_id')->findAll()) > 1) {
                        $reusedGroups[] = intval($participant['g_id']);
                    }
                }

                /** Check if the registered user allows the invitations */
                if ($participant['name'][0] == '@' && $participant['registered_user_id']) {
                    if ($userSettingService->get('disable_invitations', $participant['registered_user_id'])) {
                        $participant['invitation_disabled'] = true;
                        $notAllowdParticipants[] = $participant['name'];
                    }
                }

                $filteredParticipants[] = $participant;
            }
        }
        
        return ['participants' => $filteredParticipants, 'notAllowed' => $notAllowdParticipants, 'reusedGroups' => $reusedGroups];
    }
}