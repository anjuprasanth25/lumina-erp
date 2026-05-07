<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make(Str::random(16));

        return $data;
    }

    protected function afterCreate()
    {
        $user = $this->record;

        // Create a secure token using Laravel's core password broker
        $token = Password::getRepository()->create($user);

        $user->sendPasswordResetNotification($token);
    }
}
