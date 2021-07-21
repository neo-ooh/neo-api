===========================================================
{!! __("email-end-of-schedule-title") !!}
===========================================================

{!! __("email-end-of-schedule-body_text", [
    "name" => $actor->name,
    "campaign" => $schedule->campaign->name,
    "date" => $schedule->end_date->toDayDateTimeString(),
]) !!}

https://connect.neo-ooh.com/campaigns/{{ $schedule->campaign->owner_id }}/{{ $schedule->campaign_id }}

{!! __("email-legals", ["date" => date("Y")]) !!}© {{ date("Y") }}
