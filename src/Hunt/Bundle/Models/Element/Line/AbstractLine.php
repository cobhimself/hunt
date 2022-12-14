<?php

namespace Hunt\Bundle\Models\Element\Line;

use Hunt\Bundle\Exceptions\LineProcessFlowChangeException;
use Hunt\Bundle\Models\Element;
use Hunt\Bundle\Models\Element\Formatter\FormatterInterface;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;
use Hunt\Bundle\Models\Element\Line\LineNumber;

abstract class AbstractLine extends Element implements LineInterface
{
    /**
     * @var PartsCollection
     */
    protected $parts;

    const STATE_FRESH = 0;
    const STATE_TERMS_EXCLUDED = 1;
    const STATE_PARTS_INIT = 2;
    const STATE_PARTS_FINALIZED = 3;

    const FINAL_STATE = self::STATE_PARTS_FINALIZED;

    const STATE_FLOW = [
        self::STATE_FRESH => 'fresh',
        self::STATE_TERMS_EXCLUDED => 'exclude terms',
        self::STATE_PARTS_INIT => 'initialize parts',
        self::STATE_PARTS_FINALIZED => 'finalized',
    ];

    /**
     * @var bool
     */
    private $containsExcludedContent = false;

    /**
     * @var int
     */
    protected $lineNumber;

    protected $state;

    public function __construct(string $content = null)
    {
        parent::__construct($content);
        $this->state = $this->getInitialState();
    }

    abstract protected function getInitialState(): int;

    public function setLineNumber(string $lineNum): LineInterface
    {
        $this->lineNumber = $lineNum;

        return $this;
    }

    /**
     * @param PartsCollection $parts
     *
     * @return ParsedLine
     *
     * @throws LineProcessFlowChangeException
     */
    public function setParts(PartsCollection $parts): AbstractLine
    {
        $this->setState(self::STATE_PARTS_INIT);

        $this->parts = $parts;

        return $this;
    }

    /**
     * @return PartsCollection
     */
    public function getParts(): PartsCollection
    {
        return $this->parts;
    }

    /**
     * @throws LineProcessFlowChangeException
     */
    public function setState(int $state)
    {
        if (
            null !== $this->state
            && ($this->state !== $state - 1 || $this->state === self::FINAL_STATE)
        ) {
            throw new LineProcessFlowChangeException($this, $state);
        }

        $this->state = $state;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * @return LineNumber
     */
    public function getLineNumberElement(): LineNumberInterface
    {
        return new LineNumber($this->getLineNumber());
    }

    /**
     * @param bool $containsExcludedContent
     *
     * @return AbstractLine
     */
    public function setContainsExcludedContent(bool $containsExcludedContent): LineInterface
    {
        $this->containsExcludedContent = $containsExcludedContent;

        return $this;
    }

    public function getContainsExcludedContent(): bool
    {
        return $this->containsExcludedContent;
    }

    public function getFormatted(FormatterInterface $formatter): string
    {
        return $formatter->getFormattedLine($this);
    }
}