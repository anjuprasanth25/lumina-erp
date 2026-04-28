<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModule extends Model
{
    /** @use HasFactory<\Database\Factories\ParentModuleFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'name',
        'order',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
