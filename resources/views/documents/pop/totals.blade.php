<table class="summary-totals-table" autosize="1">
    <thead>
    <tr>
        <th class="placeholder"></th>
        <th class="placeholder"></th>
        <th class="placeholder"></th>
        <th>{!! __("pop.table-media-value") !!}</th>
        <th>{!! __("pop.table-impressions") !!}</th>
        <th>{!! __("pop.table-net-investment") !!}</th>
        <th>{!! __("pop.table-cpm") !!}</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>{!! __("pop.totals-contracted") !!}</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td>{{ formatCurrency($contract["contracted_media_value"]) }}</td>
            <td>{{ format($contract["contracted_impressions"]) }}</td>
            <td>{{ formatCurrency($contract["net_investment"]) }}</td>
            <td>{{ formatCurrency($contract["contracted_cpm"]) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="placeholder"></td>
            <td class="placeholder"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ __("pop.totals-actual") }}</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td class="media-value">{{ formatCurrency($contract["current_guaranteed_value"])  }}</td>
            <td>{{ format($purchaseReservations->sum("received_impressions")) }}</td>
            <td>-</td>
            <td>-</td>
        </tr>
        @if(count($bonusReservations) > 0)
            <tr>
                <td>{{ __("pop.totals-bonus") }}</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="media-value">{{ formatCurrency($contract["current_bonus_value"])  }}</td>
                <td>{{ format($bonusReservations->sum("received_impressions")) }}</td>
                <td>-</td>
                <td>-</td>
            </tr>
        @endif
        @if(count($buaReservations) > 0)
            <tr>
                <td>{{ __("pop.totals-bua") }}</td>
                <td class="placeholder">-</td>
                <td class="placeholder">-</td>
                <td class="media-value">{{ formatCurrency($contract["current_bua_value"])  }}</td>
                <td>{{ format($buaReservations->sum("received_impressions")) }}</td>
                <td>-</td>
                <td>-</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td>{{ __("common.total") }}</td>
            <td class="placeholder"></td>
            <td class="placeholder"></td>
            <td class="media-value">{{ formatCurrency($contract["current_value"]) }}</td>
            <td>{{ format($contract["total_received_impressions"]) }}</td>
            <td class="placeholder"></td>
            <td>{{ formatCurrency($contract["current_cpm"]) }}</td>
        </tr>
    </tfoot>
</table>
