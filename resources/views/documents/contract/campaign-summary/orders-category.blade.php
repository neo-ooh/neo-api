
<section class="summary-purchases">
    <h1 class="detailed-purchases-title">
        {{ __("contract.order-type-$category")  }}
    </h1>
    <table class="summary-purchases-table">
        <thead>
        <tr class="headers">
            <th>{!! __("contract.table-networks") !!}</th>
            <th>{!! __("contract.table-count-of-properties") !!}</th>
            <th>{!! __("contract.table-count-of-weeks") !!}</th>
            <th>{!! __("contract.table-count-of-screens-posters") !!}</th>
            <th>{!! __("contract.table-impressions") !!}</th>
            <th>{!! __("contract.table-media-value") !!}</th>
            @if($order->show_investment)
                <th>{!! __("contract.table-net-investment") !!}</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach(['shopping', 'otg', 'fitness'] as $network)
            @php
                $networkOrders = $orders->filter(fn($order) => $order->isNetwork($network))
            @endphp
            @if($networkOrders->count() === 0)
                @continue
            @endif
            <tr class="{{$network}}">
                <td>{{ __("network-$network") }}</td>
                <td>{{ $networkOrders->groupBy('property_name')->count() }}</td>
                <td>{{ $networkOrders->pluck('nb_weeks')->unique()->join(" & ") }}</td>
                <td>{{ $networkOrders->sum('nb_screens') }}</td>
                <td>{{ format($networkOrders->sum('impressions')) }}</td>
                <td>$ {{ format($networkOrders->sum('media_value')) }}</td>
                @if($order->show_investment)
                    <td>$ {{ format($networkOrders->sum("net_investment")) }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td>{{ $orders->groupBy('property_name')->count() }}</td>
            <td>-</td>
            <td>{{ $orders->sum('nb_screens') }}</td>
            <td>{{ format($orders->sum('impressions')) }}</td>
            <td>$ {{ format($orders->sum('media_value')) }}</td>
            @if($order->show_investment)
                <td>$ {{ format($orders->sum("net_investment")) }}</td>
            @endif
        </tr>
        </tfoot>
    </table>
</section>
