===========================================================\r\n
{!! __("email-review-schedule-title") !!}\r\n
===========================================================\r\n\r\n

{!! __("email-review-schedule-body_text", [
    "reviewer" => $schedule->owner->name,
    "campaign" => $schedule->campaign->name,
]) !!}

https://connect.neo-ooh.com/campaigns/{{ $schedule->campaign_id }}#{{ $schedule->id }}
