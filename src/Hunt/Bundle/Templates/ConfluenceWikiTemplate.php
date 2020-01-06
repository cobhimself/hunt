<?php

namespace Hunt\Bundle\Templates;


use Hunt\Bundle\Models\Result;

class ConfluenceWikiTemplate extends AbstractTemplate
{
    protected $highlightStart = '';
    protected $highlightEnd = '';

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
    public function getResultOutput(Result $result)
    {
        $statusText = '{status:title= |color=red}';
        $filename = $this->getFileName($result);
        $resultLines = implode(PHP_EOL, $this->getTermResults($result));
        $matchingLines = '{noformat:nopanel=true}' . PHP_EOL . $resultLines . PHP_EOL . '{noformat}';

        return '|' . $statusText . '|' . $filename . '|' . $matchingLines . '|' . PHP_EOL;
    }

    /**
     * We need to escape the pipe character since it's important for our output.
     */
    public function getResultLine(string $lineNum, string $line, string $term): string
    {
        $line = parent::getResultLine($lineNum, $line, $term);

        return str_replace('|', '\|', $line);
    }
}
