<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeDetailsFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'employee_id',
        'dob',
        'gender',
        'date_of_joining',
        'date_of_leaving',
        'designation_id',
        'department_id',
        'billing_type_id',
        'country_id',
        'family_status'
    ];
}
