<?php

namespace Hunt\Bundle\Exceptions;


class InvalidElementException extends HuntBaseException
{
    public function __construct($part)
    {
        parent::__construct(sprintf(
            'Invalid element type: %s',
            (is_object($part)) ? get_class($part) : $part
        ));
    }
}