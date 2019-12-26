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
}