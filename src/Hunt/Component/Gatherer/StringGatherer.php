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
        $this->workingLine = $line;
        $translateArray = $this->removeExcludedTerms();

        //Perform our highlighting
        $this->workingLine = str_replace(
            $this->term,
            $highlightStart . $this->term . $highlightEnd,
            $this->workingLine
        );

        return $this->addExcludedTermsBack($translateArray);
    }
}
