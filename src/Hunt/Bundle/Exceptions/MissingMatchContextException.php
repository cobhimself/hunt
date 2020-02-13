<?php


namespace Hunt\Bundle\Exceptions;

use Throwable;

/**
 * @since 1.5.0
 */
class MissingMatchContextException extends \Exception
{
    public function __construct(int $lineNumber, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('No match context exists for %s', $lineNumber), $code, $previous);
    }
}