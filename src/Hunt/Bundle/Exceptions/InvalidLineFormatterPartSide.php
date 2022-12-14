<?php

namespace Hunt\Bundle\Exceptions;


use Hunt\Bundle\Models\Line\Formatter\BaseLineFormatter;

class InvalidLineFormatterPartSide extends HuntBaseException
{

    public function __construct($side)
    {
        parent::__construct(sprintf(
            '%s is not a valid line formatter side. Valid choices are: %s',
            $side,
            BaseLineFormatter::VALID_SIDES
        ));
    }
}