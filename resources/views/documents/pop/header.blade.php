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
                     src="{{ resource_path('/logos/main.dark.'.$locale.'@2x.png')  }}" alt="Neo-OOH logo"/>
            </td>
            <td class="header-title">
                {{ $title }}
            </td>
        </tr>
    </table>
</header>
