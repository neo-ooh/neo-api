@if($type !== 'purchase')
    <pagebreak />
@endif
<x-contract::detailed-orders-table :type="$type" network="shopping" :purchases="$orders"/>
<x-contract::detailed-orders-table :type="$type" network="otg" :purchases="$orders"/>
<x-contract::detailed-orders-table :type="$type" network="fitness" :purchases="$orders"/>
<table class="detailed-purchases-summary-table">
    <tr class="headers">
        <td>Total {{ __("order-type-$type") }}</td>
        <td></td>
        <td>{{ $totalSpots }}</td>
        <td>{{ $totalScreens }}</td>
        <td>-</td>
        <td>{{ number_format($totalImpressions) }}</td>
        <td>$ {{ number_format($totalValue) }}</td>
        <td>{{ $totalDiscount == 0 ? '-' : "{$totalDiscount}%" }}</td>
        <td>$ {{ number_format($totalInvestment) }}</td>
    </tr>
</table>
