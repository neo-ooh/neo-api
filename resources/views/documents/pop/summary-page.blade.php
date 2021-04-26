<h1 class="head">{!! __("pop.title") !!}</h1>
<div class="subtitle head">{!! __("pop.subtitle", ["contract" => $contract["contract_id"]]) !!}</div>
<section id="summary">
    @includeWhen(count($purchaseReservations) > 0, 'documents.pop.summary.network-summary', ["category" => "guaranteed", "reservations" => $purchaseReservations])
    @includeWhen(count($bonusReservations) > 0, 'documents.pop.summary.network-summary', ["category" => "bonus", "reservations" => $bonusReservations])
    @includeWhen(count($buaReservations) > 0, 'documents.pop.summary.network-summary', ["category" => "bua", "reservations" => $buaReservations])
</section>
@includeWhen(count($contract["reservations"]) > 0, 'documents.pop.totals', [
    "contract" => $contract,
])
