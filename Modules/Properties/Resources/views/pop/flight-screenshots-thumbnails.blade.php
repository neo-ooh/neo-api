<section id="pop-flight-screenshots">
    @foreach($screenshots as $screenshot)
        @if($loop->index % 8 === 0 && !$loop->first)
            <pagebreak/>
        @endif
        <div class="pop-flight-screenshots__screenshot {{ $loop->index % 4 === 0 ? "flush-left" : "" }}">
            <div class="pop-flight-screenshots__screenshot__thumbnail"
                 style="background-image: url({!! ($screenshot["url"]) !!})">
            </div>
            <div class="pop-flight-screenshots__screenshot__details">
                <div class="pop-flight-screenshots__screenshot__details__date">
                    {{--                    <img class="screenshot-card-date-icon"--}}
                    {{--                         src="{{ resource_path("images/icons/calendar.png") }}"--}}
                    {{--                         alt="calendar" width="4mm"/>--}}
                    {{--                    <span class="screenshot-card-date-component">--}}
                    {{ $screenshot["received_at"]->format("Y-m-d") }}
                    {{--                    </span>--}}
                    {{--                    <img class="screenshot-card-date-icon"--}}
                    {{--                         src="{{ resource_path("images/icons/clock.png") }}"--}}
                    {{--                         alt="calendar" width="4mm" style="margin-left: 5mm"/>--}}
                    {{--                    <span class="screenshot-card-date-component">--}}
                    {{ $screenshot["received_at"]->format("H:i") }}
                    {{--                    </span>--}}
                </div>
                <div class="pop-flight-screenshots__screenshot__details__location">
                    {{ $screenshot["display_name"] }}<br/>
                    {{ $screenshot["location_name"]  }}
                </div>
            </div>
        </div>
    @endforeach
</section>
