<x-contract::detailed-summary-production-costs :production="$production"/>
<x-contract::detailed-summary-technical-specs/>
<section class="summary-right-col">
    <x-contract::totals :order="$order" :orders="$orders" :production="$production" size="small"/>
    @if($renderDisclaimers)
        <x-contract::detailed-summary-notices/>
    @endif
</section>
