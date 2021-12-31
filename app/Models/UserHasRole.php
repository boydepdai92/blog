<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * UserHasRoles
 *
 * @property int $id
 * @property int role_id
 * @property int user_id
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserHasRole extends Model
{
    use SoftDeletes;

    protected $table = 'user_has_roles';

    protected $fillable = [
        'role_id',
        'user_id'
    ];
}
