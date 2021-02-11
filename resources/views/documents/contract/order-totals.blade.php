<table class="summary-totals-table {{ $size }}">
    <thead>
    <tr>
        <th>TOTAL</th>
        @if($size === 'full')
            <th class="placeholder"></th>
            <th class="placeholder"></th>
            <th class="placeholder"></th>
        @endif
        <th>Impressions</th>
        <th>Media Value</th>
        @if($size === 'small')
            <th>Discount</th>
        @endif
        <th>Net Investment</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Guaranteed Media Total</td>
        @if($size === 'full')
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
        @endif
        <td>{{ number_format($guaranteedImpressions) }}</td>
        <td>$ {{ number_format($guaranteedValue) }}</td>
        @if($size === 'small')
            <td>{{ $guaranteedDiscount === 0 ? "-" : "{$guaranteedDiscount}%"}}</td>
        @endif
        <td class="investment">$ {{ number_format($guaranteedInvestment) }}</td>
    </tr>
    @if($hasBua)
        <tr>
            <td>Bonus Upon Availability Total</td>
            @if($size === 'full')
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
            @endif
            <td>{{ number_format($buaImpressions) }}</td>
            <td>$ {{ number_format($buaValue) }}</td>
            @if($size === 'small')
                <td>{{ $buaDiscount === 0 ? "-" : "{$buaDiscount}%"}}</td>
            @endif
            <td class="investment">$ 0</td>
        </tr>
        <tr class="grand-total">
            <td>(Potential) Grand Media Total</td>
            @if($size === 'full')
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
            @endif
            <td>{{ number_format($guaranteedImpressions + $buaImpressions) }}</td>
            <td>$ {{ number_format($guaranteedValue + $buaValue) }}</td>
            @if($size === 'small')
                <td> ??? %</td>
            @endif
            <td class="investment">$ {{ number_format($guaranteedInvestment) }}</td>
        </tr>
    @endif
    <tr>
        <td>Production Cost</td>
        @if($size === 'full')
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
        @endif
        <td>-</td>
        <td>-</td>
        @if($size === 'small')
            <td>-</td>
        @endif
        <td class="investment">$ ???</td>
    </tr>
    <tr>
        <td>Net Investment</td>
        @if($size === 'full')
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
        @endif
        <td>-</td>
        <td>-</td>
        @if($size === 'small')
            <td>-</td>
        @endif
        <td class="investment">$ {{ number_format($guaranteedValue) }} + ???</td>
    </tr>
    @if($size === 'small')
        <tr class="cpm">
            <td>CPM: ???</td>
        </tr>
    @endif
    </tbody>
</table>
