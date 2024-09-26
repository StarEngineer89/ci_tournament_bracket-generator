<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home');
    }

    public function gallery()
    {
        $tournamentsModel = model('\App\Models\TournamentModel');
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $userIdentityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');
        $tournaments = $tournamentsModel->where(['visibility' => 1]);
        $searchString = '';
        if ($this->request->getGet('query')) {
            $searchString = $this->request->getGet('query');
            $tournaments->like(['tournaments.searchable' => $searchString]);
        }
        
        $tournaments = $tournaments->findAll();

        $newTournaments = array();
        $existingHistory = $this->request->getCookie('guest_tournaments');
        $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];
        
        foreach($tournaments as $tournament){
            $temp = $tournament;
            if(time() - strtotime($tournament['created_at']) > 86400 && $tournament['user_id'] == 0){
                /** Remove expired temp tournaments from cookie value */
                $shareSettingsModel = model('\App\Models\ShareSettingsModel');
                $shareSetting = $shareSettingsModel->where(['tournament_id' => $tournament['id'], 'user_id' => 0])->first();

                if ($shareSetting) {
                    $cookie_value = $tournament['id'] . "_" . $shareSetting['token'];
                    $tournamentHistory = array_values(array_diff($tournamentHistory, array($cookie_value)));
                    /** End removing expired temp tournaments from cookie value */

                    // Store updated history in cookies (expire in 1 days)
                    $this->response->setCookie('guest_tournaments', json_encode($tournamentHistory), 24 * 60 * 60);
                }
                
                $tournamentsModel->where('id', $tournament['id'])->delete();
            }
            
            $temp['username'] = 'Guest';
            $temp['email'] = '';
            if($tournament['user_id'] > 0){
                $user = $userModel->find($tournament['user_id']);
                $userId = $userIdentityModel->find($tournament['user_id']);
                $temp['username'] = $user->username;
                $temp['email'] = $userId->secret;
            }

            $participantModel = model('\App\Models\ParticipantModel');
            $temp['participants'] = count($participantModel->where('tournament_id', $tournament['id'])->findAll());

            $shareSettingModel = model('\App\Models\ShareSettingsModel');
            $sharedTournament = $shareSettingModel->where('tournament_id', $tournament['id'])->first();
            $temp['public_url'] = '';
            if($sharedTournament) $temp['public_url'] = base_url('/tournaments/shared/') . $sharedTournament['token'];

            $newTournaments[] = $temp;
        }

        return view('gallery', ['tournaments' => $newTournaments, 'searchString' => $searchString]);
    }

    public function export(){
        $tournamentModel = model('\App\Models\TournamentModel');
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $tournaments = $tournamentModel->where(['visibility' => 1]);
        $tournaments = $tournaments->findAll();
        $filename = 'tournaments_' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        if ($this->request->getGet('filter') == 'shared' && $this->request->getGet('type') == 'wh') {
            fputcsv($output, ['ID', 'Name', 'Type', 'Status', 'Accessbility', 'Shared By', 'Shared Time', 'URL']);
        } else {
            fputcsv($output, ['ID', 'Name', 'Type', 'Status', 'Created By', 'Created Time', 'URL']);
        }

        // Fetch the data and write it to the CSV
        foreach ($tournaments as $tournament) {
            $statusLabel = TOURNAMENT_STATUS_LABELS[$tournament['status']];
            $type = $tournament['type'] == 1 ? 'Single' : 'Double';

            $tournamentId = ($tournament['tournament_id']) ?? $tournament['id'];

            $user = $userModel->find($tournament['user_id']);

            $createdTime = $tournament['created_at'];
            fputcsv($output, [
                $tournamentId,
                $tournament['name'],
                $type,
                $statusLabel,
                $user->username,
                $createdTime,
                base_url('gallery/' . $tournamentId . '/view')
            ]);
        }

        fclose($output);
        exit;
    }
}