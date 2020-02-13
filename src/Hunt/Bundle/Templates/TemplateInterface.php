<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Symfony\Component\Console\Output\OutputInterface;

interface TemplateInterface
{
    /**
     * Initialize our template.
     *
     * If extending, this parent method MUST be called.
     */
    public function init(ResultCollection $result, OutputInterface $output): TemplateInterface;

    /**
     * Whether or not to highlight the term within the results.
     */
    public function highlight(bool $highlight = true);

    /**
     * Get an array of rendered result lines.
     *
     * This method is useful when you want to change how the group of term results is rendered.
     */
    public function getTermResults(Result $result): array;

    /**
     * Returns an individual term result line.
     *
     * Override this method if you'd like to modify how each individual term result appears in the result list.
     */
    public function getResultLine(string $lineNum, string $line, string $term): string;

    /**
     * Return whether or not we are going to highlight our search term.
     */
    public function doHighlight(): bool;

    /**
     * Return the line number formatted.
     */
    public function getLineNumber(string $lineNum): string;

    /**
     * Returns the rendered filename.
     *
     * Override this method if you'd like to style the filename differently.
     */
    public function getFilename(Result $result): string;

    /**
     * Returns the rendered term Result.
     *
     * This class should call the following methods to construct the final result "Row":
     *
     *  - getTermResults
     *  - getFilename
     */
    public function getResultOutput(Result $result): string;

    /**
     * Set the header string to be output before the template.
     *
     * No new line is automatically added.
     */
    public function setHeader(string $header);

    /**
     * If necessary, provide a header here.
     */
    public function getHeader(): string;

    /**
     * Set the header string to be output before the template.
     *
     * No new line is automatically added.
     */
    public function setFooter(string $footer);

    /**
     * If necessary, provide a footer here.
     */
    public function getFooter(): string;

    /**
     * Renders a single result and adds it to the body output.
     *
     * Useful for iterating through result collections and having the body output be compiled.
     */
    public function renderResult(Result $result);

    /**
     * Return the currently rendered body output.
     */
    public function getBodyOutput(): string;

    /**
     * Returns the rendered template output.
     *
     * NOTE: You should render the body of the template using renderResult before attempting to get the output.
     */
    public function getOutput(): string;

    /**
     * Process our context lines to conform to our template.
     *
     * @since 1.5.0
     *
     * @param array $lines An array of lines we should append our context lines to.
     * @param array $contextLines An array containing context lines for a match.
     */
    public function processContextLines(array &$lines, array $contextLines);

    /**
     * Add lines to be placed before the context lines of a matching result.
     *
     * @since 1.5.0
     *
     * @param array $lines The array of lines to add to.
     */
    public function getContextSplitBefore(array &$lines);

    /**
     * Add lines to be placed before the context lines of a matching result.
     *
     * @since 1.5.0
     *
     * @param array $lines The array of lines to add to.
     */
    public function getContextSplitAfter(array &$lines);

    /**
     * Tells the template whether or not to show context lines.
     *
     * @since 1.5.0
     */
    public function setShowContext(bool $showContext): TemplateInterface;

    /**
     * Get whether or not we should worry about outputting context lines.
     *
     * @since 1.5.0
     */
    public function getShowContext(): bool;
}
