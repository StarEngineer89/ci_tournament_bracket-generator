<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;

class TournamentController extends BaseController
{
    public function index()
    {
        //
    }
    
    public function save()
    {
        $tournamentModel = model('\App\Models\TournamentModel');

        $existing = $tournamentModel->where(['name' => $this->request->getPost('name'), 'user_by' => auth()->user()->id])->findAll();

        if ($existing) {
            return json_encode(['error' => "The same tournament name is existing. Please use another name."]);
        }

        $data = [
            'name' => $this->request->getPost('title'),
            'user_by' => auth()->user()->id,
            'type' => $this->request->getPost('type'),
        ];

        $tournament_id = $tournamentModel->insert($data);

        if (!$tournament_id) {
            return json_encode(['error' => "Failed to save the tournament name."]);
        }

        if ($this->request->getPost('setting-toggle')) {
            $musicSettingsModel = model('\App\Models\MusicSettingModel');
            
            foreach ($this->request->getPost('audioType') as $index => $value) {
                $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $this->request->getPost('url')[$index];
                
                $data = [
                    'path' => $path,
                    'source' => $this->request->getPost('source')[$index],
                    'tournament_id' => $tournament_id,
                    'user_by' => auth()->user()->id,
                    'type' => $index,
                    'duration' => $this->request->getPost('duration')[$index],
                    'start' => $this->request->getPost('start')[$index],
                    'end' => $this->request->getPost('stop')[$index]
                ];
    
                $music_setting = $musicSettingsModel->insert($data);
                
                if (!$music_setting) {
                    return json_encode(['error' => "Failed to save the music settings."]);
                }
    
                $data[] = array_merge($data, ['name' => $this->request->getPost('title'), 'eliminationType' => $this->request->getPost('type')]);
            }
        }

        return json_encode(['msg' => "Success to save the tournament settings.", 'data' => $data]);
    }

    public function update()
    {
        $TournamentModel = model('\App\Models\TournamentModel');

        $brackets = $TournamentModel->where('user_by', auth()->user()->id)->findAll();

        return $this->redirect->route('tournaments');
    }

    public function upload() {
        $validationRule = [
            'audio' => [
                'label' => 'Audio File',
                'rules' => [
                    'uploaded[audio]',
                    'mime_in[audio,audio/mpeg, audio/wav,audio/ogg,audio/mid,audio/x-midi]',
                ],
            ],
        ];
        if (! $this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return json_encode($data);
        }

        $audio = $this->request->getFile('audio');

        if (! $audio->hasMoved()) {
            $filepath = $audio->store();
            
            $data = ['uploaded_fileinfo' => new File($filepath), 'path' => $filepath];

            return json_encode($data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return json_encode($data);
    }
}
