<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'parent_id',
        'name',
        'order',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
