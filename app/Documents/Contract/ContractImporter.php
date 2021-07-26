<?php

namespace Neo\Documents\Contract;

use Illuminate\Support\Facades\App;
use League\Csv\Reader;
use Neo\Documents\Network;

class ContractImporter {

    public static function parse($data) {
        // Data is expected to be a CSV file
        // Read the csv file
        $reader = Reader::createFromString($data);
        $reader->setDelimiter(',');
        $reader->setHeaderOffset(0);

        // Get all records in the file
        $records = $reader->getRecords();

        $customer = null;
        $order    = null;

        // Parse all records
        foreach ($records as $offset => $record) {
            // The first record holds additional informations such as customer and order info
            if ($offset === 1) {
                $customer = new Customer($record);
                $order    = new Order($record);

                $locale = substr($order->locale, 0, 2);
                App::setLocale($locale);
            }

            if(empty($record["order_line"]) && !empty($record["invoice_plan_ids/invoice_move_ids/nb_in_plan"])) {
                $order->addInvoicePlanStep($record);
                continue;
            }

            if(empty($record["order_line"])) {
                // ignore line
                continue;
            }

            $orderLine = new OrderLine($record);

            if ($orderLine->is_production) {
                $order->productionLines->push($orderLine);
                continue;
            }

            if ((int)$orderLine->unit_price === 0 && $orderLine->isNetwork(Network::NEO_OTG)) {
                // -Dans le On the Go, nous avons lié les produits In Screen et Full Screen dans une même propriété. Pourquoi? Parce qu'ils ont le même inventaire. Exemple: il y a 15 spot de dispo. Si un client achète un Digital Full Screen, il reste donc 14 dispos. Il va donc aussi rester 14 dispo autant pour In Screen que pour le Full Screen. Ce sont deux produits differents dans le même écran.
                //Donc, dans Odoo, lorsque j'ajoute, un Full screen (ou vice versa), ca l'ajoute aussi un in screen qui toutefois se n'a aucune valeurs dans cette propositions. Ainsi, dans le cas que ca arrive, il ne faut pas affiher le In screen. En plus, il ne doit pas faire partie des calculs sur la ligne de total.
                //Maintenant, quel champ utilisé. Je crois que le meilleur champs serait: Order Lines/Unit Price. Lorsqu'il est à 0, on affiche pas. Note que ceci est exclusif à On the Go.
                continue;
            }

            if($orderLine->quantity < PHP_FLOAT_EPSILON) {
                continue;
            }

            // Each line holds one Order Line
            $order->orderLines->push($orderLine);
        }

        // All data has been correctly parsed and imported, let's make some calculations right now
        $order->computeValues();

        return [$customer, $order];
    }
}
