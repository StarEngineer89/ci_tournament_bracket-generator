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
        'increment_score' => null,
        'increment_score_enabled' => null,
        'score_enabled' => null
    ];
}