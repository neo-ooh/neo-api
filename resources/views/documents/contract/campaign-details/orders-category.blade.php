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
        <td>{{ format($totalImpressions) }}</td>
        <td @if(!$order->show_investment)
            class="last"
                @endif>$ {{ format($totalValue) }}</td>
        @if($order->show_investment)
            <td>
                @php
                    $totalDiscount = ($totalValue - $totalInvestment) / $totalValue * 100;
                @endphp
                {{ (int)floor($totalDiscount) === 0 ? '-' : format($totalDiscount) . "%" }}
            </td>
            <td>$ {{ format($totalInvestment) }}</td>
        @endif
    </tr>
</table>
