<div class="pop-flight-details__titles">
    <span class="pop-flight-details__titles__flight-name">
        {{ strtoupper($flight->flight_name) }}
    </span>
    <span class="pop-flight-details__titles__flight-type">
        {{ __("pop.flight-type-" . $flight->flight_type->value) }}
    </span>
</div>
