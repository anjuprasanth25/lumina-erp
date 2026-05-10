<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRoleUser extends Model
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
}
