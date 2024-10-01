<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home');
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