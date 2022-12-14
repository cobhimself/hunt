<?php

namespace Hunt\Tests\Bundle\Models\Content;


class Content
{
    /**
     * @var string
     */
    protected $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}