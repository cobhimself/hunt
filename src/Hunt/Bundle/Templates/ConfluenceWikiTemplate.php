<?php

namespace Hunt\Bundle\Templates;

use Hunt\Bundle\Models\Element\Line\LineInterface;
use Hunt\Bundle\Models\Result;
use const PHP_EOL;

class ConfluenceWikiTemplate extends AbstractTemplate
{
    protected $doHighlight = true;

    /**
     * Returns the rendered term Result.
     *
     * This class should call the following methods to construct the final result "Row":
     *
     *  - getTermResults
     *  - getFilename
     */
    public function getResultOutput(Result $result): string
    {
        $statusText = '{status:title= |color=red}';
        $filename = $this->getFileName($result);
        $matchingLines = '';
        $resultLines = implode(PHP_EOL, $this->getTermResults($result));

        //If we aren't showing context lines, we'll put everything in a single panel.
        if (!$this->getShowContext()) {
            $matchingLines = '{noformat:nopanel=true}' . PHP_EOL;
        }

        $matchingLines .= $resultLines;

        //If we're not showing context lines, we'll close the panel we created
        if (!$this->getShowContext()) {
            $matchingLines .= PHP_EOL . '{noformat}';
        }

        return '|' . $statusText . '|' . $filename . '|' . $matchingLines . '|' . PHP_EOL;
    }

    /**
     * Returns an individual term result line.
     *
     * We need to escape the pipe character since it's important for our output.
     */
    public function getResultLine(LineInterface $line): string
    {
        $line = parent::getResultLine($line);

        return str_replace('|', '\|', $line);
    }

    /**
     * Add lines to be placed before the context lines of a matching result.
     *
     * @since 1.5.0
     * @param array $lines The array of lines to add to.
     */
    public function getContextSplitBefore(array &$lines)
    {
        $lines[] = '{noformat:nopanel=true}';
    }

    /**
     * Add lines to be placed before the context lines of a matching result.
     *
     * @since 1.5.0
     * @param array $lines The array of lines to add to.
     */
    public function getContextSplitAfter(array &$lines)
    {
        $lines[] = '{noformat}';
    }
}
