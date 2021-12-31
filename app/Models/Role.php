<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Role
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description'
    ];


    public function permissions(): BelongsToMany
    {
        $relation = $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');

        $relation->select(['permissions.id', 'permissions.name', 'permissions.alias', 'permissions.display_name', 'permissions.description', 'permissions.dependencies', 'permissions.created_at', 'permissions.updated_at']);
        return $relation->withTimestamps();
    }

    public function getPermissions(): Collection
    {
        /** @var Collection $permissions */
        return $this->permissions()->pluck('permissions.name', 'permissions.alias');
    }
}
