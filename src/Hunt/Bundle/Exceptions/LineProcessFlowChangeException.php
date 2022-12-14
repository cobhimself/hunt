<?php

namespace Hunt\Bundle\Exceptions;


use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineInterface;

class LineProcessFlowChangeException extends HuntBaseException
{
    public function __construct(LineInterface $line, int $desiredState)
    {
        $message = sprintf(
            'Cannot go from state "%s" to state "%s" for %s "%s"',
            Line::STATE_FLOW[$line->getState()],
            Line::STATE_FLOW[$desiredState],
            get_class($line),
            $line->getContent()
        );

        parent::__construct($message);
    }
}