<?php

namespace Hunt\Bundle\Exceptions;


use Throwable;

class FormatterException extends HuntBaseException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (null !== $previous) {
            $message .= ' Previous exception: ' . $previous->getMessage();
        }

        parent::__construct($message, $code, $previous);
    }
}