<?php

namespace Hunt\Bundle\Models\MatchContext;


/**
 * @since 1.5.0
 */
class MatchContextCollectionFactory
{

    /**
     * @var DummyMatchContextCollection
     */
    static private $dummyMatchContextCollection;

    /**
     * Get a MatchContextCollection object.
     *
     * @param bool $real If true, return a MatchContextCollection. If false, return a dummy.
     */
    public static function get(bool $real): MatchContextCollectionInterface
    {
        //If we want a real match context collection...
        if ($real) {
            return new MatchContextCollection();
        }

        //Otherwise, let's create a single dummy collection we can pass back each time we request one.
        if (!(self::$dummyMatchContextCollection instanceof DummyMatchContextCollection)) {
            self::$dummyMatchContextCollection = new DummyMatchContextCollection();
        }

        return self::$dummyMatchContextCollection;
    }
}