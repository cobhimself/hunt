<?php

namespace Hunt\Bundle\Models\MatchContext;

use Hunt\Bundle\Exceptions\MissingMatchContextException;

/**
 * Contains a collection of contexts for our matching lines.
 *
 * @since 1.5.0
 */
class MatchContextCollection implements MatchContextCollectionInterface
{
    private $longestLineNumberLength;
    private $longestLineLength;

    private $collection = [];

    public function addContext(int $lineNum, MatchContext $context)
    {
        $this->collection[$lineNum] = $context;
    }

    public function getContextForLine(int $lineNum): MatchContext
    {
        if (!isset($this->collection[$lineNum])) {
            throw new MissingMatchContextException($lineNum);
        }

        return $this->collection[$lineNum];
    }

    /**
     * Returns the number of context collections we have.
     */
    public function getCollectionSize(): int
    {
        return count($this->collection);
    }

    /**
     * Get the length of the longest line within all of our contexts.
     */
    public function getLongestLineLength(): int
    {
        if (empty($this->collection)) {
            return 0;
        }

        $this->processLengths();

        return $this->longestLineLength;
    }

    /**
     * Get the length of the longest line number.
     */
    public function getLongestLineNumberLength(): int
    {
        if (empty($this->collection)) {
            return 0;
        }

        $this->processLengths();

        return $this->longestLineNumberLength;
    }

    /**
     * Compute how long our longest context line and longest context line numbers are.
     */
    public function processLengths()
    {
        if ($this->longestLineLength === null || $this->longestLineNumberLength === null) {
            /**
             * @var MatchContext $matchContext
             */
            foreach ($this->collection as $matchLineNumber => $matchContext) {
                $lines = $matchContext->getBefore() + $matchContext->getAfter();
                foreach ($lines as $contextLineNumber => $contextLine) {
                    $this->longestLineNumberLength = max($this->longestLineNumberLength, strlen($contextLineNumber));
                    $this->longestLineLength = max($this->longestLineLength, strlen($contextLine));
                }
            }
        }
    }

    /**
     * Whether or not this context collection adds context lines.
     *
     * Useful to quickly determine if the context collection is a dummy.
     */
    public function addsContext(): bool
    {
        return true;
    }
}
