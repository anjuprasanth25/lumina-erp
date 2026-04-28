<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::saving(function ($model) {
            if (Auth::check()) {
                if ($model->isDirty()) {// Only update if data has actually changed
                    if (!$model->exists) {
                        $model->created_by = Auth::id();
                    }
                    $model->updated_by = Auth::id();
                }
            }
        });

        // static::created(function ($model) {
        //     if (Auth::check()) {
        //         $model->created_by = Auth::id();
        //         $model->updated_by = Auth::id();
        //     } else {
        //         // Optional: Set a system ID or leave null if acceptable
        //         $model->created_by = 1; // Example: System Admin
        //     }
        // });

        // static::updated(function ($model) {
        //     if (Auth::check()) {
        //         $model->updated_by = Auth::id();
        //     }
        // });
    }
}
