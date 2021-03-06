<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Hunt\Component\Gatherer\GathererInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTemplate implements TemplateInterface
{
    protected $bodyOutput = '';

    /**
     * @var ResultCollection
     */
    protected $resultCollection;

    /**
     * Whether or not to highlight the term in the rendered output.
     *
     * @var bool
     */
    protected $doHighlight = false;

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
     * The gatherer used to obtain our original results.
     *
     * We will use this to highlight the result terms.
     *
     * @var GathererInterface
     */
    private $gatherer;

    /**
     * @var string
     */
    protected $highlightStart = '*';

    /**
     * @var string
     */
    protected $highlightEnd = '*';

    /**
     * Whether or not to show the context lines.
     *
     * @since 1.5.0
     */
    protected $showContext;

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

    /**
     * Whether or not to highlight the term within the results.
     */
    public function highlight(bool $highlight = true)
    {
        $this->doHighlight = $highlight;
    }

    /**
     * Get an array of rendered result lines.
     *
     * This method is useful when you want to change how the group of term results is rendered.
     */
    public function getTermResults(Result $result): array
    {
        $lines = [];
        $term = $result->getTerm();

        foreach ($result->getMatchingLines() as $lineNum => $line) {
            $matchContext = $result->getContextCollection()->getContextForLine($lineNum);

            if ($this->getShowContext()) {
                $this->getContextSplitBefore($lines);
                $this->processContextLines($lines, $matchContext->getBefore());
            }

            $lines[] = $this->getResultLine($lineNum, $line, $term);

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
    public function getResultLine(string $lineNum, string $line, string $term): string
    {
        $finalLine = $this->getLineNumber($lineNum) . ': ';
        if ($this->doHighlight()) {
            $finalLine .= $this->gatherer->getHighlightedLine(
                $line,
                $this->getHighlightStart(),
                $this->getHighlightEnd()
            );
        } else {
            $finalLine .= $line;
        }
        $finalLine = str_replace("\n", '', $finalLine);

        return $finalLine;
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
        foreach ($contextLines as $lineNum => $line) {
            $lines[] = $this->getLineNumber($lineNum) . ': '
                . str_replace("\n", '', $line);
        }
    }

    /**
     * Return whether or not we are going to highlight our search term.
     */
    public function doHighlight(): bool
    {
        return $this->doHighlight;
    }

    /**
     * Return the line number formatted.
     */
    public function getLineNumber(string $lineNum): string
    {
        return $lineNum;
    }

    /**
     * Returns the rendered filename.
     *
     * Override this method if you'd like to style the filename differently.
     */
    public function getFilename(Result $result): string
    {
        return $result->getFileName();
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
     * Set the Gatherer we should use when highlighting our results.
     */
    public function setGatherer(GathererInterface $gatherer): TemplateInterface
    {
        $this->gatherer = $gatherer;

        return $this;
    }

    /**
     * Get the start string used when we want to begin highlighting.
     */
    public function getHighlightStart(): string
    {
        return $this->highlightStart;
    }

    /**
     * Set the string to use when we want to begin highlighting.
     */
    public function setHighlightStart(string $highlightStart)
    {
        $this->highlightStart = $highlightStart;
    }

    /**
     * Get the string to use when we are done highlighting.
     */
    public function getHighlightEnd(): string
    {
        return $this->highlightEnd;
    }

    /**
     * Set the string to use when we are done highlighting.
     */
    public function setHighlightEnd(string $highlightEnd)
    {
        $this->highlightEnd = $highlightEnd;
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
        $lines[] = '---';
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
        $lines[] = '---';
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
}
