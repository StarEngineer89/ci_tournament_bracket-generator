<?php

namespace App\Models;

use CodeIgniter\Model;

class ShareSettingsModel extends Model
{
    protected $table            = 'share_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_by', 'tournament_id', 'target', 'permission', 'token', 'users'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    
    public function tournamentDetails() {
        $this->select('share_settings.*, tournaments.name as name, tournaments.type as type, tournaments.status as status, users.username as username, auth_identities.secret as email');
        $this->join('tournaments', 'share_settings.tournament_id = tournaments.id', 'LEFT');
        $this->join('users', 'share_settings.user_by = users.id', 'LEFT');
        $this->join('auth_identities', 'share_settings.user_by = auth_identities.user_id', 'LEFT');

        return $this;
    }
}