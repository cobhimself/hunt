<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;

interface TemplateInterface
{
    /**
     * AbstractTemplate constructor.
     *
     * @param ResultCollection $resultCollection
     */
    public function __construct(ResultCollection $resultCollection);

    /**
     * Perform necessary actions before rendering the template.
     */
    public function init();

    /**
     * Whether or not to highlight the term within the results.
     *
     * @param bool $highlight
     */
    public function highlight(bool $highlight = true);

    /**
     * Get an array of rendered result lines.
     *
     * This method is useful when you want to change how the group of term results is rendered.
     *
     * @param Result $result
     *
     * @return array
     */
    public function getTermResults(Result $result): array;

    /**
     * Returns an individual term result line.
     *
     * Override this method if you'd like to modify how each individual term result appears in the result list.
     *
     * @param $lineNum
     * @param $line
     * @param $term
     * @return mixed
     */
    public function getResultLine($lineNum, $line, $term);

    /**
     * Return whether or not we are going to highlight our search term.
     *
     * @return bool
     */
    public function doHighlight(): bool;

    /**
     * Return the line number formatted.
     *
     * @param string $lineNum
     * @return string
     */
    public function getLineNumber(string $lineNum): string;

    /**
     * Returns the rendered filename.
     *
     * Override this method if you'd like to style the filename differently.
     *
     * @param Result $result
     * @return string
     */
    public function getFilename(Result $result): string;

    /**
     * Returns the rendered term Result.
     *
     * This class should call the following methods to construct the final result "Row":
     *
     *  - getTermResults
     *  - getFilename
     *
     * @param Result $result
     * @return mixed
     */
    public function getResultOutput(Result $result);

    /**
     * Set the header string to be output before the template.
     *
     * No new line is automatically added.
     *
     * @param string $header
     */
    public function setHeader(string $header);

    /**
     * If necessary, provide a header here.
     *
     * @return string
     */
    public function getHeader(): string;

    /**
     * Set the header string to be output before the template.
     *
     * No new line is automatically added.
     *
     * @param string $footer
     */
    public function setFooter(string $footer);

    /**
     * If necessary, provide a footer here.
     *
     * @return string
     */
    public function getFooter(): string;

    /**
     * Renders a single result and adds it to the body output.
     *
     * Useful for iterating through result collections and having the body output be compiled.
     *
     * @param Result $result
     */
    public function renderResult(Result $result);

    /**
     * Return the currently rendered body output.
     *
     * @return string
     */
    public function getBodyOutput(): string;

    /**
     * Returns the rendered template output.
     *
     * NOTE: You should render the body of the template using renderResult before attempting to get the output.
     *
     * @return string
     */
    public function getOutput(): string;
}
