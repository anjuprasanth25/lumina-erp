<?php

namespace App\Models;

use App\Traits\ScopesByRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseClaim extends Model
{
    use HasFactory, SoftDeletes, ScopesByRole;

    protected $fillable = [
        'claim_number',
        'employee_id',
        'company_id',
        'department_id',
        'currency_id',
        'line_manager_id',
        'bu_approver_id',
        'expense_type_id',
        'claim_date',
        'is_billed_to_client',
        'client_name',
        'has_policy_exception',
        'exception_reason',
        'status',
        'total_amount_claim_currency',
        'total_vat_claim_currency',
        'total_amount_local_currency',
        'booking_reference_code',
        'booked_at',
        'booked_by',
        'posted_at',
        'posted_by',
        'verifier_id',
        'verified_at',
        'line_manager_approved_at',
        'bu_approved_at',
        'finance_approver_id',
        'finance_approved_at',
        'payment_method'
    ];

    protected $casts = [
        'claim_date' => 'date',
        'is_billed_to_client' => 'boolean',
        'has_policy_exception' => 'boolean',
        'total_amount_claim_currency' => 'decimal:2',
        'total_vat_claim_currency' => 'decimal:2',
        'total_amount_local_currency' => 'decimal:2',
        'booked_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function lineManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'line_manager_id');
    }

    public function buApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bu_approver_id');
    }

    public function financeApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_approver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseClaimItem::class, 'expense_claim_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ExpenseClaimHistory::class, 'expense_claim_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(user::class, 'employee_id', 'employee_id');
    }
}
