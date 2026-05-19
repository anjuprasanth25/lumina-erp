<?php

namespace App\Filament\Management\Resources\EmployeeResource\Pages;

use App\Filament\Management\Resources\EmployeeResource;
use App\Models\CompanyRoleUser;
use App\Models\Role;
use App\Models\User;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Save the Employee normally
        $employee = static::getModel()::create($data);

        $user = User::create([
            'name' => trim($employee->first_name . ' ' . $employee->last_name),
            'email' => $employee->email,
            'password' => Hash::make('Welcome123!'),//Hash::make(Str::random(12)),
            'employee_id' => $employee->id,
            'is_active' => true,
        ]);

        $role = Role::where('code', 'general_access')->first();

        if ($role) {
            CompanyRoleUser::create([
                'company_id' => $employee->company_id,
                'role_id' => $role->id,
                'user_id' => $user->id
            ]);
        }

        return $employee;
    }

    protected function afterCreate(): void
    {
        $employee = $this->record;
        $user = User::where('employee_id', $employee->id)->first();

        if ($user) {
            // 2. Generate the token
            $token = Password::getRepository()->create($user);

            // 3. Send the notification
            $user->sendPasswordResetNotification($token);
        }
    }

}
