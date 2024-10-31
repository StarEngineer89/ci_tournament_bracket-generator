<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Tournament extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];    
    protected $attributes = [
        'id' => null,
        'name' => null,
        'user_id' => null,
        'type' => null,
        'searchable' => null,
        'archive' => null,
        'shuffle_enabled' => null,
        'description' => null,
        'score_bracket' => null,
        'score_enabled' => null, 
        'score_bracket', 
        'increment_score_enabled' => null, 
        'increment_score' => null, 
        'increment_score_type', 
        'visibility', 
        'availability', 
        'available_start', 
        'available_end', 
        'evaluation_method', 
        'voting_accessibility', 
        'voting_mechanism', 
        'max_vote_value', 
        'voting_retain', 
        'allow_host_override',
        'pt_image_update_enabled'
    ];
}