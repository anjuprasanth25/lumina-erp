<x-mail::message>
# Welcome {{ $user->name }}!

Your account has been created. Please click the button below to set up your password before logging in.

<x-mail::button :url="$passwordResetLink">
Set Up Your Password
</x-mail::button>

<x-mail::button :url="config('app.url') . '/management/login'">
Login to Your Account
</x-mail::button>

<x-mail::subcopy>
If you are having trouble clicking the "Set Up Your Password" button, copy and paste the URL below into your web
browser:
[{{ $passwordResetLink }}]({{ $passwordResetLink }})
</x-mail::subcopy>

Thanks,
{{ config('app.name') }}
</x-mail::message>
