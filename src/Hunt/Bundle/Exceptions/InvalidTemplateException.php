<?php

namespace Hunt\Bundle\Exceptions;

use Hunt\Bundle\Templates\TemplateFactory;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * @since 1.5.0
 */
class InvalidTemplateException extends InvalidArgumentException
{
    public function __construct(string $type)
    {
        $msg = sprintf('"%s" is not a valid template type. Choose from:' . \PHP_EOL . \PHP_EOL . '%s',
            $type,
            implode(\PHP_EOL . ' - ', array_keys(TemplateFactory::TEMPLATE_LIST))
        );
        parent::__construct($msg);
    }
}
