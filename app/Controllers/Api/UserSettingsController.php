<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UserSettingsController extends BaseController
{
    protected $userSettingsModel;
    public function __construct() {
        $this->userSettingsModel = model('App\Models\UserSettingModel');
    }

    public function index()
    {
        $settings = $this->userSettingsModel->where('user_id', auth()->user()->id)->findAll();
        return $this->response->setJson($settings);
    }

    public function show($id = null)
    {
        $setting = $this->userSettingsModel->find($id);
        if (!$setting) {
            return $this->response->setJson(['status' => 'error', 'msg' => 'Setting not found']);
        }
        return $this->response->setJson($setting);
    }

    public function save($id = null)
    {
        $data = $this->request->getRawInput();
        
        foreach ($data as $key => $value) {
            if ($setting = $this->userSettingsModel->where(['user_id' => auth()->user()->id, 'setting_name' => $key])->first()) {
                $setting['setting_value'] = $value;
            } else {
                $setting = new \App\Entities\UserSetting($data);
                $setting->user_id = auth()->user()->id;
                $setting->setting_name = $key;
                $setting->setting_value = $value;
            }
            
            if (!$this->userSettingsModel->save($setting)) {
                return $this->response->setJson(['status' => 'error', 'msg' => 'Failed to save the setting']);
                
            }
        }
        
        return $this->response->setJson(['status' => 'success', 'setting' => $setting]);
    }

    public function delete($id = null)
    {
        if ($this->userSettingsModel->delete($id)) {
            return $this->response->setJson(['status' => 'success']);
        }
        return $this->response->setJson(['status' => 'error', 'msg' => 'Setting not found']);
    }
}