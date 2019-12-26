<?php

namespace Hunt\Bundle\Models;


use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class ResultCollection
 *
 * A collection of Hunter Results.
 *
 * @package Hunt\Bundle\Models
 */
class ResultCollection extends ParameterBag
{
    /**
     * Get the length of the longest filename in the results.
     *
     * @return mixed
     */
    public function getLongestFilenameLength()
    {
        return max(array_map('strlen', $this->keys()));
    }

    /**
     * Returns the number of digits in the line numbers for all results.
     *
     * @return int
     */
    public function getLongestLineNumInResults(): int
    {
        return max(
            array_map(static function (Result $result) {
                    return $result->getLongestLineNumLength();
                },
                $this->all()
            )
        );
    }

    /**
     * Sort the ResultCollection by filename.
     *
     * @param int $flags See sort flags https://www.php.net/manual/en/function.sort.php
     */
    public function sortByFilename($flags = SORT_DESC)
    {
        ksort($this->parameters, $flags | SORT_FLAG_CASE | SORT_STRING );
    }

    /**
     * Get rid of any results which no longer have matching lines.
     *
     * @return int The number of results after the squash.
     */
    public function squashEmptyResults(): int
    {
        $this->parameters = array_filter(
            $this->parameters,
            static function (Result $result) {
                return $result->getNumMatches() > 0;
            }
        );

        return count($this->parameters);
    }
}