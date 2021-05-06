@php
    if(!function_exists("format")) {
        function format($number) {
            $locale = \Illuminate\Support\Facades\App::currentLocale();
            $nbrfmtr =new NumberFormatter($locale, NumberFormatter::DECIMAL);

            if($locale === 'fr') {
                $nbrfmtr->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, ' ');
            }

            $output = $nbrfmtr->format($number);
            return str_replace(" "," ",$output);
        }

        function formatCurrency($number) {
            $locale = \Illuminate\Support\Facades\App::currentLocale();
            $nbrfmtr =new NumberFormatter($locale, NumberFormatter::CURRENCY);

            $nbrfmtr->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '$');

            $output = $nbrfmtr->format($number);

            // Remove decimals if zero.
            return str_replace([
                $nbrfmtr->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL)."00",
                " "
            ], ["", " "], $output);
        }
    }

    $locale = App::currentLocale()
@endphp
<header>
    <table class="header-table">
        <tr>
            <td class="header-company-logo">
                <img class="header-neo-logo"
                     src="{{ resource_path('/logos/main.dark.'.$locale.'.svg')  }}" alt="Neo-OOH logo"/>
            </td>
            <td class="header-title">
                {{ $title }}
            </td>
            <td class="header-contract">
                <table class="header-contract-data">
                    <tr>
                        <td class="header-contract-data-label">{!! __("contract.header-customer") !!}</td>
                        <td class="header-contract-data-value">{{ $customer->parent_name }}</td>
                    </tr>
                    <tr>
                        <td class="header-contract-data-label">{!! __("contract.header-advertiser") !!}</td>
                        <td class="header-contract-data-value">{{ $customer->account }}</td>
                    </tr>
                    <tr>
                        <td class="header-contract-data-label">{!! __("contract.header-proposal") !!}</td>
                        <td class="header-contract-data-value">{{ $order->reference }}</td>
                    </tr>
                    <tr>
                        <td class="header-contract-data-label">{!! __("contract.header-presented-to") !!}</td>
                        <td class="header-contract-data-value">{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <td class="header-contract-data-label">{!! __("contract.header-date") !!}</td>
                        <td class="header-contract-data-value">{{ $order->date }}</td>
                    </tr>
                    <tr class="last">
                        <td class="header-contract-data-label">{!! __("contract.header-account-executive") !!}</td>
                        <td class="header-contract-data-value">{{ $order->salesperson }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</header>
