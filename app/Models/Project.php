<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];

    public function expenseclaimItems(): MorphMany
    {
        $this->morphMany(ExpenseClaimItem::class, 'charged_to_type');
    }
}
