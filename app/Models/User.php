<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\WelcomeOnboardNotification;
use App\Traits\HasAuditColumns;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasAuditColumns, CanResetPassword;

    protected ?Collection $moduleCache = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'is_admin',
        'employee_id'
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

    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->is_active)
            return false;

        if ($panel->getId() == 'admin') {
            return $this->isSystemAdmin();
        }

        return $this->isSystemAdmin() || $this->companyRoleAssignments()->exists();
    }

    //function to check whethet the current user has access to this module code
    public function hasModule(string $moduleSlug)
    {
        if ($this->isSystemAdmin())
            return true;

        return $query = $this->companyRoleAssignments()
            ->whereHas('role.modules', function ($query) use ($moduleSlug) {
                $query->where('modules.slug', $moduleSlug);
            })
            ->exists();
    }

    public function isSystemAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function getModuleMetadata(string $slug)
    {
        if ($this->moduleCache === null) {
            //get the module details for all the modules the usr has access to
            $this->moduleCache = Module::whereHas(
                'roles',
                function ($query) {
                    $query->whereIn('roles.id', $this->companyRoleAssignments()->pluck('role_id'));
                }
            )->with('parentModule')
                ->get()
                ->keyBy('slug');
        }

        return $this->moduleCache->get($slug);

    }
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


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // public function roles(): BelongsToMany
    // {
    //     return $this->belongsToMany(Role::class);
    // }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_role_user')
            ->withPivot(['role_id', 'module_id'])
            ->withTimestamps();
    }

    public function companyRoleAssignments()
    {
        return $this->hasMany(CompanyRoleUser::class, 'user_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new WelcomeOnboardNotification($token));
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

}
