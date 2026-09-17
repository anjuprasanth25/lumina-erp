<?php

namespace App\Policies;

use App\Models\ExpenseClaim;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpenseClaimPolicy
{
    public const MODULE_SLUG = 'expense_claim';

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExpenseClaim $claim): bool
    {
        if ($user->employee_id && $claim->employee_id === $user->employee_id) {
            return true;
        }

        if (in_array($user->id, [$claim->line_manager_id, $claim->bu_approver_id, $claim->finance_approver_id])) {
            return true;
        }

        return $user->hasModulerole($claim->company_id, self::MODULE_SLUG, ['verifier', 'approver', 'admin', 'finance-approver']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExpenseClaim $claim): bool
    {
        return ($user->employee_id && $claim->employee_id === $user->employee_id
            && in_array($claim->status, ['draft', 'pending_verification', 'rejected']));
    }

    public function verify(User $user, ExpenseClaim $claim): bool
    {
        return $user->hasModulerole($claim->company_id, self::MODULE_SLUG, ['verifier']);
    }

    public function approve(User $user, ExpenseClaim $claim): bool
    {
        if ($claim->status != 'pending_approval')
            return false;

        if (in_array($user->id, [$claim->line_manager_id, $claim->bu_approver_id, $claim->finance_approver_id]))
            return true;

        return $user->hasModulerole($claim->company_id, self::MODULE_SLUG, ['approver']);
    }

    public function financeApprove(User $user, ExpenseClaim $claim): bool
    {
        if ($claim->status !== 'pending_finance_approval') {
            return false;
        }

        if ($user->id === $claim->finance_approver_id) {
            return true;
        }

        return $user->hasModuleRole($claim->company_id, self::MODULE_SLUG, ['finance-approver']);
    }

    public function book(User $user, ExpenseClaim $claim): bool
    {
        if ($claim->status !== 'approved') {
            return false;
        }

        return $user->hasModuleRole($claim->company_id, self::MODULE_SLUG, ['finance', 'finance-approver']);
    }

    public function post(User $user, ExpenseClaim $claim): bool
    {
        if ($claim->status !== 'booked') {
            return false;
        }

        return $user->hasModuleRole($claim->company_id, self::MODULE_SLUG, ['finance', 'finance-approver']);
    }

    public function financeView(User $user, ExpenseClaim $claim): bool
    {
        if (! in_array($claim->status, ['booked', 'posted'], true)) {
            return false;
        }

        return $user->hasModuleRole($claim->company_id, self::MODULE_SLUG, ['finance', 'finance-approver']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExpenseClaim $claim): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExpenseClaim $claim): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExpenseClaim $claim): bool
    {
        return false;
    }
}
