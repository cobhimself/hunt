<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Element\Line\LineInterface;
use Hunt\Bundle\Models\Result;

use const PHP_EOL;

/**
 * @since 1.3.0
 */
class FileListTemplate extends AbstractTemplate
{
    /**
     * Render the filename only.
     */
    public function getResultOutput(Result $result): string
    {
        return $result->getFileName() . PHP_EOL;
    }

    /**
     * Returns a blank string since we're not interested in displaying result lines.
     */
    public function getLineNumber(LineInterface $line): string
    {
        return '';
    }
}
