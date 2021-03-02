<header>
<table class="header-table">
    <tr>
        <td class="header-company-logo">
            <img class="header-neo-logo"
                 src="{{ resource_path('/logos/main.dark.en@2x.png')  }}" alt="Neo-OOH logo"/>
        </td>
        <td class="header-title">
            {{ $title }}
        </td>
        <td class="header-contract">
            <table class="header-contract-data">
                <tr>
                    <td class="header-contract-data-label">Customer</td>
                    <td class="header-contract-data-value">{{ $customer->parent_name }}</td>
                </tr>
                <tr>
                    <td class="header-contract-data-label">Advertiser</td>
                    <td class="header-contract-data-value">{{ $customer->account }}</td>
                </tr>
                <tr>
                    <td class="header-contract-data-label">Proposal #</td>
                    <td class="header-contract-data-value">{{ $order->reference }}</td>
                </tr>
                <tr>
                    <td class="header-contract-data-label">Presented to</td>
                    <td class="header-contract-data-value">{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td class="header-contract-data-label">Date</td>
                    <td class="header-contract-data-value">{{ $order->date }}</td>
                </tr>
                <tr>
                    <td class="header-contract-data-label">Account Executive</td>
                    <td class="header-contract-data-value">{{ $order->salesperson }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</header>
