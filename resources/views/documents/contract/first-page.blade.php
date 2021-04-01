<section class="contract-home-page">
    <h1>{!! __("contract.contract-number-title", ["number" => $order->reference]) !!}</h1>
    <div class="parties">
        <div class="contract-party first-party">
            <div class="contract-party-left-column">{!! __("contract.first-party-title") !!}</div>
            <div class="contract-party-right-column">
                <p class="first-party-name">
                    {!! __("contract.first-party-desc") !!}
                </p>
                <table class="party-infos">
                    <tr>
                        <td>{!! __("common.phone") !!}</td>
                        <td>[missing column]</td>
                    </tr>
                    <tr>
                        <td>{!! __("contract.represented-by") !!}</td>
                        <td>{{ $order->salesperson }}</td>
                    </tr>
                    <tr>
                        <td>{!! __("common.email-address") !!}</td>
                        <td>[missing column]</td>
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
                        <td>[missing column]</td>
                    </tr>
                    <tr>
                        <td>{!! __("common.email-address") !!}</td>
                        <td>[missing column]</td>
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
    <table class="payable-contact">
        <tr class="header">
            <th colspan="2">
                {!! __("contract.payable-contact-header") !!}
            </th>
        </tr>
        <tr>
            <td>{!! __("common.complete-name") !!}</td>
            <td></td>
        </tr>
        <tr>
            <td>{!! __("common.phone") !!}</td>
            <td></td>
        </tr>
        <tr>
            <td>{!! __("common.email-address") !!}</td>
            <td></td>
        </tr>
        <tr>
            <td>{!! __("common.billing-address") !!}</td>
            <td></td>
        </tr>
        <tr>
            <td>{!! __("common.invoice") !!}</td>
            <td class="invoice-type-cell">
                <label for="contract-type-electronic">{!! __("contract.type-electronic") !!}</label>
                <input type="radio" name="contract-type" id="contract-type-electronic">
                <label for="contract-type-paper">{!! __("contract.type-paper") !!}</label>
                <input type="radio" name="contract-type" id="contract-type-paper">
            </td>
        </tr>
    </table>
    <div class="costs-recap">
        <p>{!! __("contract.customer-accepts") !!}</p>
        <h2 class="investment-label">{!! __("contract.investment") !!}</h2>
        <table class="costs-recap-table">
            <tr>
                <td>{!! __("contract.total-media-net") !!}</td>
                <td class="value row-1">{{ number_format($order->grand_total_investment, 2) }} $</td>
            </tr>
            <tr>
                <td>{!! __("contract.production-costs") !!}</td>
                <td class="value">{{ number_format($order->production_costs, 2) }} $</td>
            </tr>
            <tr class="footer">
                <th>{!! __("contract.grand-total-net") !!}</th>
                <th>{{ number_format($order->net_investment, 2) }} $</th>
            </tr>
        </table>
    </div>
    <div class="payment-terms">
        <h2>{!! __("contract.payment-terms-title") !!}</h2>
        <ul>
            <li>{!! __("contract.payment-terms-one") !!}</li>
            <li><strong>{!! __("contract.payment-terms-two") !!}</strong></li>
            <li>{!! __("contract.payment-terms-three") !!}</li>
            <li>{!! __("contract.payment-terms-four") !!}</li>
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
