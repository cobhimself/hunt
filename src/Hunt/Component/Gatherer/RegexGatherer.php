<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Result;

/**
 * @since 1.4.0
 */
class RegexGatherer extends AbstractGatherer
{
    /**
     * Performs a regex based comparison for our term/excluded terms and sets the matching lines within the result.
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

            if (!empty($testLine) && preg_match($this->term, $testLine)) {
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

        $this->workingLine = preg_replace_callback(
            $this->term,
            static function ($matches) use ($highlightEnd, $highlightStart) {
                if (1 === count($matches)) {
                    return $highlightStart . $matches[0] . $highlightEnd;
                }

                return str_replace($matches[1], $highlightStart . $matches[1] . $highlightEnd, $matches[0]);
            },
            $this->workingLine
        );

        return $this->addExcludedTermsBack($translateArray);
    }
}
