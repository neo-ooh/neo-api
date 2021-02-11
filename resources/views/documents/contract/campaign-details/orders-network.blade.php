<section class="detailed-purchases">
    <table class="detailed-purchases-table">
        <thead>
        <tr class="title">
            <td colspan="11" class="detailed-purchases-title">
                {{ __("order-type-$type")  }}
                <span class="{{ $network }}"> / {{ __("network-$network") }}</span>
            </td>
        </tr>
        <tr class="headers">
            <th>Markets, Properties & Products</th>
            <th>City</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Spots</th>
            <th>Screens / Posters</th>
            <th>Weeks</th>
            <th>Impressions</th>
            <th>Media Value</th>
            <th>Discount %</th>
            <th>Net Investment</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalSpots = 0;
            $totalScreens = 0;
            $totalImpressions = 0;
            $totalMediaValue = 0;
            $totalDiscount = 0;
            $totalNetInvestment = 0;
        @endphp
        {{-- Loop over each region --}}
        @foreach($orders as $purchasesByProperties)
            @php
                $regionSpots = 0;
                $regionScreens = 0;
                $regionImpressions = 0;
                $regionMediaValue = 0;
                $regionDiscount = 0;
                $regionNetInvestment = 0;
            @endphp

            {{-- Print the region row --}}
            <tr class="region-row">
                <td>{{ $purchasesByProperties->first()->first()->market }}</td>
            </tr>

            {{-- Loop over each property in the region --}}
            @foreach($purchasesByProperties as $purchases)
                {{-- Print the property row --}}
                <tr class="property-row {{ $loop->first ? '' : 'first' }} {{ $network }}">
                    <td class="property-name {{ $network }}">{{ $purchases->first()->property_name }}</td>
                    <td class="property-city">{{ $purchases[0]->property_city }}</td>
                    <td></td>
                    <td class="border-right"></td>
                    <td class="border-right"></td>
                    <td class="border-right"></td>
                    <td class="border-right"></td>
                    <td class="border-right"></td>
                    <td class="border-right"></td>
                    <td class="border-right"></td>
                    <td></td>
                </tr>

                {{-- Loop over each purchase for this property --}}
                @foreach($purchases as $purchase)
                    @php
                        $regionSpots += (int)$purchase->quantity;
                        $regionScreens += (int)$purchase->nb_screens;
                        $regionImpressions += (int)$purchase->impressions;
                        $regionMediaValue += (int)$purchase->unit_price;
                        $regionDiscount += (int)$purchase->discount;
                        $regionNetInvestment += (int)$purchase->netInvestment();
                    @endphp

                    <tr class="purchase-row {{ $loop->last ? 'last' : '' }} {{ $network }}">
                        <td class="product-type">{{ $purchase->product }}</td>
                        <td></td>
                        <td>{{ $purchase->date_start }}</td>
                        <td class="border-right">{{ $purchase->date_end }}</td>
                        <td class="border-right">{{ $purchase->quantity }}</td>
                        <td class="border-right">{{ $purchase->nb_screens }}</td>
                        <td class="border-right">{{ $purchase->nb_weeks }}</td>
                        <td class="border-right">{{ number_format($purchase->impressions) }}</td>
                        <td class="border-right">$ {{ $purchase->unit_price }}</td>
                        <td class="border-right">{{ $purchase->discount == 0 ? '-' : "{$purchase->discount}%" }}</td>
                        <td>$ {{ $purchase->netInvestment() }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Print the region footer --}}
            <tr class="region-footer-row {{ $network }}">
                <td class="label-row">Total {{ $purchasesByProperties->first()->first()->market }}</td>
                <td></td>
                <td></td>
                <td class="border-right"></td>
                <td class="border-right">{{ $regionSpots }}</td>
                <td class="border-right">{{ $regionScreens }}</td>
                <td class="border-right">-</td>
                <td class="border-right">{{ number_format($regionImpressions) }}</td>
                <td class="border-right">$ {{ $regionMediaValue }}</td>
                <td class="border-right">{{ $regionDiscount === 0 ? '-' : $regionDiscount->discount }}</td>
                <td>$ {{ $regionNetInvestment }}</td>
            </tr>

            @php
                $totalSpots += $regionSpots;
                $totalScreens += $regionScreens;
                $totalImpressions += $regionImpressions;
                $totalMediaValue += $regionMediaValue;
                $totalDiscount += $regionDiscount;
                $totalNetInvestment += $regionNetInvestment;
            @endphp

            <tr class="spacer-row">
                @for($i = 0; $i < 11; ++$i)
                    <td></td>
                @endfor
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="{{ $network }}">
            <td class="label-row">Total {{ $networkName }}</td>
            <td></td>
            <td></td>
            <td class="border-right"></td>
            <td class="border-right">{{ $totalSpots }}</td>
            <td class="border-right">{{ $totalScreens }}</td>
            <td class="border-right">-</td>
            <td class="border-right">{{ number_format($totalImpressions) }}</td>
            <td class="border-right">$ {{ $totalMediaValue }}</td>
            <td class="border-right">{{ $totalDiscount === 0 ? '-' : $totalDiscount->discount }}</td>
            <td>$ {{$totalNetInvestment}}</td>
        </tr>
        </tfoot>
    </table>
</section>
