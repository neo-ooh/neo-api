<section class="summary-purchases">
    <h1 class="detailed-purchases-title">
        {{ __("order-type-$category")  }}
    </h1>
    <table class="summary-purchases-table">
        <thead>
        <tr class="headers">
            <th>Networks</th>
            <th># of properties</th>
            <th># of weeks</th>
            <th># of screens & posters</th>
            <th>Impressions</th>
            <th>Media Value</th>
            <th>Net investment</th>
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
                    <td>{{ number_format($networkOrders->sum('impressions')) }}</td>
                    <td>$ {{ number_format($networkOrders->sum('unit_price')) }}</td>
                    <td>$ {{ number_format($networkOrders->sum(fn($order) => $order->netInvestment())) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td>{{ $orders->groupBy('property_name')->count() }}</td>
                <td>-</td>
                <td>{{ $orders->sum('nb_screens') }}</td>
                <td>{{ number_format($orders->sum('impressions')) }}</td>
                <td>$ {{ number_format($orders->sum('unit_price')) }}</td>
                <td>$ {{ number_format($orders->sum(fn($order) => $order->netInvestment())) }}</td>
            </tr>
        </tfoot>
    </table>
</section>
