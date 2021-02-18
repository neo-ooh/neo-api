@if($type !== 'purchase')
    <pagebreak/>
@endif
<x-contract::detailed-orders-table :type="$type"
                                   :order="$order"
                                   network="shopping"
                                   :purchases="$orders"/>
<x-contract::detailed-orders-table :type="$type"
                                   :order="$order"
                                   network="otg"
                                   :purchases="$orders"/>
<x-contract::detailed-orders-table :type="$type"
                                   :order="$order"
                                   network="fitness"
                                   :purchases="$orders"/>
<table class="detailed-purchases-summary-table" autosize="1">
    <tr class="headers">
        <td @if(!$order->show_investment)
            class="larger"
                @endif >Total {{ __("order-type-$type") }}</td>
        <td></td>
        <td>{{ $totalSpots }}</td>
        <td>{{ $totalScreens }}</td>
        <td>-</td>
        <td>{{ number_format($totalImpressions) }}</td>
        <td @if(!$order->show_investment)
            class="last"
                @endif>$ {{ number_format($totalValue) }}</td>
        @if($order->show_investment)
            <td>
                {{ $totalDiscount == 0 ? '-' : round($totalDiscount / $orders->flatten()->count())."%" }}
            </td>
            <td>$ {{ number_format($totalInvestment) }}</td>
        @endif
    </tr>
</table>
