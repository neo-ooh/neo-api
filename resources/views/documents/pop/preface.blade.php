<pagebreak />
<section id="preface">
    <table class="preface-table">
        <tr>
            <td>{{ __("common.header-contract") }}</td>
            <td>{{ $contract["contract_id"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-advertiser") }}</td>
            <td>{{ $contract["advertiser"]["name"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-client") }}</td>
            <td>{{ $contract["client"]["name"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-campaign-dates") }}</td>
            <td>{{ $start_date }} / {{ $end_date }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-account-executive") }}</td>
            <td>{{ $contract["executive"] }}</td>
        </tr>
        <tr>
            <td>{{ __("common.header-presented-to") }}</td>
            <td>{{ $contract["presented_to"] }}</td>
        </tr>
    </table>
</section>
<div class="neo-logo-footer">
    <img class="small-neo-logo"
         src="{{ resource_path('/logos/main.dark.'.$contract["locale"].'.svg')  }}" alt="Neo-OOH"/>
</div>
