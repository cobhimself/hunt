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
     * Returns the given string with our search term highlighted.
     *
     * Excluded terms are ignored.
     *
     * @return mixed
     */
    public function getHighlightedLine(string $line, string $highlightStart = '', string $highlightEnd = '');

    /**
     * Returns whether or not the given line matches.
     *
     * @since 1.5.0
     *
     * @param string $line A single line from the file we are gathering from.
     */
    public function lineMatches(string $line): bool;

    /**
     * Perform the highlighting of the given line.
     *
     * @since 1.5.0
     */
    public function highlightLine(string $line, string $highlightStart = '', string $highlightEnd = ''): string;
}
