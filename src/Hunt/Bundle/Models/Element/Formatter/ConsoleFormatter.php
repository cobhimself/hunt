<?php

namespace Hunt\Bundle\Models\Element\Formatter;


use Hunt\Bundle\Exceptions\FormatterException;
use Hunt\Bundle\Exceptions\InvalidElementException;
use Hunt\Bundle\Models\Element\Line\LineNumber;

class ConsoleFormatter extends Formatter
{

    /**
     * ConsoleFormatter constructor.
     *
     * @throws InvalidElementException If we've set anything up wrong.
     */
    public function __construct()
    {
        $this->setAfterContentForElement(LineNumber::class, ': ');
    }
}