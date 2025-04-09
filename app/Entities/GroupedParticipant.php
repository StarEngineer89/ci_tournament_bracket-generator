<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class GroupedParticipant extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    protected $attributes = [
        'id'            => null,
        'group_id'  => null,
        'participant_id' => null
    ];
}