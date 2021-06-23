<?php


namespace Neo\Documents\Contract;


use Neo\Documents\Contract\XLSXComponents\Header;
use Neo\Documents\Contract\XLSXComponents\NetworkOrders;
use Neo\Documents\Contract\XLSXComponents\ProductionFees;
use Neo\Documents\Contract\XLSXComponents\TechnicalSpecs;
use Neo\Documents\Contract\XLSXComponents\Totals;
use Neo\Documents\XLSX\XLSXDocument;

class XLSXProposal extends XLSXDocument {

    public const TYPE_PROPOSAL = 'proposal';
    public const TYPE_CONTRACT = 'contract';

    protected string $documentType;

    protected Customer $customer;
    protected Order $order;

    /**
     * @inheritDoc
     */
    protected function ingest($data): bool {
        [$this->customer, $this->order] = ContractImporter::parse($data);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        $header = new Header($this->order, $this->customer);
        $header->render($this->ws);

        for($i = 0; $i < 26; ++$i) {
            $this->ws->getColumnDimensionByColumn($i)->setWidth(15);
        }

        // Print orderlines for eeach network
        foreach(["shopping", "otg", "fitness"] as $network ) {
            $networkPrinter = new NetworkOrders($network, $this->order);
            $networkPrinter->render($this->ws);
        }

        // Print the totals
        $totalsPrinter = new Totals($this->order);
        $totalsPrinter->render($this->ws);

        // Print the technical specifications
        $specsPrinter = new TechnicalSpecs();
        $specsPrinter->render($this->ws);

        // And the production costs
        $productionFeesPrinter = new ProductionFees($this->order);
        $productionFeesPrinter->render($this->ws);

        // Autosize all columns
//        foreach($this->ws->getColumnDimensions() as $column) {
//            $column->setAutoSize(true);
//        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "Proposal";
    }
}
