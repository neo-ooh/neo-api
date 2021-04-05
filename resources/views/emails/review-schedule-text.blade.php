===========================================================\r\n
{!! __("email-review-schedule-title") !!}\r\n
===========================================================\r\n\r\n

{!! __("email-review-schedule-body", [
"scheduler" => $schedule->owner->name,
"campaign" => $schedule->campaign->name,
"campaignowner" => $schedule->campaign->owner->name,
]) !!}

https://connect.neo-ooh.com/campaigns/{{ $schedule->campaign_id }}#{{ $schedule->id }}
