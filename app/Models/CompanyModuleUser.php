<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyModuleUser extends Pivot
{
    protected $fillable = [
        'user_id',
        'company_id',
        'module_id',
        'access_level',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

}
