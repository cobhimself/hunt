<?php

namespace Hunt\Bundle\Exceptions;


use Hunt\Bundle\Models\Line\Parts\PartInterface;

class InvalidFormatLinePart extends HuntBaseException
{
    public function __construct(PartInterface $part)
    {
        parent::__construct(sprintf(
            'No known formatter for part type %s. Please define one in BaseLineFormatter class.',
            get_class($part)
        ));
    }
}