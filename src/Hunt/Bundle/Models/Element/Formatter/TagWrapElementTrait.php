<?php

namespace Hunt\Bundle\Models\Element\Formatter;

trait TagWrapElementTrait
{
    /**
     * @param string $element
     * @param string $tag The tag to surround the element with.
     */
    public function wrapElement($element, string $tag): FormatterInterface
    {
        /** @var FormatterInterface $this */
        $this->setSidesForElement($element, '<' . $tag . '>', '</' . $tag . '>');

        return $this;
    }
}