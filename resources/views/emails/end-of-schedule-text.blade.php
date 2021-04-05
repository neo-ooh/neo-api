===========================================================\r\n
{!! __("email-end-of-schedule-title") !!}\r\n
===========================================================\r\n\r\n

{!! __("email-end-of-schedule-body_text", [
    "name" => $dest->name,
    "campaign" => $schedule->campaign->name,
    "date" => $schedule->end_date->toDayDateTimeString(),
]) !!}\r\n\r\n

https://connect.neo-ooh.com/campaigns/{{ $schedule->campaign->owner_id }}/{{ $schedule->campaign_id }}\r\n\r\n\r\n

{!! __("email-legals", ["date" => date("Y")]) !!}© {{ date("Y") }}
