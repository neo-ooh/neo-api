<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnsupportedDocumentFormatException.php
 */

namespace Neo\Exceptions\Documents;

use Neo\Documents\DocumentFormat;
use Neo\Exceptions\BaseException;

class UnsupportedDocumentFormatException extends BaseException {
    public function __construct(public DocumentFormat $invalidFormat, public array $supportFormats) {
        $supportedFormtatsStr = implode(",", array_map(fn(DocumentFormat $format) => $format->name, $this->supportFormats));
        parent::__construct("Invalid format $this->invalidFormat, supported formats are $supportedFormtatsStr");
    }
}
