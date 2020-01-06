<?php

namespace Hunt\Bundle\Exceptions;


use Hunt\Bundle\Templates\TemplateFactory;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class InvalidTemplateException extends InvalidArgumentException
{
    public function __construct(string $type)
    {
        $this->message = '"' . $type . '" is not a valid template type. Choose from:' . PHP_EOL . PHP_EOL
            . ' - ' . implode(PHP_EOL . ' - ', array_keys(TemplateFactory::TEMPLATE_LIST));
    }

}
