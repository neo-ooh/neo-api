===========================================================
{!! __("email-welcome-title") !!}
===========================================================

{!! __("email-welcome-body", ["name" => $actor->name]) !!}

https://connect.neo-ooh.com/welcome?token={{ $signupToken }}
