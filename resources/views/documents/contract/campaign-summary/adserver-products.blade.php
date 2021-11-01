<section class="summary-purchases">
    <h1 class="detailed-purchases-title">
        {{ __("contract.adserver-products")  }}
    </h1>
    <table class="broadcast-periods-table no-space-after">
        <tr>
            <td class="label">
                {{ trans_choice("common.broadcast-periods", $lines->pluck("rangeLengthString")->unique()) }}
            </td>
            <td class="periods-col">
                @foreach($lines->pluck("rangeLengthString")->unique() as $rangeString)
                    @if($loop->index % 2 !== 0)
                        <br />
                    @endif
                    {{ $rangeString }}
                    @if($loop->index % 2 !== 0 && $loop->remaining > 0)
            </td>
            <td class="periods-col">
                @endif
            @endforeach
            </td>
        </tr>
    </table>
    <div class="extension-strategy-cpm">
        CPM: {{ formatCurrency($lines->sum("cpm")) }}
    </div>
    <table class="audience-extension-table">
        <thead>
        <tr class="headers">
            <th>{!! __("contract.table-networks") !!}</th>
            <th>{!! __("contract.table-markets") !!}</th>
            <th>{!! __("contract.table-impressions") !!}</th>
            <th>{!! __("contract.table-cpm") !!}</th>
            <th>{!! __("contract.table-media-value") !!}</th>
            @if($order->show_investment)
                <th>{!! __("contract.table-net-investment") !!}</th>
            @endif
        </tr>
        </thead>
        <tbody>
        <tr class="strategy-type-row">
            <td colspan="{{ $order->show_investment ? 6 : 5 }}">
                {{ __("common.network-adserver") }}
            </td>
        </tr>
            @foreach($lines as $line)
            <tr class="strategy-row">
                <td>{{$line->network}}</td>
                <td>{{$line->market_name}}</td>
                <td>{{$line->impressions}}</td>
                <td>{{formatCurrency($line->cpm)}}</td>
                <td>{{formatCurrency($line->unit_price * $line->nb_weeks)}}</td>
                @if($order->show_investment)
                    <td>{{ formatCurrency($line->subtotal) }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
