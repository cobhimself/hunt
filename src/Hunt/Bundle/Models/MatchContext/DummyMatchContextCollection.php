<?php


namespace Hunt\Bundle\Models\MatchContext;


/**
 * Contains a match context collection which implements necessary methods but the methods are noops.
 *
 * Useful when we need to be able to provide a match collection but we don't want anything to be done with it.
 *
 * @since 1.5.0
 */
class DummyMatchContextCollection implements MatchContextCollectionInterface
{
    /**
     * @var MatchContext
     */
    static private $matchContext;

    public function __construct()
    {
        //We'll only initialize our match context once to keep our memory down.
        if (!(self::$matchContext instanceof MatchContext)) {
            self::$matchContext = new MatchContext([], []);
        }
    }

    public function addContext(int $lineNum, MatchContext $context) {}

    public function getContextForLine(int $lineNum): MatchContext
    {
        return self::$matchContext;
    }

    /**
     * No longest line; return 0.
     */
    public function getLongestLineLength(): int
    {
        return 0;
    }

    /**
     * No longest line number; return 0.
     */
    public function getLongestLineNumberLength(): int
    {
        return 0;
    }

    /**
     * Nothing to do here.
     */
    public function processLengths() {}

    /**
     * Whether or not this context collection adds context lines.
     *
     * Useful to quickly determine if the context collection is a dummy.
     */
    public function addsContext(): bool
    {
        return false;
    }

    /**
     * Returns the number of context collections we have.
     */
    public function getCollectionSize(): int
    {
        return 0;
    }
}