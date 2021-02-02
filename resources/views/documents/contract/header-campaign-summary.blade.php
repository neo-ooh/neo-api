<header>
    <img class="details-header-neo-logo"
         src="{{ resource_path('/logos/main.dark.en@2x.png')  }}" alt="Neo-OOH logo"/>
    <div class="details-header-title-wrapper">
        <span class="details-header-title">Campaign Summary</span>
    </div>
    <table class="details-header-contract-data">
        <tr>
            <td class="details-header-contract-data-label">Customer</td>
            <td class="details-header-contract-data-value">{{ $customer->account }}</td>
        </tr>
        <tr>
            <td class="details-header-contract-data-label">Advertiser</td>
            <td class="details-header-contract-data-value">{{ $customer->company }}</td>
        </tr>
        <tr>
            <td class="details-header-contract-data-label">Proposal #</td>
            <td class="details-header-contract-data-value">{{ $order->reference }}</td>
        </tr>
        <tr>
            <td class="details-header-contract-data-label">Presented to</td>
            <td class="details-header-contract-data-value">{{ $customer->name }}</td>
        </tr>
        <tr>
            <td class="details-header-contract-data-label">Date</td>
            <td class="details-header-contract-data-value">{{ $order->date }}</td>
        </tr>
        <tr>
            <td class="details-header-contract-data-label">Account Executive</td>
            <td class="details-header-contract-data-value">{{ $order->salesperson }}</td>
        </tr>
    </table>
</header>
