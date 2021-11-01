<section class="contract-home-page">
    <h1>
        @if($order->use_invoice_plan)
            {!! __("contract.payment-terms-confirmation-title") !!}
        @else
            {!! __("contract.contract-number-title", ["number" => $order->reference]) !!}
        @endif
    </h1>
    @if($order->use_invoice_plan)
        <h2 class="center">
            {!! __("contract.contract-number-title", ["number" => $order->reference]) !!}
        </h2>
    @endif
    <h3  @if($order->use_invoice_plan) class="small" @endif >{!! $order->campaign_name !!}</h3>
    <div class="parties">
        <div class="contract-party first-party">
            <div class="contract-party-left-column">{!! __("contract.first-party-title") !!}</div>
            <div class="contract-party-right-column">
                <p class="first-party-name">
                    {!! __("contract.first-party-desc", ["company" => $order->company_name]) !!}
                </p>
                <table class="party-infos">
                    <tr>
                        <td>{!! __("common.phone") !!}</td>
                        <td>{{ $order->salesperson_phone }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("contract.represented-by") !!}</td>
                        <td>{{ $order->salesperson }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("common.email-address") !!}</td>
                        <td>{{ $order->salesperson_email }}</td>
                    </tr>
                </table>
                <span class="party-referred-as">
                    {!! __("contract.first-party-referred-as") !!}
                </span>
            </div>
        </div>
        <div class="contract-party second-party">
            <div class="contract-party-left-column">
                {!! __("contract.second-party-title") !!}
            </div>
            <div class="contract-party-right-column">
                <table class="party-infos">
                    <tr>
                        <td>{!! __("common.customer") !!}</td>
                        <td>{{ $customer->parent_name }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("common.address") !!}</td>
                        <td>{{ $customer->getAddress() }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("common.phone") !!}</td>
                        <td>{{ $customer->phone }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("common.email-address") !!}</td>
                        <td>{{ $customer->email }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("contract.represented-by") !!}</td>
                        <td>{{ $customer->name }}</td>
                    </tr>
                </table>
                <span class="party-referred-as">
                    {!! __("contract.second-party-referred-as") !!}
                </span>
            </div>
        </div>
    </div>
    <div class="costs-recap">
        <p>{!! __("contract.customer-accepts") !!}</p>
        <h2 class="investment-label">{!! __("contract.investment") !!}</h2>
        <table class="costs-recap-table">
            <tr>
                <td>{!! __("contract.total-media-net") !!}</td>
                <td class="value row-1">{{ formatCurrency($order->grand_total_investment) }}</td>
            </tr>
            <tr>
                <td>{!! __("contract.production-costs") !!}</td>
                <td class="value">{{ formatCurrency($order->production_costs) }}</td>
            </tr>
            <tr class="footer">
                <th>{!! __("contract.grand-total-net") !!}</th>
                <th>{{ formatCurrency($order->net_investment) }}</th>
            </tr>
        </table>
    </div>
    <div class="payable-accounts-wrapper">
        <h2>{!! __("contract.payable-accounts") !!}</h2>
        <table class="payable-accounts">
            <tr>
                <td class="email-label">{{ __("common.email-address") }}</td>
                <td class="payable-account-email">
                    {{ $customer->payable_account }}
                </td>
            </tr>
        </table>
    </div>
    <div class="payment-terms">
        <h2>{!! __("contract.payment-terms-title") !!}</h2>
        @if($order->use_invoice_plan)
            <table class="payment-steps">
                @foreach($order->invoice_plan_steps as $step)
                    <tr>
                        <td>{{ formatCurrency($step["amount"]) }}</td>
                        <td>{{ __("contract.billed-on") }}</td>
                        <td>{{ $step["date"]->toDateString() }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
        <ul>
            <li>{!! __("contract.payment-terms-one") !!}</li>
            <li>{!! __("contract.payment-terms-two") !!}</li>
            <li>{!! __("contract.payment-terms-three") !!}</li>
        </ul>
    </div>
    <div class="intervention">
        <h2>{!! __("contract.intervention-title") !!}</h2>
        <p>{!! __("contract.intervention-desc") !!}</p>
    </div>
    <table class="signatures">
        <tr class="header">
            <th colspan="6">{!! __("contract.signatures-title") !!}</th>
        </tr>
        <tr class="client-top-row">
            <td>{!! __("contract.signed-by-client") !!}</td>
            <td class="date-placeholder"></td>
            <td class="v-separator"></td>
            <td class="signed-by">{!! __("contract.signed-by") !!}</td>
            <td class="signature-placeholder"></td>
            <td class="end-spacing"></td>
        </tr>
        <tr class="client-bottom-row">
            <td></td>
            <td class="date">Date</td>
            <td class="v-separator"></td>
            <td class="signed-by-spacer"></td>
            <td class="client-name">{{ $customer->name }}</td>
            <td class="end-spacing"></td>
        </tr>
        <tr class="neo-top-row">
            <td>{!! __("contract.signed-by-neo") !!}</td>
            <td class="date-placeholder"></td>
            <td class="v-separator"></td>
            <td class="signed-by">{!! __("contract.signed-by") !!}</td>
            <td class="signature-placeholder"></td>
            <td class="end-spacing"></td>
        </tr>
        <tr class="neo-bottom-row">
            <td></td>
            <td class="date">Date</td>
            <td class="v-separator"></td>
            <td class="signed-by-spacer"></td>
            <td class="client-name">{{ $order->salesperson }}</td>
            <td class="end-spacing"></td>
        </tr>
    </table>
</section>
