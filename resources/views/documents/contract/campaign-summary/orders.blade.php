@if($purchaseOrders->count() > 0)
    <x-contract::summary-orders-category category="purchase"
                                         :orders="$purchaseOrders"
                                         :order="$order"/>
@endif
@if($bonusOrders->count() > 0)
    <x-contract::summary-orders-category category="bonus"
                                         :orders="$bonusOrders"
                                         :order="$order" />
@endif
@if($buaOrders->count() > 0)
    <x-contract::summary-orders-category category="bua"
                                         :orders="$buaOrders"
                                         :order="$order" />
@endif
