<?php

namespace Hunt\Bundle\Models\MatchContext;


/**
 * @since 1.5.0
 */
interface MatchContextCollectionInterface
{
    public function addContext(int $lineNum, MatchContext $context);

    public function getContextForLine(int $lineNum): MatchContext;

    /**
     * Get the length of the longest line within all of our contexts.
     */
    public function getLongestLineLength(): int;

    /**
     * Get the length of the longest line number.
     */
    public function getLongestLineNumberLength(): int;

    /**
     * Compute how long our longest context line and longest context line numbers are.
     */
    public function processLengths();

    /**
     * Whether or not this context collection adds context lines.
     *
     * Useful to quickly determine if the context collection is a dummy.
     */
    public function addsContext(): bool;

    /**
     * Returns the number of context collections we have.
     */
    public function getCollectionSize(): int;
}