<?php


namespace Hunt\Bundle\Models;


use Hunt\Bundle\Models\Element\ElementInterface;

abstract class Element implements ElementInterface
{
    /**
     * @var string
     */
    protected $content;

    public function __construct(string $content = null)
    {
        if (null !== $content) {
            $this->content = $content;
        }
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): ElementInterface
    {
        $this->content = $content;

        return $this;
    }
}