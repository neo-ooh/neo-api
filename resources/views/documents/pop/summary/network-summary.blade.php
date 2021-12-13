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
        @foreach(['shopping', 'otg', 'fitness'] as $network)
            @php
                $networkReservations = $reservations->where("network", $network)
            @endphp
            @if($networkReservations->count() === 0)
                @continue
            @endif
            <tr class="{{$network}}">
                <td>{{ __("network-$network") }}</td>
                <td>{{ $networkReservations->min("start_date")->format("Y-m-d") }}</td>
                <td>{{ $networkReservations->max("end_date")->format("Y-m-d") }}</td>
                <td>{{ format($contract["networks"][$network]["{$category}_impressions"] ?? 0) }}</td>
                <td>{{ format($networkReservations->sum("received_impressions")) }}</td>
                <td>
                    {{ formatCurrency($category === 'bua' ? $networkReservations->sum("received_impressions") * $contract["bua_impression_value"] : $contract["networks"][$network]["{$category}_media_value"]) }}
                </td>
                <td>{{ formatCurrency($contract["networks"][$network]["{$category}_net_investment"] ?? 0) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td>{{ $reservations->min("start_date")->format("Y-m-d") }}</td>
            <td>{{ $reservations->max("end_date")->format("Y-m-d") }}</td>
            <td>{{ format($contract["{$category}_impressions"]) }}</td>
            <td>{{ format($reservations->sum("received_impressions")) }}</td>
            <td>{{ formatCurrency($category === 'bua' ? $reservations->sum("received_impressions") * $contract["bua_impression_value"] : $contract["{$category}_media_value"]) }}</td>
            <td>{{ formatCurrency($category === "guaranteed" ? $contract["net_investment"] : 0) }}</td>
        </tr>
        </tfoot>
    </table>
</section>
