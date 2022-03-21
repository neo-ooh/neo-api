<pagebreak/>
<section id="preface">
    <table class="preface-table">
        <tr>
            <td>{{ __("common.header-contract") }}</td>
            <td>{{ $data->contract_name }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-advertiser") }}</td>
            <td>{{ $data->advertiser["name"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-client") }}</td>
            <td>{{ $data->client["name"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-campaign-dates") }}</td>
            <td>{{ $start_date }} / {{ $end_date }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-account-executive") }}</td>
            <td>{{ $data->salesperson["name"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-presented-to") }}</td>
            <td>{{ $data->presented_to["name"] }}</td>
        </tr>
    </table>
</section>
<div class="neo-logo-footer">
    <img class="small-neo-logo"
         src="{{ resource_path('/logos/main.dark.png')  }}" alt="Neo-OOH"/>
</div>
