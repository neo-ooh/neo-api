<table class="preface">
    <tr>
        <td>{{ __("pop.header-contract") }}</td>
        <td>{{ $contract_name }}</td>
    </tr>
    <tr>
        <td>{{ __("pop.header-advertiser") }}</td>
        <td>{{ $advertiser_name }}</td>
    </tr>
    <tr>
        <td>{{ __("pop.header-client") }}</td>
        <td>{{ $client_name }}</td>
    </tr>
    <tr>
        <td>{{ __("pop.header-dates") }}</td>
        <td>{{ $start_date->toDateString() }} / {{ $end_date->toDateString() }}</td>
    </tr>
    <tr>
        <td>{{ __("pop.header-salesperson") }}</td>
        <td>{{ $salesperson }}</td>
    </tr>
    <tr>
        <td>{{ __("pop.header-presented-to") }}</td>
        <td>{{ $presented_to }}</td>
    </tr>
</table>
