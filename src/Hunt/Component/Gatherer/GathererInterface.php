<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
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
     * Take our line and split it up into parts.
     *
     * @param Line $line The line we want to parse.
     *
     * @return ParsedLine
     */
    public function getParsedLine(Line $line): ParsedLine;

    /**
     * Returns whether or not the given line matches.
     *
     * @since 1.5.0
     *
     * @param int $lineNum The line number associated with the line.
     * @param string $line A single line from the file we are gathering from.
     */
    public function lineMatches(int $lineNum, string $line): bool;

    /**
     * Set whether or not we should trim the whitespace around our matching lines.
     */
    public function setTrimMatchingLines(bool $trimMatchingLines): GathererInterface;

    /**
     * Whether or not we should trim matching lines.
     */
    public function doTrimMatchingLines(): bool;
}
