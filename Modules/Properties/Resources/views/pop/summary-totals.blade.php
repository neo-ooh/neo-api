<div class="pop-totals-disclaimer">
    {{ __("pop.capped-impressions-disclaimer") }}
</div>
<table autosize="1"
       class="pop-summary-totals__table">
    <thead>
    <tr>
        <th class="pop-summary-totals__header__flight">
        </th>
        <th class="pop-summary-totals__header__start-date">
        </th>
        <th class="pop-summary-totals__header__end-date">
        </th>
        <th class="pop-summary-totals__header pop-summary-totals__header__media-value">
            {{ __("pop.flight-media-value")  }}
        </th>
        <th class="pop-summary-totals__header pop-summary-totals__header__impressions">
            {{ __("pop.flight-impressions")  }}
        </th>
        <th class="pop-summary-totals__header pop-summary-totals__header__net-investment">
            {{ __("pop.flight-net-investment")  }}
        </th>
        <th class="pop-summary-totals__header pop-summary-totals__header__cpm">
            {{ __("pop.flight-cpm")  }}
        </th>
    </tr>
    </thead>
    <tbody>
    <tr class="pop-summary-totals__contracted-row">
        <td class="pop-summary-totals__cell pop-summary-totals__flight">
            {{ __("pop.contracted") }}
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__start-date">
            -
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__end-date">
            -
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__media-value">
            {{ formatNumber($contracted["media_value"]) }}
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__impressions">
            {{ formatNumber($contracted["impressions"]) }}
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__net-investment">
            {{ formatCurrency($contracted["net_investment"]) }}
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__cpm">
            {{ formatCurrency($contracted["cpm"]) }}
        </td>
    </tr>
    <tr>
        <td class="pop-summary-totals__cell pop-summary-totals__flight">
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__start-date">
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__end-date">
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__media-value">
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__impressions">
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__net-investment">
        </td>
        <td class="pop-summary-totals__cell pop-summary-totals__cpm">
        </td>
    </tr>
    @foreach($flights as $flight)
        <tr>
            <td class="pop-summary-totals__cell pop-summary-totals__flight">
                {{ $flight["name"] }}
            </td>
            <td class="pop-summary-totals__cell pop-summary-totals__start-date">
                -
            </td>
            <td class="pop-summary-totals__cell pop-summary-totals__end-date">
                -
            </td>
            <td class="pop-summary-totals__cell pop-summary-totals__media-value">
                {{ formatNumber((int)$flight["media_value"]) }}
            </td>
            <td class="pop-summary-totals__cell pop-summary-totals__impressions">
                {{ formatNumber($flight["counted_impressions"]) }}
            </td>
            <td class="pop-summary-totals__cell pop-summary-totals__net-investment">
                -
            </td>
            <td class="pop-summary-totals__cell pop-summary-totals__cpm">
                -
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td class="pop-summary-totals__footer pop-summary-totals__flight">
            {{ __("pop.totals") }}
        </td>
        <td class="pop-summary-totals__footer">
        </td>
        <td class="pop-summary-totals__footer">
        </td>
        <td class="pop-summary-totals__footer pop-summary-totals__footer__media-value">
            {{ formatNumber((int)$totals["media_value"]) }}
        </td>
        <td class="pop-summary-totals__footer pop-summary-totals__footer__impressions">
            {{ formatNumber($totals["counted_impressions"]) }}
        </td>
        <td class="pop-summary-totals__footer pop-summary-totals__footer__net-investment">
        </td>
        <td class="pop-summary-totals__footer pop-summary-totals__footer__cpm">
            {{ formatCurrency($totals["cpm"]) }}
        </td>
    </tr>
    </tfoot>
</table>
