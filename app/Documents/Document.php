<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Document.php
 */

namespace Neo\Documents;

use Neo\Documents\Exceptions\UnknownGenerationException;

abstract class Document {
    public static function make($data): Document {
        $document = new static();

        if (!$document->ingest($data)) {
            throw new UnknownGenerationException();
        }

        return $document;
    }


    /**
     * Holds the data ingestion logic.
     *
     * @param $data
     * @return bool A boolean value indicating if the data was correctly ingested or not
     */
    abstract protected function ingest($data): bool;

    /**
     * Holds the entire document generation logic. Each document has to define one.
     *
     * @return bool A boolean value indicating if the document generation was successful or not
     */
    abstract public function build(): bool;

    /**
     * @return string The name of the generated document
     */
    abstract public function getName(): string;

    /**
     * Must return the mime/type of the built document
     * @return string
     */
    abstract public function format(): string;

    /**
     * @return mixed The raw built document, ready to be shown in the browser
     */
    abstract public function output();
}
