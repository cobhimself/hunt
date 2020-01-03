<?php


namespace Hunt\Component\Gatherer;


use \InvalidArgumentException;

class GathererFactory
{
    const GATHERER_STRING = 1;
    const GATHERER_REGEX = 2;

    public static function getByType(int $type, $term, $exclude)
    {
        switch ($type) {
            case self::GATHERER_STRING:
                return new StringGatherer($term, $exclude);
            case self::GATHERER_REGEX:
                throw new InvalidArgumentException('Gatherer not implemented yet.');
            default:
                throw new InvalidArgumentException('Unknown gatherer type: ' . $type);
        }
    }
}
