<section id="pop-flight-screenshots">
    @foreach($mockups as $mockup)
        @if($loop->index % 4 === 0 && !$loop->first)
            <pagebreak/>
        @endif
        <div class="pop-flight-screenshots__mockup">
            <div class="pop-flight-screenshots__mockup__picture"
                 style="background-image: url({!! ($mockup["url"]) !!})">
            </div>
            <div class="pop-flight-screenshots__mockup__details">
                <div class="pop-flight-screenshots__mockup__details__date">
                    {{--                    <img class="screenshot-card-date-icon"--}}
                    {{--                         src="{{ resource_path("images/icons/calendar.png") }}"--}}
                    {{--                         alt="calendar" width="4mm"/>--}}
                    {{--                    <span class="screenshot-card-date-component">--}}
                    {{ $mockup["received_at"]->format("Y-m-d") }}
                    {{--                    </span>--}}
                    {{--                    <img class="screenshot-card-date-icon"--}}
                    {{--                         src="{{ resource_path("images/icons/clock.png") }}"--}}
                    {{--                         alt="calendar" width="4mm" style="margin-left: 5mm"/>--}}
                    {{--                    <span class="screenshot-card-date-component">--}}
                    {{ $mockup["received_at"]->format("H:i") }}
                    {{--                    </span>--}}
                </div>
                <div class="pop-flight-screenshots__mockup__details__location">
                    {{ $mockup["display_name"] }}<br/>
                    {{ $mockup["location_name"]  }}
                </div>
            </div>
        </div>
    @endforeach
</section>
