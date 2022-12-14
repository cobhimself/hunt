<?php

namespace Hunt\Bundle\Exceptions;


use Throwable;

class UnknownGathererMatchCacheException extends HuntBaseException
{
    public function __construct(int $lineNumber, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('No gatherer match cache exists for %s', $lineNumber), $code, $previous);
    }
}