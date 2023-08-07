<table class="pop-flight-details__table">
    <thead>
    <tr>
        @php
            $style = "border-bottom-color: #". $network->toned_down_color;
        @endphp
        <td class="pop-flight-details__level-header pop-flight-details__level-header__label"
            style="{{ $style }}">
            {{ $header["label"] }}
        </td>
        <td class="pop-flight-details__level-header pop-flight-details__level-header__start-date"
            style="{{ $style }}">
            {{__("pop.flight-start-date")}}
        </td>
        <td class="pop-flight-details__level-header pop-flight-details__level-header__end-date"
            style="{{ $style }}">
            {{__("pop.flight-end-date")}}
        </td>
        <td class="pop-flight-details__level-header pop-flight-details__level-header__contracted-impressions"
            style="{{ $style }}">
            {{__("pop.flight-contracted-impressions")}}
        </td>
        <td class="pop-flight-details__level-header pop-flight-details__level-header__counted-impressions"
            style="{{ $style }}">
            {{__("pop.flight-counted-impressions")}}
        </td>
        <td class="pop-flight-details__level-header pop-flight-details__level-header__media-value"
            style="{{ $style }}">
            {{__("pop.flight-delivered-media-value")}}
        </td>
        <td class="pop-flight-details__level-header pop-flight-details__level-header__cpm"
            style="{{ $style }}">
            {{__("pop.flight-cpm")}}
        </td>
    </tr>
    </thead>
    <tbody>
    @foreach($lines as $line)
        @php
            $lineClass = "pop-flight-details__level-" . $line["level"];
        @endphp
        <tr>
            <td class="{{ $lineClass }} {{$lineClass}}__label pop-flight-details__cell pop-flight-details__cell__label">
                @switch($line["level"])
                    @case(-1)
                        @break
                    @default
                        {{ $line["label"] }}
                @endswitch
            </td>
            <td class="{{ $lineClass }} {{$lineClass}}__start-date pop-flight-details__cell pop-flight-details__cell__start-date">
                @switch($line["level"])
                    @case(1)
                        {{ $flight->start_date }}
                        @break
                    @case(2)
                        -
                        @break
                @endswitch
            </td>
            <td class="{{ $lineClass }} {{$lineClass}}__media-value pop-flight-details__cell pop-flight-details__cell__end-date">
                @switch($line["level"])
                    @case(1)
                        {{ $flight->end_date }}
                        @break
                    @case(2)
                        -
                        @break
                @endswitch
            </td>
            <td class="{{ $lineClass }} {{$lineClass}}__contracted-impressions pop-flight-details__cell pop-flight-details__cell__contracted-impressions">
                {{ formatNumber((int)$line["contracted_impressions"]) }}
            </td>
            <td class="{{ $lineClass }} {{$lineClass}}__counted-impressions pop-flight-details__cell pop-flight-details__cell__counted-impressions">
                {{ formatNumber((int)$line["counted_impressions"]) }}
            </td>
            <td class="{{ $lineClass }} {{$lineClass}}__media-value pop-flight-details__cell pop-flight-details__cell__media-value">
                {{ formatCurrency($line["media_value"]) }}
            </td>
            <td class="{{ $lineClass }} {{$lineClass}}__cpm pop-flight-details__cell pop-flight-details__cell__cpm">
                {{ formatCurrency($line["cpm"]) }}
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td class="pop-flight-details__level-footer pop-flight-details__level-header__label">
        </td>
        <td class="pop-flight-details__level-footer pop-flight-details__level-footer__start-date">
        </td>
        <td class="pop-flight-details__level-footer pop-flight-details__level-footer__end-date">
        </td>
        <td class="pop-flight-details__level-footer pop-flight-details__level-footer__contracted-impressions">
            {{ formatNumber($footer["contracted_impressions"]) }}
        </td>
        <td class="pop-flight-details__level-footer pop-flight-details__level-footer__counted-impressions">
            {{ formatNumber($footer["counted_impressions"]) }}
        </td>
        <td class="pop-flight-details__level-footer pop-flight-details__level-footer__media-value">
            {{ formatCurrency($footer["media_value"]) }}
        </td>
        <td class="pop-flight-details__level-footer pop-flight-details__level-footer__cpm">
            {{ formatCurrency($footer["cpm"]) }}
        </td>
    </tr>
    </tfoot>
</table>
