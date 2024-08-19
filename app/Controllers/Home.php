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
        foreach($tournaments as $tournament){
            $temp = $tournament;
            $user = $userModel->find($tournament['user_id']);
            $userId = $userIdentityModel->find($tournament['user_id']);
            // var_dump($user);exit;
            $temp['username'] = $user->username;
            $temp['email'] = $userId->secret;
            $newTournaments[] = $temp;
        }

        return view('gallery', ['tournaments' => $newTournaments, 'searchString' => $searchString]);
    }
}