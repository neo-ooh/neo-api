<header class="pop-header">
    <img class="pop-header__logo"
         src="{{ resource_path('/logos/main.dark.png')  }}"
         alt="Neo-OOH logo"/>
    <div class="pop-header__title">{{ $title }}</div>
    <table class="pop-header__contract">
        <tr>
            <td class="pop-header__contract__label">{!! __("pop.header-contract") !!}</td>
            <td class="pop-header__contract__value">{{ $contract_name }}</td>
        </tr>
        <tr>
            <td class="pop-header__contract__label">{!! __("pop.header-advertiser") !!}</td>
            <td class="pop-header__contract__value">{{ $advertiser }}</td>
        </tr>
        <tr>
            <td class="pop-header__contract__label">{!! __("pop.header-client") !!}</td>
            <td class="pop-header__contract__value">{{ $client }}</td>
        </tr>
        <tr>
            <td class="pop-header__contract__label last">{!! __("pop.header-salesperson") !!}</td>
            <td class="pop-header__contract__value last">{{ $salesperson }}</td>
        </tr>
    </table>
</header>
