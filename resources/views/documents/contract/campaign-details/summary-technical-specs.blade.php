{{-- NEO-SHOPPING --}}
@if($order->getShoppingOrders()->count() > 0)
    <h2 class="technical-specs-title">
        {!! __("contract.technical-specs") !!}
        <span class="shopping">/ {!! __("contract.network-shopping") !!}</span>
    </h2>
    <table class="technical-specs-table">
        <thead>
        <tr>
            <th>{!! __("contract.table-product") !!}</th>
            <th>{!! __("contract.table-spot-duration") !!}</th>
            <th>{!! __("contract.table-loop-duration") !!}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{!! __("contract.products-digital-horizontal") !!}</td>
            <td>15 sec.</td>
            <td>5 min.</td>
        </tr>
        <tr>
            <td>{!! __("contract.products-digital-vertical") !!}</td>
            <td>10 sec.</td>
            <td>70 sec.</td>
        </tr>
        <tr>
            <td>{!! __("contract.products-digital-spectacular") !!}</td>
            <td>15 sec.</td>
            <td>5 min.</td>
        </tr>
        <tr>
            <td>{!! __("contract.products-digital-spectacular") !!}</td>
            <td>10 sec.</td>
            <td>70 sec.</td>
        </tr>
        </tbody>
    </table>
@endif
@if($order->getOTGOrders()->count() > 0)
    {{-- NEO-On the Go --}}
    <h2 class="technical-specs-title">
        {!! __("contract.technical-specs") !!}
        <span class="otg">/ {!! __("contract.network-otg") !!}</span>
    </h2>
    <table class="technical-specs-table">
        <thead>
        <tr>
            <th>{!! __("contract.table-product") !!}</th>
            <th>{!! __("contract.table-spot-duration") !!}</th>
            <th>{!! __("contract.table-loop-duration") !!}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{!! __("contract.products-digital-horizontal-indoor") !!}</td>
            <td>15 sec.</td>
            <td>3 min.</td>
        </tr>
        <tr>
            <td>{!! __("contract.products-digital-in-screen") !!}</td>
            <td>15 sec.</td>
            <td>4 min.</td>
        </tr>
        <tr>
            <td>{!! __("contract.products-digital-full-screen") !!}</td>
            <td>15 sec.</td>
            <td>4 min.</td>
        </tr>
        <tr>
            <td>{!! __("products-digital-outdoor-vertical") !!}</td>
            <td>10 sec.</td>
            <td>70 sec.</td>
        </tr>
        </tbody>
    </table>
@endif
@if($order->getFitnessOrders()->count() > 0)
    {{-- NEO-FITNESS --}}
    <h2 class="technical-specs-title">
        {!! __("contract.technical-specs") !!}
    </h2>
    <table class="technical-specs-table">
        <thead>
        <tr>
            <th>{!! __("contract.table-product") !!}</th>
            <th>{!! __("contract.table-spot-duration") !!}</th>
            <th>{!! __("contract.table-loop-duration") !!}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{!! __("contract.products-digital-horizontal") !!}</td>
            <td>15 sec.</td>
            <td>10 min.</td>
        </tr>
        <tr>
            <td>{!! __("contract.products-digital-vertical") !!}</td>
            <td>10 sec.</td>
            <td>70 sec.</td>
        </tr>
        </tbody>
    </table>
@endif
