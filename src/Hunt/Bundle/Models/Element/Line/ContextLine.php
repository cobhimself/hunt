<?php

namespace Hunt\Bundle\Models\Element\Line;

class ContextLine extends Line
{
    /**
     * @return ContextLineNumber
     */
    public function getLineNumberElement(): LineNumberInterface
    {
        return new ContextLineNumber($this->getLineNumber());
    }
}