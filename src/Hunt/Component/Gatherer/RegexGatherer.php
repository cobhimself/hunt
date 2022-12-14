<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Exceptions\LineFactoryException;
use Hunt\Bundle\Exceptions\UnknownGathererMatchCacheException;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;

/**
 * @since 1.4.0
 */
class RegexGatherer extends AbstractGatherer
{
    /**
     * Contains regex matches indexed by line number
     *
     * @var array
     */
    private $mapCache = [];

    /**
     * Whether or not the given line matches.
     *
     * In addition to confirming our line matches or not, we'll also keep a cache of matches returned so we do not need
     * to do so later when we perform the highlighting of the line and its matches.
     */
    public function lineMatches(int $lineNum, string $line): bool
    {
        if (empty($line)) {
            return false;
        }

        $matches = [];
        $result = preg_match($this->term, $line, $matches);

        if ($result) {
            //We don't need the full line so we can remove the first element in the
            $this->mapCache[$lineNum] = $matches;
        }

        return $result;
    }

    /**
     * @param Line $line
     *
     * @return ParsedLine
     *
     * @throws LineFactoryException If we are unable to get a parsed line.
     * @throws UnknownGathererMatchCacheException If we attempt to get cache for a line we've not seen.
     */
    public function getParsedLine(Line $line): ParsedLine
    {
        if (!array_key_exists($line->getLineNumber(), $this->mapCache)) {
            throw new UnknownGathererMatchCacheException($line->getLineNumber());
        }

        $matches = $this->mapCache[$line->getLineNumber()];

        $parts = new PartsCollection();

        $fullLine = $line->getContent();

        $start = 0;

        foreach ($matches as $match => $offset) {
            $before = substr($fullLine, $start, $offset - $start);
            $parts->add(new Normal($before));
            $parts->add(new Match($match));
            $start += $offset + strlen($match);
        }

        //Add the final portion
        $parts->add(new Normal(substr($fullLine, $start, strlen($fullLine - $start))));

        return LineFactory::getParsed($line, $parts);
    }
}
