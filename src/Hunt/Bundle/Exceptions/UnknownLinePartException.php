<?php

namespace Hunt\Bundle\Exceptions;

use Hunt\Bundle\Models\Line\Parts\Match;
use Hunt\Bundle\Models\Line\Parts\Normal;

class UnknownLinePartException extends HuntBaseException
{

    /**
     * UnknownLinePartException constructor.
     *
     * @param mixed $part
     */
    public function __construct($part)
    {
        $msg = sprintf(
            'Unknown part! Instance of %s or %s required; %s given.',
            Normal::class,
            Match::class,
            (is_object($part)) ? get_class($part) : gettype($part)
        );

        parent::__construct($msg);
    }
}