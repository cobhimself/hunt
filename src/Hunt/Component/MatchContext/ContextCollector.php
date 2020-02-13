<?php

namespace Hunt\Component\MatchContext;


use Hunt\Bundle\Models\MatchContext\MatchContext;
use Hunt\Bundle\Models\MatchContext\MatchContextCollection;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionFactory;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionInterface;

/**
 * Collect before and after context lines for our matching lines.
 *
 * @since 1.5.0
 */
class ContextCollector implements ContextCollectorInterface
{
    /**
     * @var int
     */
    private $numContextLines;

    /**
     * @var array The lines which we have as 'before' context.
     */
    private $before = [];

    /**
     * @var array The lines which we have as 'after' context.
     */
    private $after = [];

    /**
     * @var bool Whether or not we have found a matching line and are within our context line limits.
     */
    private $inMatch = false;

    /**
     * @var int The matching line number.
     */
    private $matchingLineNum;

    /**
     * @var MatchContextCollection
     */
    private $contextCollection;

    public function __construct(int $numContextLines = 0)
    {
       $this->numContextLines = $numContextLines;
       $this->contextCollection = MatchContextCollectionFactory::get($numContextLines > 0);
    }

    /**
     * Add a line to our collection.
     */
    public function addLine(int $num, string $line, bool $isMatch)
    {
        //Do we even have anything to do?
        if ($this->numContextLines <= 0) {
            return;
        }

        //Have we satisfied the number of 'after' context lines?
        if (count($this->after) === $this->numContextLines) {
            $this->finalizeContext();
        }

        if ($isMatch) {
            $this->matchFound($num);
            //Nothing to record if the current line is a match.
            return;
        }

        if (!$this->inMatch) {
            $this->advanceLineCollection($this->before, $num, $line);
        } else {
            $this->advanceLineCollection($this->after, $num, $line);
        }
    }

    /**
     * Advance the context lines for the given context line array.
     *
     * @param array $contextLineArray One of $this->before or $this->after.
     */
    private function advanceLineCollection(array &$contextLineArray, int $num, string $line)
    {
        if (count($contextLineArray) === $this->numContextLines) {
            $contextLineArray = array_slice($contextLineArray, 1, NULL, true);
        }
        $contextLineArray[$num] = $line;
    }

    public function matchFound(int $num)
    {
        //It's possible our match has been found before we could move past the amount of 'after' context lines. In this
        //instance, we should finalize the context and continue.
        if(!empty($this->after) || $this->inMatch) {
            $this->finalizeContext();
        }

        $this->inMatch = true;
        $this->matchingLineNum = $num;
    }

    private function reset()
    {
        $this->before = [];
        $this->after = [];
        $this->inMatch = false;
        $this->matchingLineNum = null;
    }

    /**
     * Add a new MatchContext to our context collection.
     */
    private function finalizeContext()
    {
        if (!($this->numContextLines > 0)) {
            return;
        }

        //Hold on to the 'after' lines so we can use it to populate our new 'before' context lines.
        $after = $this->after;

        if ((!empty($this->before) || !empty($this->after)) && $this->matchingLineNum !== null) {
            //We've got a complete match context!
            $this->contextCollection->addContext(
                $this->matchingLineNum,
                new MatchContext($this->before, $this->after)
            );
        }

        $this->reset();

        //Go head and set our before lines to what our after lines were since we've been collecting 'after' lines
        $this->before = $after;
    }

    public function getContextCollection(): MatchContextCollectionInterface
    {
        return $this->contextCollection;
    }

    /**
     * Must be called after running the ContextCollector to make sure the final context is set.
     */
    public function finalize()
    {
        $this->finalizeContext();
    }
}