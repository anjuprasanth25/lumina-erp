<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasAuditColumns;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasAuditColumns;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function companymoduleUsers(): HasMany
    {
        return $this->hasMany(CompanyModuleUser::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasModuleAccess($module_id, $company_id)
    {
        $hascompanyAccess = $this->companymoduleUsers()
            ->where('company_id', $company_id)
            ->whereNull('module_id')
            ->exists();

        if (!$hascompanyAccess)
            return false;

        //check if the user has access to this module in companny_module_user
        $hasModuleAcess = $this->companymoduleUsers()
            ->where('company_id', $company_id)
            ->where('module_id', $module_id)
            ->exists();

        if ($hasModuleAcess)
            return true;

        // 2. Check if the module is in one of the user's Roles
        return $hasModuleInRole = $this->roles()
            ->whereHas('modules', fn($q) => $q->where('modules.id', $module_id))
            ->exists();



    }
}
