<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Exceptions\LineFactoryException;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Bundle\Models\Element\Line\Parts\Match;
use Hunt\Bundle\Models\Element\Line\Parts\Normal;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;
use Hunt\Component\StringSearchWalker;

class StringGatherer extends AbstractGatherer
{
    /**
     * Performs a string based comparison for our term/excluded terms and sets the matching lines within the result.
     */
    public function lineMatches(int $lineNum, string $line): bool
    {
        return false !== strpos($line, $this->term);
    }

    /**
     * Take our line and split it up into normal and match parts.
     *
     * @param Line $line The line we want to parse.
     *
     * @return ParsedLine
     *
     * @throws LineFactoryException
     */
    public function getParsedLine(Line $line): ParsedLine
    {
        $parts   = new PartsCollection();
        $content = $line->getContent();
        $walker  = new StringSearchWalker($content, $this->term);

        foreach ($walker as $beforeContent) {
            $parts[] = new Normal($beforeContent);
            $parts[] = new Match($this->term);
        }

        //We need to get the final part after the last match
        $parts[] = new Normal($walker->tail());

        return LineFactory::getParsed($line, $parts);
    }
}
