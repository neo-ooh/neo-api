<section class="detailed-purchases">
    <h2 class="detailed-purchases-title">
        {{ __("order-type-$type")  }}
        <span class="detailed-purchase-title-network {{ $network }}"> / {{ __("network-$network") }}</span>
    </h2>
    <table class="detailed-purchases-table">
        <thead>
            <tr>
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
            {{-- Loop over each region --}}
            @foreach($purchasesByRegion as $purchasesByProperties)
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
                            <td class="border-right">{{ $purchase->discount == 0 ? '-' : $purchase->discount }}</td>
                            <td>$ {{ $purchase->unit_price * $purchase->quantity * (1 - (int)$purchase->discount / 100) }}</td>
                        </tr>
                    @endforeach
                @endforeach

            @endforeach
        </tbody>
    </table>
</section>
