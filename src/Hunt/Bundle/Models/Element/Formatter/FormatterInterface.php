<?php

namespace Hunt\Bundle\Models\Element\Formatter;


use Hunt\Bundle\Models\Element\ElementInterface;
use Hunt\Bundle\Models\Element\Line\LineInterface;

interface FormatterInterface
{
    public function format(ElementInterface $element): string;
    public function getFormattedLine(LineInterface $line): string;
    public function setSidesForElement($element, string $beforeContent, string $afterContent): FormatterInterface;
    public function setBeforeContentForElement($element, string $beforeContent): FormatterInterface;
    public function setAfterContentForElement($element, string $afterContent): FormatterInterface;
    public function getBeforeContentForElement($element): string;
    public function getAfterContentForElement($element): string;
}