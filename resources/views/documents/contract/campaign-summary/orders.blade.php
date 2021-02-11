@if($purchaseOrders->count() > 0)
    <x-contract::summary-orders-category category="purchase" :orders="$purchaseOrders" />
@endif
@if($bonusOrders->count() > 0)
    <x-contract::summary-orders-category category="bonus" :orders="$bonusOrders" />
@endif
@if($buaOrders->count() > 0)
    <x-contract::summary-orders-category category="bua" :orders="$buaOrders" />
@endif
