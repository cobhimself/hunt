<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Result;

class StringGatherer extends AbstractGatherer
{
    /**
     * Performs a string based comparison for our term/excluded terms and sets the matching lines within the result.
     */
    public function lineMatches(string $line): bool
    {
        return false !== strpos($line, $this->term);
    }

    /**
     * Perform the highlighting of the given line.
     */
    public function highlightLine(string $line, string $highlightStart = '', string $highlightEnd = ''): string
    {
        return str_replace(
            $this->term,
            $highlightStart . $this->term . $highlightEnd,
            $line
        );
    }
}
