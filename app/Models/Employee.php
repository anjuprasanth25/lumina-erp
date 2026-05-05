<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'code',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'is_active',
        'company_id',
        'date_of_joining',
        'date_of_leaving',
        'designation_id',
        'department_id',
        'billing_type_id',
        'country_id',
        'created_by',
        'updated_by',
        'line_manager_id'
    ];

    public function details(): HasOne
    {
        return $this->hasOne(EmployeeDetail::class, 'employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function billingType(): BelongsTo
    {
        return $this->belongsTo(BillingType::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function lineManager()
    {
        return $this->belongsTo(Employee::class, 'line_manager_id');
    }


}
