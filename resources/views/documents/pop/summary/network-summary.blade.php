<section class="pop-summary">
    <h1 class="summary-network">
        {{ __("common.order-type-$category")  }}
    </h1>
    <table class="summary-network-table">
        <thead>
        <tr class="headers">
            <th class="network">{!! __("pop.table-network") !!}</th>
            <th class="start-date">{!! __("pop.table-start-date") !!}</th>
            <th class="end-date">{!! __("pop.table-end-date") !!}</th>
            <th class="contracted-impressions">{!! __("pop.table-contracted-impressions") !!}</th>
            <th class="received-impressions">{!! __("pop.table-received-impressions") !!}</th>
            <th class="media-value">{!! __($category === 'bua' ? "pop.table-media-value-counted" : "pop.table-media-value" ) !!}</th>
            <th class="net-investment">{!! __("pop.table-net-investment") !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($values->networks as $networkValues)
            @php
                $color = [1 => "#3e9cca", 2 => "#d63641", 3 => "#e5782c"][$networkValues->network->id];
            @endphp
            <tr>
                <td style="background-color: {{ $color }}">
                    {{ $networkValues->network->name }}
                </td>
                <td style="background-color: {{ $color }}">
                    {{ $networkValues->start_date->format("Y-m-d") }}
                </td>
                <td style="background-color: {{ $color }}">
                    {{ $networkValues->end_date->format("Y-m-d") }}
                </td>
                <td style="background-color: {{ $color }}">
                    {{ format($networkValues->contracted_impressions) }}
                </td>
                <td style="background-color: {{ $color }}">
                    {{ format($networkValues->counted_impressions) }}
                </td>
                <td style="background-color: {{ $color }}">
                    {{ formatCurrency($category === 'bua'
? ($networkValues->media_value / $networkValues->contracted_impressions) * $networkValues->counted_impressions
: $networkValues->media_value) }}
                </td>
                <td style="background-color: {{ $color }}"> {{ formatCurrency($networkValues->net_investment) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td>{{ $values->start_date->format("Y-m-d") }}</td>
            <td>{{ $values->end_date->format("Y-m-d") }}</td>
            <td>{{ format($values->contracted_impressions) }}</td>
            <td>{{ format($values->counted_impressions) }}</td>
            <td>{{ formatCurrency($category === 'bua'
? ($values->media_value / $values->contracted_impressions) * $values->counted_impressions
: $values->media_value) }}</td>
            <td>{{ formatCurrency($values->net_investment) }}</td>
        </tr>
        </tfoot>
    </table>
</section>
