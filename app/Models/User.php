<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $roles = null;
    protected $permissions = null;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_has_roles',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    public function can($abilities, $arguments = []): bool
    {
        if (method_exists($this, 'hasPermissions')) {
            return $this->hasPermissions($abilities);
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        $superAdminRole = config('permission.super_admin_role');
        $roles = $this->getRoles();
        if (is_array($roles) || $roles instanceof \Illuminate\Support\Collection) {
            foreach ($roles as $role) {
                if (!empty($superAdminRole) && $role->id == $superAdminRole) {
                    return true;
                }
            }
        } else {
            return $roles === $superAdminRole;
        }

        return false;
    }

    public function hasPermissions($permissions): bool
    {
        if (!empty($this->is_super_admin)) {
            return true;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if (empty($this->getPermissions())) {
            return false;
        }

        if (is_array($permissions)) {
            return empty(array_diff($permissions, array_keys($this->getPermissions())));
        }

        return in_array($permissions, array_keys($this->getPermissions()));
    }

    public function getPermissions(): array
    {
        if (!isset($this->permissions)) {
            $this->permissions = [];
            /** @var Role $role */
            foreach ($this->getRoles() as $role) {
                /** @var Collection $rows */
                $rows = $role->getPermissions();
                $this->permissions = array_merge($this->permissions, $rows->toArray());
            }
        }

        return $this->permissions;
    }

    public function getRoles()
    {
        if (!isset($this->roles)) {
            $userId = $this->getAuthIdentifier();
            $this->roles = Role::join('user_has_roles', 'user_has_roles.role_id', '=', 'roles.id')
                                    ->where('user_id', $userId)
                                    ->whereNull('roles.deleted_at')
                                    ->whereNull('user_has_roles.deleted_at')
                                    ->get(['roles.id', 'roles.name', 'roles.description', 'roles.created_at', 'roles.updated_at']);
            if (count($this->roles) == 0) {
                $defaultRole = Role::where('id', config('permission.default_role', 2))->first();
                if ($defaultRole) {
                    UserHasRole::create([
                        'user_id' => $userId,
                        'role_id' => $defaultRole->id
                    ]);
                }
                $this->roles = [$defaultRole];
            }
        }
        return $this->roles;
    }
}
