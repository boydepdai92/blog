<?php

namespace App\Repositories\Criteria;

class PostCriteria extends RequestCriteria
{
    protected $criteriaFields = [
        'status' => 'status',
        'from_time' => 'publish_date',
        'to_time'   => 'publish_date'
    ];
}
