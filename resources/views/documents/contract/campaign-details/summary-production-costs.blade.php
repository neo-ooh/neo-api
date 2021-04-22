@if($production->count() > 0)
    <h2 class="technical-specs-title">
        {!! __("contract.production-costs") !!}
    </h2>
    <table class="production-specs-table">
        <thead>
        <tr>
            <th>{!! __("contract.table-description") !!}</th>
            <th>{!! __("contract.table-quantity") !!}</th>
            <th>{!! __("contract.table-cost") !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($production as $p)
            <tr>
                <td>{{ substr($p->description, strlen("[production]")) }}</td>
                <td>{{ $p->quantity }}</td>
                <td>$ {{ format($p->subtotal)  }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>{!! __("contract.total") !!}</td>
                <td>{{ $production->sum("quantity") }}</td>
                <td>$ {{ format($production->sum("subtotal")) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
