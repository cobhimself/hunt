<?php


namespace Hunt\Bundle\Templates;


use Hunt\Bundle\Models\Result;

class ConsoleTemplate extends AbstractTemplate
{
    private $longestFilenameLength = 0;
    /**
     * Updated to contain the longest line number in a file's term result list.
     *
     * @var int
     */
    private $longestLineNum = 0;

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
    public function getResultOutput(Result $result)
    {
        static $filenameSeparator = ': ';
        $output = "\n";

        //First, let's construct the filepath and pad it to the length of our longest filename
        $output .= str_pad($result->getFileName(), $this->longestFilenameLength, " ", STR_PAD_LEFT);

        $output .= $filenameSeparator;

        $resultLines = $this->getTermResults($result);

        $output .= array_shift($resultLines);

        //Do we have any additional results?
        if (count($resultLines) > 0)
        {
            foreach ($resultLines as $line)
            {
                //Pad our result filename
                $output .= str_repeat(' ', $this->longestFilenameLength + strlen($filenameSeparator));

                $output .= $line;
            }
        }

        return $output;
    }

    /**
     * @param string $lineNum
     * @return string
     */
    protected function getLineNumber(string $lineNum): string
    {
        return str_pad($lineNum, $this->longestLineNum, ' ', STR_PAD_LEFT);
    }

    public function init()
    {
        $this->longestFilenameLength = $this->resultCollection->getLongestFilenameLength();
        $this->longestLineNum = $this->resultCollection->getLongestLineNumInResults();
        $this->highlight();
    }
}