<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'order',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function parentModule(): BelongsTo
    {
        return $this->belongsTo(ParentModule::class, 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

