<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyRoleUser extends Pivot
{
    protected $table = 'company_role_user';

    protected $fillable = [
        'user_id',
        'company_id',
        'role_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
