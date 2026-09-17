<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ScopesByRole
{

    public function scopeForUserModuleAccess(
        Builder $query,
        ?Collection $moduleAccessDetails = null,
        $user = null
    ) {
        $user = $user ?? auth()->user();

        $accessDetails = $moduleAccessDetails ?? request()->attributes->get('moduleAccessDetails');

        if (!$accessDetails || $accessDetails->isEmpty()) {
            return $query->where('employee_id', $user->employee_id);
        }

        $adminOrVerifierCompanyIds = $accessDetails
            ->whereIn('role', ['admin', 'verifier'])
            ->pluck('company_id')
            ->toArray();

        $approverCompanyIds = $accessDetails
            ->where('role', ['approver', 'finance-approver'])
            ->pluck('company_id')
            ->toArray();




        $standardCompanyIds = $accessDetails
            ->where('role', 'standard')
            ->pluck('company_id')
            ->toArray();

        return $query->where(function (Builder $mainQuery) use (
            $user,
            $adminOrVerifierCompanyIds,
            $approverCompanyIds,
            $standardCompanyIds
        ) {
            if (!empty($adminOrVerifierCompanyIds)) {
                $mainQuery->orWhereIn('company_id', $adminOrVerifierCompanyIds);
            }

            if (!empty($approverCompanyIds)) {
                $mainQuery->whereIn('company_id', $approverCompanyIds)
                    ->where(function ($q) use ($user) {
                        $q->where('employee_id', $user->employee_id)
                            ->orWhere('line_manager_id', $user->id)
                            ->orWhere('bu_approver_id', $user->id)
                            ->orWhere('finance_approver_id', $user->id)
                            ->orWhere(function ($sub) {
                                $sub->where('status', 'pending_finance_approval');
                            });
                    });
            }

            if (!empty($standardCompanyIds)) {
                $mainQuery->orWhere(function ($q) use ($user, $standardCompanyIds) {
                    $q->whereIn('company_id', $standardCompanyIds)
                        ->where(function ($sub) use ($user) {
                            $sub->where('employee_id', $user->employee_id)
                                ->orwhere('line_manager_id', $user->id)
                                ->orWhere('bu_approver_id', $user->id);
                        });
                });
            }
        });
    }
}
