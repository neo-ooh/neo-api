<table class="summary-totals-table {{ $size }}" autosize="1">
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
        @if($size === 'small' && $showInvestment)
            <th>Discount</th>
        @endif
        <th>Net Investment</th>
    </tr>
    </thead>
    <tbody>
    @if($orders->count() > 0)
        <tr>
            <td>Guaranteed Media Total</td>
            @if($size === 'full')
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
            @endif
            <td>{{ number_format($guaranteedImpressions) }}</td>
            <td>$ {{ number_format($guaranteedValue) }}</td>
            @if($size === 'small' && $showInvestment)
                <td>
                    @php
                        $guaranteedDiscount = ($guaranteedValue - $guaranteedInvestment) / $guaranteedValue * 100;
                    @endphp
                    {{ round($guaranteedDiscount) === 0 ? '-' : number_format($guaranteedDiscount) . "%" }}
                </td>
            @endif
            <td class="investment">
                @if($showInvestment)
                    $ {{ number_format($guaranteedInvestment) }}
                @else
                    -
                @endif
            </td>
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
                @if($size === 'small' && $showInvestment)
                    <td> 100% </td>
                @endif
                <td class="investment">
                    @if($showInvestment)
                        $ 0
                    @else
                        -
                    @endif
                </td>
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
                @if($size === 'small' && $showInvestment)
                    <td>
                        @php
                            $potentionValue = $buaValue + $guaranteedValue;
                            $potentionDiscount = ($potentionValue - $guaranteedInvestment) / $potentionValue * 100;
                        @endphp
                        {{ round($potentionDiscount) === 0 ? '-' : number_format($potentionDiscount) . "%" }}
                    </td>
                @endif
                <td class="investment">
                    @if($showInvestment)
                        $ {{ number_format($grandTotalInvestment) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endif
    @endif
    @if($production->count() > 0)
        <tr>
            <td>Production Cost</td>
            @if($size === 'full')
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
            @endif
            <td>-</td>
            <td>-</td>
            @if($size === 'small' && $showInvestment)
                <td>-</td>
            @endif
            <td class="investment">
                $ {{ number_format($production->sum("subtotal")) }}
            </td>
        </tr>
    @endif
    @if($orders->count() > 0)
        <tr>
            <td>Net Investment</td>
            @if($size === 'full')
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
            @endif
            <td>-</td>
            <td>-</td>
            @if($size === 'small' && $showInvestment)
                <td>-</td>
            @endif
            <td class="investment">
                $ {{ number_format($grandTotalInvestment + ($production->count() > 0 ? $production->sum("subtotal") : 0)) }}</td>
        </tr>
    @endif
    @if($size === 'small' && $orders->count() > 0)
        <tr class="cpm">
            <td>CPM:
                $ {{ number_format($grandTotalInvestment / ($guaranteedImpressions + $buaImpressions) * 1000, 2) }}</td>
        </tr>
    @endif
    </tbody>
</table>
