<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    protected $fieldSearchable = [
        'status',
        'publish_date' => 'between'
    ];

    public function model(): string
    {
        return Post::class;
    }
}
