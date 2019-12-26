<?php

namespace Hunt\Component\Gatherer;


use Hunt\Bundle\Models\Result;

class StringGatherer extends AbstractGatherer
{

    /**
     * Performs a string based comparison for our term/excluded terms and sets the matching lines within the result.
     *
     * @param Result $result
     *
     * @return bool
     */
    public function gather(Result $result): bool
    {
        $matchingLines = [];

        foreach ($result->getFileIterator() as $num => $line) {
            $testLine = $line;
            if ($this->exclude !== null && is_array($this->exclude)) {
                foreach ($this->exclude as $excludeTerm) {
                    $testLine = str_replace($excludeTerm, '', $testLine);
                }
            }

            if (strpos($testLine, $this->term) !== false) {
                $matchingLines[$num] =  $this->getTrimMatchingLines() ? ltrim($line) : $line;
            }
        }

        $result->setMatchingLines($matchingLines);

        return count($matchingLines) > 0;
    }
}