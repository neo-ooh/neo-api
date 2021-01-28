<?php

namespace Neo\Documents;

use Mpdf\Mpdf;
use Neo\Documents\Exceptions\UnknownGenerationException;

abstract class Document {
    /**
     * @param mixed $data
     * @return Document
     * @throws UnknownGenerationException
     */
    public static function make($data): Document {
        $document = new static();

        if(!$document->build($data)) {
            throw new UnknownGenerationException();
        }

        return $document;
    }

    /*
     * Internal methods
     */

    protected array $settings;

    protected Mpdf $mpdf;

    protected function __construct() {
        $this->mpdf = new Mpdf();
    }

    /**
     * Holds the entire document generation logic. Each document has to define one.
     *
     * @param mixed $data
     * @return bool A boolean value indicating if the document generation was successful or not
     */
    abstract protected function build($data): bool;
}
