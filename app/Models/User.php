<?php

namespace App\Models;

<<<<<<< HEAD
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_BRANCH_ADMIN = 'branch_admin';
    public const ROLE_HR = 'hr';
    public const ROLE_DEPARTMENT_MANAGER = 'department_manager';
    public const ROLE_EMPLOYEE = 'employee';

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'is_active',
        'location_id', 'employee_id',
    ];

    protected $hidden = ['password', 'remember_token'];

>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
<<<<<<< HEAD
        ];
    }
=======
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_user')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === self::ROLE_BRANCH_ADMIN;
    }

    public function isHr(): bool
    {
        return $this->role === self::ROLE_HR;
    }

    public function isDepartmentManager(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT_MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    /** Admin override: Branch Admin or Super Admin */
    public function canOverrideApproval(): bool
    {
        return $this->isSuperAdmin() || $this->isBranchAdmin();
    }

    /** Can manage branch (branch admin for own location, super admin for all) */
    public function canManageBranch(?int $locationId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($locationId === null) {
            return false;
        }
        if ($this->isBranchAdmin() || $this->isHr()) {
            return $this->location_id === $locationId || $this->locations()->where('location_id', $locationId)->exists();
        }
        return false;
    }

    /** Backward compatibility */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_BRANCH_ADMIN], true);
    }
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
}
