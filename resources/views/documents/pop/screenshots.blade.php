<pagebreak/>
<section id="screenshots">
    @foreach($screenshots as $screenshot)
        @if($loop->index % 8 === 0 && !$loop->first)
            <pagebreak/>
        @endif
        <div class="screenshot-card {{ $loop->index % 4 === 0 ? "flush-left" : "" }}">
            <div class="thumbnail-wrapper">
                <div class="thumbnail"
                     style="background-image: url({!! ($screenshot->url) !!})"></div>
            </div>
            <div class="info">
                <div class="location">
                    {{ $screenshot->city }}, {{ $screenshot->province }}
                    - {{ $screenshot->format }}
                </div>
                <div class="date">
                    <img class="screenshot-card-date-icon"
                         src="{{ resource_path("images/icons/calendar.png") }}"
                         alt="calendar" width="4mm"/>
                    <span class="screenshot-card-date-component">
                        {{ $screenshot->created_at->format("Y-m-d") }}
                    </span>
                    <img class="screenshot-card-date-icon"
                         src="{{ resource_path("images/icons/clock.png") }}"
                         alt="calendar" width="4mm" style="margin-left: 5mm"/>
                    <span class="screenshot-card-date-component">
                        {{ $screenshot->created_at->format("H:i") }}
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</section>
