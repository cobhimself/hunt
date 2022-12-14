<?php

namespace Hunt\Component\MatchContext;

use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionInterface;

/**
 * @since 1.5.0
 */
class DummyContextCollector implements ContextCollectorInterface
{
    public static $matchContextCollection;

    /**
     * @var MatchContextCollectionInterface
     */
    private $contextCollection;

    /**
     * Construct the collector and provide the number of context lines we want to have before/after matches.
     */
    public function __construct(int $numContextLines = 0)
    {
        $this->contextCollection = new DummyMatchContextCollection();
    }

    /**
     * Add a line to our collection.
     *
     * If the line is a match, we perform some additional processes but we do not include matching lines within
     * our context lines.
     *
     * @param Line $line The line content.
     * @param bool $isMatch Whether or not this line is a match.
     */
    public function addLine(Line $line, bool $isMatch) { }

    /**
     * Perform processes required when a match is found.
     *
     * @param int $num Line number of the match.
     */
    public function matchFound(int $num) { }

    /**
     * Get the context collection we've collected.
     */
    public function getContextCollection(): MatchContextCollectionInterface
    {
        return $this->contextCollection;
    }

    /**
     * Must be called after running the ContextCollector to make sure the final context is set.
     */
    public function finalize() { }
}
