<?php

namespace Neo\Documents\Contract;

use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Neo\Documents\Document;

class Contract extends Document {
    protected Customer $customer;
    protected Order $order;

    /**
     * @var array
     */
    protected array $orderLines;

    protected function build($data): bool {
        $this->ingest($data);
        return false;
    }

    private function ingest($data) {
        // Data is expected to be a CSV file
        // Read the csv file
        $reader = Reader::createFromString($data);
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        // Get all records in the file
        $records = $reader->getRecords();

        // Parse all records
        foreach ($records as $offset => $record) {
            // The first record holds additional informations such as customer and order info
            if($offset === 1) {
                $this->ingestHeaders($record);
            }

            // Each line holds one Order Line
            $this->ingestOrderLine($record);
        }

        dump($this->customer);
        dump($this->order);
    }

    private function ingestHeaders(array $record) {
        $this->customer = new Customer($record);
        $this->order = new Order($record);
    }

    private function ingestOrderLine(array $record) {

    }
}
