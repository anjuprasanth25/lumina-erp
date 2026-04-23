<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingType extends Model
{
    /** @use HasFactory<\Database\Factories\BillingTypeFactory> */
    use HasFactory, HasAuditColumns;

    protected $fillable = ['name', 'code'];

}
