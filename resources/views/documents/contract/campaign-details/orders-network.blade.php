<section class="detailed-purchases">
    <h2 class="detailed-purchases-title">
        {{ __("order-type-$type")  }}
        <span class="{{ $network }}"> / {{ __("network-$network") }}</span>
    </h2>
    <table class="detailed-purchases-wrapper" autosize="1">
        <thead>
        <tr><th></th></tr>
        <tr>
            <th>
                <table class="detailed-purchases-table">
                    <tr class="headers">
                        <th class="{{ $order->show_investment ?: "larger" }}">
                            Markets, Properties & Products
                        </th>
                        <th>City</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Spots</th>
                        <th>Screens / Posters</th>
                        <th>Weeks</th>
                        <th>Impressions</th>
                        <th class="{{ $order->show_investment ?: "last" }}">
                            Media Value
                        </th>
                        @if($order->show_investment)
                            <th>Discount %</th>
                            <th>Net Investment</th>
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
            $totalDiscount = 0;
            $totalNetInvestment = 0
        @endphp
        {{-- Loop over each region --}}
        @foreach($orders as $region => $regionOrders)
            @php
                $regionSpots = 0;
                $regionScreens = 0;
                $regionImpressions = 0;
                $regionMediaValue = 0;
                $regionDiscount = 0;
                $regionNetInvestment = 0
            @endphp
            {{-- Print the region row --}}
            <tr class="region-row-wrapper">
                <td>
                    <table class="region-row-table">
                        <tr class="region-row">
                            <td class="{{ $order->show_investment ?: "larger" }}">
                                {{ $regionOrders->first()->first()->market }}
                            </td>
                            <td class="filler {{ $order->show_investment ? "show-investment" : "" }}"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            {{-- Loop over each property in the region --}}
            @foreach($regionOrders as $propertiesOrders)
                @php $isLastProperty = $loop->last; @endphp
                <tr>
                    <td>
                        <table class="property-purchases-table">
                            {{-- Print the property row --}}
                            <tr class="property-row {{ $loop->first ? '' : 'first' }} {{ $network }}">
                                <th class="property-name {{ $network }} {{ $order->show_investment ?: "larger" }}">
                                    {{ $propertiesOrders->first()->property_name }}
                                </th>
                                <th class="property-city">
                                    {{ strlen($propertiesOrders[0]->property_city) > 20 ? substr($propertiesOrders[0]->property_city,0,20)."..." : $propertiesOrders[0]->property_city }}
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
                                    $regionDiscount += $purchase->discount;
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
                                    <td class="border-right">{{ number_format($purchase->impressions) }}</td>
                                    <td class="{{ $order->show_investment ? "border-right" : "" }}">
                                        $ {{ number_format($purchase->media_value) }}
                                    </td>
                                    @if($order->show_investment)
                                        <td class="border-right">
                                            {{ $purchase->discount == 0 ? '-' : "{$purchase->discount}%" }}
                                        </td>
                                        <td>$ {{ number_format($purchase->net_investment) }}</td>
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
                        <tr class="region-footer-row {{ $network }}">
                            <td class="label-row {{ $order->show_investment ?: "larger" }}">
                                Total {{ $regionOrders->first()->first()->market }}</td>
                            <td></td>
                            <td></td>
                            <td class="border-right"></td>
                            <td class="border-right">{{ $regionSpots }}</td>
                            <td class="border-right">{{ $regionScreens }}</td>
                            <td class="border-right">-</td>
                            <td class="border-right">{{ number_format($regionImpressions) }}</td>
                            <td @if($order->show_investment)
                                class="border-right"
                                    @endif >
                                $ {{ number_format($regionMediaValue) }}
                            </td>
                            @if($order->show_investment)
                                <td class="border-right">
                                    {{ $regionDiscount === 0 ? '-' : ($regionDiscount / $regionOrders->flatten()->count()) . "%" }}
                                </td>
                                <td>$ {{ number_format($regionNetInvestment) }}</td>
                            @endif
                        </tr>
                        @php
                            $totalSpots += $regionSpots;
                            $totalScreens += $regionScreens;
                            $totalImpressions += $regionImpressions;
                            $totalMediaValue += $regionMediaValue;
                            $totalDiscount += $regionDiscount;
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
                    <tr class="{{ $network }}">
                        <td class="label-row {{ $order->show_investment ?: "larger" }}">Total {{ $networkName }}</td>
                        <td></td>
                        <td></td>
                        <td class="border-right"></td>
                        <td class="border-right">{{ $totalSpots }}</td>
                        <td class="border-right">{{ $totalScreens }}</td>
                        <td class="border-right">-</td>
                        <td class="border-right">{{ number_format($totalImpressions) }}</td>
                        <td class="{{ $order->show_investment ? "border-right" : "last" }}" >
                            $ {{ number_format($totalMediaValue) }}
                        </td>
                        @if($order->show_investment)
                            <td class="border-right">
                                {{ $totalDiscount === 0 ? '-' : round($totalDiscount / $orders->flatten()->count()) . "%" }}
                            </td>
                            <td>$ {{ number_format($totalNetInvestment) }}</td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
        </tfoot>
    </table>
    <div class="detailed-purchases-footer"></div>
</section>
