<?php

namespace App\Models;

use CodeIgniter\Model;

class TournamentModel extends Model
{
    protected $table = 'tournaments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['name', 'user_id', 'type', 'status', 'searchable', 'archive', 'shuffle_enabled', 'description', 'score_enabled', 'scoring_method', 'score_manual_override', 'score_bracket', 'increment_score_enabled', 'increment_score', 'increment_score_type', 'visibility', 'availability', 'available_start', 'available_end', 'evaluation_method', 'voting_accessibility', 'voting_mechanism', 'max_vote_value', 'voting_retain', 'round_duration_combine', 'allow_host_override', 'pt_image_update_enabled', 'theme', 'winner_audio_everyone', 'max_group_size', 'advance_count', 'participant_manage_metrics', 'host_manage_metrics', 'allow_metric_edits', 'timer_option', 'timer_auto_advance', 'timer_require_scores', 'timer_start_manually', 'timer_start_option', 'round_score_editing', 'round_advance_method', 'round_time_type', 'round_time', 'round_duration', 'ranking', 'tiebreaker', 'score_weight', 'time_weight'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
}