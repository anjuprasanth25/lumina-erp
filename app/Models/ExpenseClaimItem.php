<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseClaimItem extends Model
{
    protected $fillable = [
        'expense_claim_id',
        'expense_category_id',
        'bill_date',
        'invoice_number',
        'supplier_client_name',
        'attendee_employee_names',
        'description',
        'charged_to_type',
        'charged_to_id',
        'item_currency_id',
        'exchange_rate',
        'amount',
        'vat_amount',
        'amount_local_currency',
        'attachment_path',
        'attachment_ref',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'amount_local_currency' => 'decimal:2'
    ];

    public function expenseClaim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'item_currency_id');
    }
}