<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Result;

class StringGatherer extends AbstractGatherer
{
    /**
     * Performs a string based comparison for our term/excluded terms and sets the matching lines within the result.
     */
    public function gather(Result $result): bool
    {
        $matchingLines = [];

        foreach ($result->getFileIterator() as $num => $line) {
            $testLine = $line;
            if (null !== $this->exclude && is_array($this->exclude)) {
                foreach ($this->exclude as $excludeTerm) {
                    $testLine = str_replace($excludeTerm, '', $testLine);
                }
            }

            if (false !== strpos($testLine, $this->term)) {
                $matchingLines[$num] = $this->getTrimMatchingLines() ? ltrim($line) : $line;
            }
        }

        $result->setMatchingLines($matchingLines);

        return count($matchingLines) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getHighlightedLine(string $line, string $highlightStart = '', string $highlightEnd = ''): string
    {
        //We could use regex but it's possible the complexity would cause the search to take a long time. Therefore,
        //we are going to replace our exclude terms with placeholders, highlight our original term, and then put our
        //exclude terms back.
        static $placeholder = '$9#041x';
        $counter = 0;

        //We need to build a translation between our exclude terms and the placeholders
        $translate = [];
        foreach ($this->exclude as $exclude) {
            ++$counter;
            $translate[$exclude] = $placeholder . $counter;
        }
        $placeholderLine = strtr($line, $translate);

        //Perform our highlighting
        $placeholderLine = str_replace(
            $this->term,
            $highlightStart . $this->term . $highlightEnd,
            $placeholderLine
        );

        //Reverse our placeholder translation
        $translate = array_flip($translate);

        return strtr($placeholderLine, $translate);
    }
}
