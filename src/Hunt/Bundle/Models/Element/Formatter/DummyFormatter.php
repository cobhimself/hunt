<?php

namespace Hunt\Bundle\Models\Element\Formatter;


use Hunt\Bundle\Models\Element\Line\LineInterface;
use Hunt\Bundle\Models\Element\ElementInterface;

class DummyFormatter implements FormatterInterface
{
    public function format(ElementInterface $element): string
    {
        return $element->getContent();
    }

    public function getFormattedLine(LineInterface $line): string
    {
        return $line->getContent();
    }

    public function setSidesForElement($element, string $beforeContent, string $afterContent): FormatterInterface
    {
        return $this;
    }

    public function setBeforeContentForElement($element, string $beforeContent): FormatterInterface
    {
        return $this;
    }

    public function setAfterContentForElement($element, string $afterContent): FormatterInterface
    {
        return $this;
    }

    public function getBeforeContentForElement($element): string
    {
        return '';
    }

    public function getAfterContentForElement($element): string
    {
        return '';
    }
}