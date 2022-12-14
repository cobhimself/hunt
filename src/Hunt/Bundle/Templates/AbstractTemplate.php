<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Element\Line\ContextLineNumber;
use Hunt\Bundle\Models\Element\ContextSplit;
use Hunt\Bundle\Models\Element\Formatter\DummyFormatter;
use Hunt\Bundle\Models\Element\Formatter\FormatterInterface;
use Hunt\Bundle\Models\Element\Line\LineNumber;
use Hunt\Bundle\Models\Element\ResultFilePath;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineInterface;
use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTemplate implements TemplateInterface
{
    protected $bodyOutput = '';

    /**
     * @var ResultCollection
     */
    protected $resultCollection;

    /**
     * @var OutputInterface|null
     */
    protected $output;

    /**
     * The header string to be output before the template's content.
     *
     * @var string
     */
    private $header = '';

    /**
     * The footer string to be output after the template's content.
     *
     * @var string
     */
    private $footer = '';

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * Whether or not to show the context lines.
     *
     * @since 1.5.0
     */
    protected $showContext;

    /**
     * Whether or not we want to highlight the matching term.
     *
     * @var bool
     */
    private $highlightMatch = false;

    /**
     * Perform necessary actions before rendering the template.
     *
     * @codeCoverageIgnore
     */
    public function init(ResultCollection $resultCollection, OutputInterface $output): TemplateInterface
    {
        $this->resultCollection = $resultCollection;
        $this->output = $output;

        return $this;
    }

    public function setFormatter(FormatterInterface $formatter): TemplateInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        if (null === $this->formatter) {
            $this->formatter = new DummyFormatter();
        }

        return $this->formatter;
    }

    /**
     * Get an array of rendered result lines.
     *
     * This method is useful when you want to change how the group of term results is rendered.
     */
    public function getTermResults(Result $result): array
    {
        $lines = [];

        foreach ($result->getMatchingLines() as $lineNum => $line) {
            $matchContext = $result->getContextCollection()->getContextForLine($lineNum);

            if ($this->getShowContext()) {
                $this->getContextSplitBefore($lines);
                $this->processContextLines($lines, $matchContext->getBefore());
            }

            $lines[] = $this->getResultLine($line);

            if ($this->getShowContext()) {
                $this->processContextLines($lines, $matchContext->getAfter());
                $this->getContextSplitAfter($lines);
            }
        }

        return $lines;
    }

    /**
     * Returns an individual term result line.
     *
     * Override this method if you'd like to modify how each individual term result appears in the result list.
     */
    public function getResultLine(LineInterface $line): string
    {
        $finalLine = $this->getLineNumber($line);
        $finalLine .= $this->getFormatter()->getFormattedLine($line);
        $finalLine = str_replace("\n", '', $finalLine);

        return $finalLine;
    }

    public function getLineNumber(LineInterface $line): string
    {
        return $this->getFormatter()->format(new LineNumber($line->getLineNumber()));
    }

    /**
     * Process our context lines to conform to our template.
     *
     * @param array $lines        An array of lines we should append our context lines to.
     * @param array $contextLines An array containing context lines for a match.
     *
     * @since 1.5.0
     */
    public function processContextLines(array &$lines, array $contextLines)
    {
        /**
         * @var Line $line
         */
        foreach ($contextLines as $lineNum => $line) {
            $finalLine = $this->getFormatter()->format(new ContextLineNumber($lineNum));
            $finalLine .= str_replace("\n", '', $this->getFormatter()->getFormattedLine($line));
            $lines[] = $finalLine;
        }
    }

    /**
     * Returns the rendered filename.
     *
     * Override this method if you'd like to style the filename differently.
     */
    public function getFilename(Result $result): string
    {
        return $this->getFormatter()->format(new ResultFilePath($result->getFileName()));
    }

    /**
     * Returns the rendered term Result.
     *
     * This class should call the following methods to construct the final result "Row":
     *
     *  - getTermResults
     *  - getFilename
     */
    abstract public function getResultOutput(Result $result): string;

    /**
     * Set the header string to be output before the template.
     *
     * No new line is automatically added.
     */
    public function setHeader(string $header)
    {
        $this->header = $header;
    }

    /**
     * If necessary, provide a header here.
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Set the header string to be output before the template.
     *
     * No new line is automatically added.
     */
    public function setFooter(string $footer)
    {
        $this->footer = $footer;
    }

    /**
     * If necessary, provide a footer here.
     */
    public function getFooter(): string
    {
        return $this->footer;
    }

    /**
     * Renders a single result and adds it to the body output.
     *
     * Useful for iterating through result collections and having the body output be compiled.
     *
     * @codeCoverageIgnore
     */
    public function renderResult(Result $result)
    {
        $this->bodyOutput .= $this->getResultOutput($result);
    }

    /**
     * Return the currently rendered body output.
     *
     * @codeCoverageIgnore
     */
    public function getBodyOutput(): string
    {
        return $this->bodyOutput;
    }

    /**
     * Returns the rendered template output.
     *
     * NOTE: You should render the body of the template using renderResult before attempting to get the output.
     *
     * @codeCoverageIgnore
     */
    public function getOutput(): string
    {
        return $this->getHeader()
            . $this->getBodyOutput()
            . $this->getFooter();
    }

    /**
     * Add lines to be placed before the context lines of a matching result.
     *
     * @since 1.5.0
     *
     * @param array $lines The array of lines to add to.
     */
    public function getContextSplitBefore(array &$lines)
    {
        $lines[] = $this->getFormatter()->format(new ContextSplit('---'));
    }

    /**
     * Add lines to be placed before the context lines of a matching result.
     *
     * @since 1.5.0
     *
     * @param array $lines The array of lines to add to.
     */
    public function getContextSplitAfter(array &$lines)
    {
        $lines[] = $this->getFormatter()->format(new ContextSplit('---'));
    }

    /**
     * Tells the template whether or not to show context lines.
     *
     * @since 1.5.0
     */
    public function setShowContext(bool $showContext): TemplateInterface
    {
        $this->showContext = $showContext;

        return $this;
    }

    /**
     * Get whether or not we should worry about outputting context lines.
     *
     * @since 1.5.0
     */
    public function getShowContext(): bool
    {
        return $this->showContext;
    }

    /**
     * @inheritDoc
     */
    public function highlight(bool $highlight = true): TemplateInterface
    {
        $this->highlightMatch = $highlight;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function doHighlight(): bool
    {
        return $this->highlightMatch;
    }
}
