<?php

namespace Hunt\Component\Gatherer;


use Hunt\Bundle\Models\Result;

/**
 * Interface GathererInterface
 *
 * Gatherers gather the matching lines found in files obtained by hunters.
 *
 * @package Hunt\Component\Gatherer
 */
interface GathererInterface
{
    /**
     * GathererInterface constructor.
     *
     * @param string $term   The term we originally were hunting for.
     * @param array  $exclude An array of terms to exclude from the search.
     */
    public function __construct(string $term, array $exclude);

    /**
     * Gather the matching lines within a result's file.
     *
     * @param Result $result
     *
     * @return bool True if we found matching lines. False otherwise.
     */
    public function gather(Result $result): bool;
}