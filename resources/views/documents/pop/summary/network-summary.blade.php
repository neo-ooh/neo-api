<section class="pop-summary">
    <h1 class="summary-network">
        {{ __("common.order-type-$category")  }}
    </h1>
    <table class="summary-network-table">
        <thead>
        <tr class="headers">
            <th>{!! __("pop.table-network") !!}</th>
            <th>{!! __("pop.table-start-date") !!}</th>
            <th>{!! __("pop.table-end-date") !!}</th>
            <th>{!! __("pop.table-contracted-impressions") !!}</th>
            <th>{!! __("pop.table-received-impressions") !!}</th>
            <th>{!! __("pop.table-media-value") !!}</th>
            <th>{!! __("pop.table-net-investment") !!}</th>
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
                    {{ formatCurrency($category === "bua"
                           ? $contract["guaranteed_media_value"] + $contract["bonus_media_value"]
                           : $contract["networks"][$network]["{$category}_media_value"]) }}
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
            <td>{{ format($contract["guaranteed_impressions"]) }}</td>
            <td>{{ format($reservations->sum("received_impressions")) }}</td>
            <td>{{ formatCurrency($contract["guaranteed_media_value"]) }}</td>
            <td>{{ formatCurrency($contract["net_investment"]) }}</td>
        </tr>
        </tfoot>
    </table>
</section>
