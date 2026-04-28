<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDetail extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeDetailsFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'employee_id',
        'dob',
        'gender',
        'family_status',
        'created_by',
        'updated_by'
    ];



    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


}
