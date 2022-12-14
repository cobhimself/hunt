<?php

namespace Hunt\Bundle\Models\Element\Line;


use Hunt\Bundle\Models\Element\ElementInterface;
use Hunt\Bundle\Models\Element\Formatter\FormatterInterface;

interface LineInterface extends ElementInterface
{
    /**
     * Get the line number of this line.
     */
    public function getLineNumber(): int;
    public function setLineNumber(string $lineNum): LineInterface;
    public function getLineNumberElement(): LineNumberInterface;
    public function setState(int $state);
    public function getState(): int;
    public function setContainsExcludedContent(bool $containsExcludedContent): LineInterface;
    public function getContainsExcludedContent(): bool;
    public function getFormatted(FormatterInterface $formatter): string;
}