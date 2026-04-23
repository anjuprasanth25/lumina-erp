<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'code',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'is_active',
        'company_id',
        'created_by',
        'updated_by'
    ];
}
