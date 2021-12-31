<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string created_by
 * @property int status
 * @property Carbon|null $publish_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'title',
        'content',
        'created_by',
        'publish_date',
        'status'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
}
