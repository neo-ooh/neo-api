<x-contract::detailed-summary-production-costs :production="$production" />
<x-contract::detailed-summary-technical-specs />
<section class="summary-right-col">
    <x-contract::totals :orders="$orders" :production="$production" size="small" />
    <x-contract::detailed-summary-notices />
</section>
