<section class="detailed-purchases">
    <h2 class="detailed-purchases-title">
        {{ __("order-type-$type")  }}
        <span class="{{ $network }}"> / {{ __("network-$network") }}</span>
    </h2>
    <table class="detailed-purchases-wrapper" autosize="1">
        <thead>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th>
                <table class="detailed-purchases-table">
                    <tr class="headers {{ $order->show_investment ?: "without-invest" }}">
                        <th>
                            {{ __("contract.table-markets-properties-products") }}
                        </th>
                        <th>
                            {{ __("contract.table-city") }}
                        </th>
                        <th>
                            {{ __("contract.table-start-date") }}
                        </th>
                        <th>
                            {{ __("contract.table-end-date") }}
                        </th>
                        <th>
                            {{ __("contract.table-spots") }}
                        </th>
                        <th>
                            {{ __("contract.table-screens-posters") }}
                        </th>
                        <th>
                            {{ __("contract.table-weeks") }}
                        </th>
                        <th>
                            {{ __("contract.table-impressions") }}
                        </th>
                        <th class="{{ $order->show_investment ?: "last" }}">
                            {{ __("contract.table-media-value") }}
                        </th>
                        @if($order->show_investment)
                            <th>
                                {{ __("contract.table-discount") }}
                            </th>
                            <th class="investment-col">
                                {!! __("contract.table-net-investment") !!}
                            </th>
                        @endif
                    </tr>
                </table>
            </th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalSpots = 0;
            $totalScreens = 0;
            $totalImpressions = 0;
            $totalMediaValue = 0;
            $totalNetInvestment = 0
        @endphp
        {{-- Loop over each region --}}
        @foreach($orders as $region => $regionOrders)
            @php
                $regionSpots = 0;
                $regionScreens = 0;
                $regionImpressions = 0;
                $regionMediaValue = 0;
                $regionNetInvestment = 0
            @endphp
            {{-- Print the region row --}}
            <tr class="region-row-wrapper">
                <td>
                    <table class="region-row-table">
                        <tr class="region-row {{ $order->show_investment ?: "without-invest" }}">
                            <td>
                                {{ __("common.region-" . $regionOrders->first()->first()->market) }}
                            </td>
                            <td class="filler"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            {{-- Loop over each property in the region --}}
            @foreach($regionOrders as $propertiesOrders)
                @php
                    $isLastProperty = $loop->last;
                    $cityCutoff = $order->show_investment ? 20 : 30;
                    $cityName = strlen($propertiesOrders[0]->property_city) > $cityCutoff
                        ? substr($propertiesOrders[0]->property_city, 0, $cityCutoff - 3) . "..."
                        : $propertiesOrders[0]->property_city;
                @endphp
                <tr>
                    <td>
                        <table class="property-purchases-table">
                            {{-- Print the property row --}}
                            <tr class="property-row {{ $loop->first ? '' : 'first' }} {{ $network }} {{ $order->show_investment ?: "without-invest" }}">
                                <th class="property-name {{ $network }}">
                                    {{ $propertiesOrders->first()->property_name }}
                                </th>
                                <th class="property-city">
                                    {{ $cityName }}
                                </th>
                                <th></th>
                                <th class="border-right"></th>
                                <th class="border-right"></th>
                                <th class="border-right"></th>
                                <th class="border-right"></th>
                                <th class="border-right"></th>
                                @if($order->show_investment)
                                    <th class="border-right"></th>
                                    <th class="border-right"></th>
                                @endif
                                <th></th>
                            </tr>
                            {{-- Loop over each purchase for this property --}}
                            @foreach($propertiesOrders as $purchase)
                                @php
                                    $regionSpots += $purchase->quantity;
                                    $regionScreens += $purchase->nb_screens;
                                    $regionImpressions += $purchase->impressions;
                                    $regionMediaValue += $purchase->media_value;
                                    $regionNetInvestment += $purchase->net_investment
                                @endphp

                                <tr class="purchase-row {{ $loop->last && !$isLastProperty ? 'last' : '' }} {{ $isLastProperty && $loop->last ? 'last-of-region' : '' }} {{ $network }}">
                                    <td class="product-type">{{ $purchase->product }}</td>
                                    <td></td>
                                    <td>{{ $purchase->date_start }}</td>
                                    <td class="border-right">{{ $purchase->date_end }}</td>
                                    <td class="border-right">{{ $purchase->quantity }}</td>
                                    <td class="border-right">{{ $purchase->nb_screens }}</td>
                                    <td class="border-right">{{ $purchase->nb_weeks }}</td>
                                    <td class="border-right">{{ format($purchase->impressions) }}</td>
                                    <td class="{{ $order->show_investment ? "border-right" : "" }}">
                                        {{ formatCurrency($purchase->media_value) }}
                                    </td>
                                    @if($order->show_investment)
                                        <td class="border-right">
                                            {{ $purchase->discount == 0 ? '-' : "{$purchase->discount}%" }}
                                        </td>
                                        <td class="investment-col">{{ formatCurrency(round($purchase->net_investment)) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            @endforeach
            {{-- Print the region footer --}}
            <tr class="region-footer-wrapper">
                <td>
                    <table class="region-footer-table">
                        <tr class="region-footer-row {{ $network }} {{ $order->show_investment ?: "without-invest" }}">
                            <td class="label-row">
                                Total {{ $regionOrders->first()->first()->market }}</td>
                            <td></td>
                            <td></td>
                            <td class="border-right"></td>
                            <td class="border-right">{{ $regionSpots }}</td>
                            <td class="border-right">{{ $regionScreens }}</td>
                            <td class="border-right">-</td>
                            <td class="border-right">{{ format($regionImpressions) }}</td>
                            <td @if($order->show_investment)
                                class="border-right"
                                    @endif >
                                {{ formatCurrency($regionMediaValue) }}
                            </td>
                            @if($order->show_investment)
                                <td class="border-right">
                                    @php
                                        $regionDiscount = ($regionMediaValue - $regionNetInvestment) / $regionMediaValue * 100;
                                    @endphp
                                    {{ (int)floor($regionDiscount) === 0 ? '-' : format($regionDiscount) . "%" }}
                                </td>
                                <td class="investment-col">{{ formatCurrency(round($regionNetInvestment)) }}</td>
                            @endif
                        </tr>
                        @php
                            $totalSpots += $regionSpots;
                            $totalScreens += $regionScreens;
                            $totalImpressions += $regionImpressions;
                            $totalMediaValue += $regionMediaValue;
                            $totalNetInvestment += $regionNetInvestment
                        @endphp
                    </table>
                </td>
            </tr>
            <tr class="spacer-row">
                <td></td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="totals-row-wrapper">
            <td>
                <table class="totals-wrapper">
                    <tr class="{{ $network }} {{ $order->show_investment ?: "without-invest" }}">
                        <td class="label-row">Total {{ $networkName }}</td>
                        <td></td>
                        <td></td>
                        <td class="border-right"></td>
                        <td class="border-right">{{ $totalSpots }}</td>
                        <td class="border-right">{{ $totalScreens }}</td>
                        <td class="border-right">-</td>
                        <td class="border-right">{{ format($totalImpressions) }}</td>
                        <td class="{{ $order->show_investment ? "border-right" : "last" }}">
                            {{ formatCurrency($totalMediaValue) }}
                        </td>
                        @if($order->show_investment)
                            <td class="border-right">
                                @php
                                    $totalDiscount = ($totalMediaValue - $totalNetInvestment) / $totalMediaValue * 100;
                                @endphp
                                {{ (int)floor($totalDiscount) === 0 ? '-' : format($totalDiscount) . "%" }}
                            </td>
                            <td class="investment-col">{{ formatCurrency(round($totalNetInvestment)) }}</td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
        </tfoot>
    </table>
    <div class="detailed-purchases-footer"></div>
</section>
