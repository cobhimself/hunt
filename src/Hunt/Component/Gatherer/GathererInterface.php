<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Result;

/**
 * Interface GathererInterface.
 *
 * Gatherers gather the matching lines found in files obtained by hunters.
 */
interface GathererInterface
{
    /**
     * GathererInterface constructor.
     *
     * @param string $term    the term we originally were hunting for
     * @param array  $exclude an array of terms to exclude from the search
     */
    public function __construct(string $term, array $exclude);

    /**
     * Gather the matching lines within a result's file.
     *
     * @return bool True if we found matching lines. False otherwise.
     */
    public function gather(Result $result): bool;

    /**
     * Set whether or not we want this gatherer to trim whitespace from the beginning of matching lines.
     *
     * @return AbstractGatherer
     */
    public function setTrimMatchingLines(bool $trim = true): GathererInterface;

    /**
     * Returns the given string with our search term highlighted.
     *
     * Excluded terms are ignored.
     *
     * @return mixed
     */
    public function getHighlightedLine(string $line, string $highlightStart = '', string $highlightEnd = '');
}
