<?php


namespace Hunt\Component\Gatherer;


use Exception;
use Hunt\Bundle\Models\Result;

class AbstractGatherer implements GathererInterface
{
    /**
     * The term we are searching for.
     *
     * @var string
     */
    protected $term;

    /**
     * A list of exclude terms.
     *
     * @var array
     */
    protected $exclude;

    /**
     * Whether or not to trim spaces from the beginning of matching lines.
     *
     * @var bool
     */
    protected $trimMatchingLines = false;

    public function __construct(string $term, array $exclude)
    {
        $this->term = $term;
        $this->exclude = $exclude;
    }

    /**
     * Gather a set of matching lines from the Result's file.
     *
     * @param Result $result
     *
     * @return bool True if we still have matches, false otherwise.
     */
     public function gather(Result $result): bool
     {
         //replace with custom gather functionality
         new Exception('The gather method must be extended!');

         return false;
     }

    /**
     * Set whether or not we want this gatherer to trim whitespace from the beginning of matching lines.
     *
     * @param bool $trim
     *
     * @return AbstractGatherer
     */
    public function setTrimMatchingLines(bool $trim = true): GathererInterface
    {
         $this->trimMatchingLines = $trim;

         return $this;
    }

    /**
     * Get whether or not we want to trim spaces from the beginning of the matching lines.
     *
     * @return bool
     */
    public function getTrimMatchingLines(): bool
    {
        return $this->trimMatchingLines;
    }
}