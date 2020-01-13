<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Result;

class FileListTemplate extends AbstractTemplate
{
    /**
     * Render the filename only.
     */
    public function getResultOutput(Result $result): string
    {
        return $result->getFileName() . \PHP_EOL;
    }

    /**
     * Returns a blank string since we're not interested in displaying result lines.
     */
    public function getLineNumber(string $lineNum): string
    {
        return '';
    }
}
