<table class="detailed-purchases-summary-table" autosize="1">
    <tr class="headers" @if($order->show_investment)
    class="with-invest"
            @endif>
        <td @if(!$order->show_investment)
            class="larger"
                @endif >Total {{ __("order-type-$type") }}</td>
        <td @if(!$order->show_investment)
            class="larger-2"
        @endif></td>
        <td>{{ $totalSpots }}</td>
        <td>{{ $totalScreens }}</td>
        <td>-</td>
        <td>{{ format($totalImpressions) }}</td>
        <td @if(!$order->show_investment)
            class="last"
                @endif>{{ formatCurrency($totalValue) }}</td>
        @if($order->show_investment)
            <td>
                @php
                    $totalDiscount = ($totalValue - $totalInvestment) / $totalValue * 100
                @endphp
                {{ (int)floor($totalDiscount) === 0 ? '-' : format($totalDiscount) . "%" }}
            </td>
            <td class="investment-col">{{ formatCurrency($totalInvestment) }}</td>
        @endif
    </tr>
</table>
