@if($production->count() > 0)
    <h2 class="technical-specs-title">
        Production Cost
    </h2>
    <table class="production-specs-table">
        <thead>
        <tr>
            <th>Description</th>
            <th>Quantity</th>
            <th>Cost</th>
        </tr>
        </thead>
        <tbody>
        @foreach($production as $p)
            <tr>
                <td>{{ substr($p->description, strlen("[production]")) }}</td>
                <td>{{ $p->nb_screens }}</td>
                <td>$ {{ number_format($p->subtotal)  }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td>{{ $production->sum("nb_screens") }}</td>
                <td>$ {{ number_format($production->sum("subtotal")) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
