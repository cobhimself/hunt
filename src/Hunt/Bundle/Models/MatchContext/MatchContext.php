<?php

namespace Hunt\Bundle\Models\MatchContext;

/**
 * Hold context lines for our results.
 *
 * @since 1.5.0
 */
class MatchContext
{
    /**
     * @var array
     */
    private $before = [];

    /**
     * @var array
     */
    private $after = [];

    public function __construct(array $before = [], array $after = [])
    {
        $this->setBefore($before);
        $this->setAfter($after);
    }

    /**
     * Set the context lines which come before.
     */
    public function setBefore(array $before): MatchContext
    {
        $this->before = $before;

        return $this;
    }

    /**
     * Get the context lines which came before our result.
     */
    public function getBefore(): array
    {
        return $this->before;
    }

    /**
     * Set the context lines which come after.
     */
    public function setAfter(array $after): MatchContext
    {
        $this->after = $after;

        return $this;
    }

    /**
     * Get the context lines which came after our result.
     */
    public function getAfter(): array
    {
        return $this->after;
    }
}
