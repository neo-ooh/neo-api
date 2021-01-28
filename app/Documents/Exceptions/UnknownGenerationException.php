<?php

namespace Neo\Documents\Exceptions;

use Neo\Exceptions\BaseException;

class UnknownGenerationException extends BaseException {
    protected $code = "documents.generation-error";
    protected $message = "An unknown error happened while generating the document.";
}
