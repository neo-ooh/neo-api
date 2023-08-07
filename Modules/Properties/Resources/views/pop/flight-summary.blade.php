<section class="pop-flight-summary">
    <div class="pop-flight-summary__titles">
        <span class="pop-flight-summary__titles__flight-name">
            {{ strtoupper($flight->flight_name) }}
        </span>
        <span class="pop-flight-summary__titles__flight-type">
            {{ __("pop.flight-type-" . $flight->flight_type->value) }}
        </span>
    </div>
    <table autosize="1"
           class="pop-flight-summary__table">
        <thead>
        <tr>
            <th class="pop-flight-summary__header pop-flight-summary__header__network">
                {{ __("pop.flight-network")  }}
            </th>
            <th class="pop-flight-summary__header pop-flight-summary__header__start-date">
                {{ __("pop.flight-start-date")  }}
            </th>
            <th class="pop-flight-summary__header pop-flight-summary__header__end-date">
                {{ __("pop.flight-end-date")  }}
            </th>
            <th class="pop-flight-summary__header pop-flight-summary__header__contracted-impressions">
                {{ __("pop.flight-contracted-impressions")  }}
            </th>
            <th class="pop-flight-summary__header pop-flight-summary__header__counted-impressions">
                {{ __("pop.flight-counted-impressions")  }}
            </th>
            <th class="pop-flight-summary__header pop-flight-summary__header__media-value">
                {{ __("pop.flight-media-value")  }}
            </th>
            <th class="pop-flight-summary__header pop-flight-summary__header__net-investment">
                {{ __("pop.flight-net-investment")  }}
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($networks as $network)
            <tr>
                <td class="pop-flight-summary__cell pop-flight-summary__network"
                    style="background-color: {{$network["color"]}}">
                    {{ $network["name"] }}
                </td>
                <td class="pop-flight-summary__cell pop-flight-summary__start-date"
                    style="background-color: {{$network["color"]}}">
                    {{ $network["start_date"] }}
                </td>
                <td class="pop-flight-summary__cell pop-flight-summary__end-date"
                    style="background-color: {{$network["color"]}}">
                    {{ $network["end_date"] }}
                </td>
                <td class="pop-flight-summary__cell pop-flight-summary__contracted-impressions"
                    style="background-color: {{$network["color"]}}">
                    {{ formatNumber($network["contracted_impressions"]) }}
                </td>
                <td class="pop-flight-summary__cell pop-flight-summary__counted-impressions"
                    style="background-color: {{$network["color"]}}">
                    {{ formatNumber(round($network["counted_impressions"])) }}
                </td>
                <td class="pop-flight-summary__cell pop-flight-summary__media-value"
                    style="background-color: {{$network["color"]}}">
                    {{ formatCurrency(round($network["media_value"])) }}
                </td>
                <td class="pop-flight-summary__cell pop-flight-summary__net-investment"
                    style="background-color: {{$network["color"]}}">
                    {{ formatCurrency(round($network["net_investment"])) }}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td class="pop-flight-summary__footer">
            </td>
            <td class="pop-flight-summary__footer">
            </td>
            <td class="pop-flight-summary__footer">
            </td>
            <td class="pop-flight-summary__footer pop-flight-summary__footer__counted-impressions">
                {{ formatNumber($totals["contracted_impressions"]) }}
            </td>
            <td class="pop-flight-summary__footer pop-flight-summary__footer__counted-impressions">
                {{ formatNumber($totals["counted_impressions"]) }}
            </td>
            <td class="pop-flight-summary__footer pop-flight-summary__footer__counted-impressions">
                {{ formatCurrency($totals["media_value"]) }}
            </td>
            <td class="pop-flight-summary__footer pop-flight-summary__footer__counted-impressions">
                {{ formatCurrency($totals["net_investment"]) }}
            </td>
        </tr>
        </tfoot>
    </table>
</section>
