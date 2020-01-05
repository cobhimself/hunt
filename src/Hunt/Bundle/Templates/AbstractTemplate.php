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
    private $highlightStart = '*';

    /**
     * @var string
     */
    private $highlightEnd = '*';

    /**
     * AbstractTemplate constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct(ResultCollection $resultCollection, OutputInterface $output = null)
    {
        $this->resultCollection = $resultCollection;
        $this->output = $output;

        $this->init();
    }

    /**
     * Perform necessary actions before rendering the template.
     *
     * @codeCoverageIgnore
     */
    public function init()
    {
        //nop return;
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
            $lines[] = $this->getResultLine($lineNum, $line, $term);
        }

        return $lines;
    }

    /**
     * Returns an individual term result line.
     *
     * Override this method if you'd like to modify how each individual term result appears in the result list.
     *
     * @param $lineNum
     * @param $line
     * @param $term
     *
     * @return mixed
     */
    public function getResultLine($lineNum, $line, $term)
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

        return $finalLine;
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
     *
     * @return mixed
     */
    abstract public function getResultOutput(Result $result);

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
     *
     * @return AbstractTemplate
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
}
