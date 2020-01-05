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
        $matchingLines = '{noformat:nopanel=true}' . implode(PHP_EOL, $this->getTermResults($result)) . '{noformat}';

        return '|' . $statusText . '|' . $filename . '|' . $matchingLines . '|' . PHP_EOL;
    }
}
