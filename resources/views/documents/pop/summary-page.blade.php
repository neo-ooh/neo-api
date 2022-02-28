<section id="summary">
    @includeWhen($data->has_guaranteed_buys, 'documents.pop.summary.network-summary', ["category" => "guaranteed", "values" => $guaranteed_values])
    @includeWhen($data->has_bonus_buys, 'documents.pop.summary.network-summary', ["category" => "bonus", "values" => $bonus_values])
    @includeWhen($data->has_bua_buys, 'documents.pop.summary.network-summary', ["category" => "bua", "values" => $bua_values])
</section>
@includeWhen(count($data->values) > 0, 'documents.pop.totals', [
    "data" => $data,
])
