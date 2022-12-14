<?php

namespace Hunt\Bundle\Exceptions;

use Hunt\Bundle\Templates\TemplateFactory;
use const PHP_EOL;

/**
 * @since 1.5.0
 */
class InvalidTemplateException extends HuntBaseException
{
    public function __construct(string $type)
    {
        $msg = sprintf('"%s" is not a valid template type. Choose from:' . PHP_EOL . PHP_EOL . '%s',
            $type,
            implode(PHP_EOL . ' - ', array_keys(TemplateFactory::TEMPLATE_LIST))
        );
        parent::__construct($msg);
    }
}
