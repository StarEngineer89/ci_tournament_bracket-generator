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
                
                $setting = [
                    'path' => $path,
                    'source' => $this->request->getPost('source')[$index],
                    'tournament_id' => $tournament_id,
                    'user_by' => auth()->user()->id,
                    'type' => $index,
                    'duration' => $this->request->getPost('duration')[$index],
                    'start' => $this->request->getPost('start')[$index],
                    'end' => $this->request->getPost('stop')[$index]
                ];
    
                $music_setting = $musicSettingsModel->insert($setting);
                
                if (!$music_setting) {
                    return json_encode(['error' => "Failed to save the music settings."]);
                }
    
                $data['music'][] = $setting;
            }
        }

        $data['tournament_id'] = $tournament_id;

        return json_encode(['msg' => "Success to save the tournament settings.", 'data' => $data]);
    }

    public function getMusicSettings($id)
    {
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $settings = $musicSettingModel->where(['tournament_id' => $id])->findAll();

        return json_encode(['msg' => "Tournament was updated successfully.", 'data' => $settings]);
    }

    public function update($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');

        $tournamentModel->update($id, $this->request->getPost());

        return json_encode(['msg' => "Tournament was updated successfully.", 'data' => $this->request->getPost()]);
    }

    public function updateMusic($tournament_id)
    {
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        foreach ($this->request->getPost('audioType') as $index => $value) {
            $settings = $musicSettingModel->where(['tournament_id' => $tournament_id, 'type' => $value])->findAll();
            
            if (count($settings)) {
                $setting = $settings[0];
            } else {
                $setting = [];
            }

            $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $this->request->getPost('url')[$index];
            
            $setting['path'] = $path;
            $setting['source'] = $this->request->getPost('source')[$index];
            $setting['tournament_id'] = $tournament_id;
            $setting['user_by'] = auth()->user()->id;
            $setting['type'] = $index;
            $setting['duration'] = $this->request->getPost('duration')[$index];
            $setting['start'] = $this->request->getPost('start')[$index];
            $setting['end'] = $this->request->getPost('stop')[$index];

            $musicSettingModel->save($setting);
        }

        return json_encode(['msg' => "Tournament was updated successfully.", 'data' => $this->request->getPost()]);
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
