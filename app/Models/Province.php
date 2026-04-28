<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinceFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
