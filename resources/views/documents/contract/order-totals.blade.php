<table class="summary-totals-table {{ $size }}" autosize="1">
    <thead>
    <tr>
        <th></th>
        @if($size === 'full')
            <th class="placeholder"></th>
            <th class="placeholder"></th>
            <th class="placeholder"></th>
        @endif
        <th>{!! __("contract.table-impressions") !!}</th>
        <th>{!! __("contract.table-media-value") !!}</th>
        @if($size === 'small' && $showInvestment)
            <th>{!! __("contract.table-discount") !!}</th>
        @endif
        <th>{!! __("contract.table-net-investment") !!}</th>
    </tr>
    </thead>
    <tbody>
    @if($orders->count() > 0)
        <tr>
            <td>{!! __("contract.totals-guaranteed-media-total") !!}</td>
            @if($size === 'full')
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
            @endif
            <td>{{ format($guaranteedImpressions) }}</td>
            <td>{{ formatCurrency(round($guaranteedValue)) }}</td>
            @if($size === 'small' && $showInvestment)
                <td>
                    {{ round($guaranteedDiscount) === 0.0 ? '-' : format($guaranteedDiscount) . "%" }}
                </td>
            @endif
            <td class="investment">
                @if($showInvestment)
                    {{ formatCurrency($guaranteedInvestment) }}
                @else
                    -
                @endif
            </td>
        </tr>
        @if($hasBua)
            <tr>
                <td>{!! __("contract.totals-bua-total") !!}</td>
                @if($size === 'full')
                    <td class="placeholder">-</td>
                    <td class="placeholder">-</td>
                    <td class="placeholder">-</td>
                @endif
                <td>{{ format($buaImpressions) }}</td>
                <td>{{ formatCurrency(round($buaValue)) }}</td>
                @if($size === 'small' && $showInvestment)
                    <td> 100% </td>
                @endif
                <td class="investment">
                    @if($showInvestment)
                        {{ formatCurrency(0) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr class="grand-total">
                <td>{!! __("contract.totals-potential-total") !!}</td>
                @if($size === 'full')
                    <td class="placeholder">-</td>
                    <td class="placeholder">-</td>
                    <td class="placeholder">-</td>
                @endif
                <td>{{ format($guaranteedImpressions + $buaImpressions) }}</td>
                <td>{{ formatCurrency(round($guaranteedValue + $buaValue)) }}</td>
                @if($size === 'small' && $showInvestment)
                    <td>
                        {{ (int)round($potentialDiscount) === 0 ? '-' : format($potentialDiscount) . "%" }}
                    </td>
                @endif
                <td class="investment">
                    @if($showInvestment)
                        {{ formatCurrency($grandTotalInvestment) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endif
    @endif
    @if($production->count() > 0)
        <tr>
            <td>{!! __("contract.totals-production-cost") !!}</td>
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
                {{ formatCurrency($productionCosts) }}
            </td>
        </tr>
    @endif
    @if($orders->count() > 0)
        <tr>
            <td>{!! __("contract.table-net-investment") !!}</td>
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
                {{ formatCurrency($grandTotalInvestment + $productionCosts) }}</td>
        </tr>
    @endif
    @if($size === 'small' && $orders->count() > 0)
        <tr class="cpm">
            <td>CPM:
                {{ formatCurrency($cpm, 2) }}</td>
        </tr>
    @endif
    </tbody>
</table>
