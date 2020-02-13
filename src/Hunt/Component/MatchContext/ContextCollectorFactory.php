<?php

namespace Hunt\Component\MatchContext;

use Hunt\Component\MatchContext\ContextCollector;
use Hunt\Component\MatchContext\ContextCollectorInterface;
use Hunt\Component\MatchContext\DummyContextCollector;

/**
 * @since 1.5.0
 */
class ContextCollectorFactory
{

    /**
     * Get a context collector.
     *
     * @param int $numLines The number of lines we should include in our context.
     *
     * @return ContextCollectorInterface
     */
    public static function get(int $numLines = 0): ContextCollectorInterface
    {
        if ($numLines > 0) {
            return new ContextCollector($numLines);
        }

        return new DummyContextCollector();
    }
}
