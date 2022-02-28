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
        <td>{{ formatCurrency($data->guaranteed_media_value) }}</td>
        <td>{{ format($data->guaranteed_contracted_impressions) }}</td>
        <td>{{ formatCurrency($data->guaranteed_net_investment) }}</td>
        <td>{{ formatCurrency($data->guaranteed_cpm) }}</td>
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
        <td class="media-value">{{ formatCurrency($data->counted_guaranteed_media_value)  }}</td>
        <td>{{ format($data->counted_guaranteed_impressions) }}</td>
        <td>-</td>
        <td>-</td>
    </tr>
    @if($data->has_bonus_buys)
        <tr>
            <td>{{ __("pop.totals-bonus") }}</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td class="media-value">{{ formatCurrency($data->counted_bonus_media_value)  }}</td>
            <td>{{ format($data->counted_bonus_impressions) }}</td>
            <td>-</td>
            <td>-</td>
        </tr>
    @endif
    @if($data->has_bua_buys)
        <tr>
            <td>{{ __("pop.totals-bua") }}</td>
            <td class="placeholder">-</td>
            <td class="placeholder">-</td>
            <td class="media-value">{{ formatCurrency($data->counted_bua_media_value)  }}</td>
            <td>{{ format($data->counted_bua_impressions) }}</td>
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
        <td class="media-value">{{ formatCurrency($data->total_counted_media_value) }}</td>
        <td>{{ format($data->total_counted_impressions) }}</td>
        <td class="placeholder"></td>
        <td>{{ formatCurrency($data->total_counted_cpm) }}</td>
    </tr>
    </tfoot>
</table>
