<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = [
        'name',
        'code',
        'currency_id',
        'base_currency_id',
        'country_id',
        'is_active'
    ];

}
