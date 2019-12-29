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
        $output = '';

        //First, let's construct the filepath and pad it to the length of our longest filename
        $output .= str_pad(
            $result->getFileName(),
            $this->longestFilenameLength,
            ' ',
            STR_PAD_LEFT
        );

        $output .= $filenameSeparator;

        $resultLines = $this->getTermResults($result);

        //Add our first match in line with the file name. Others will follow below it.
        $output .= array_shift($resultLines) . PHP_EOL;

        //Do we have any additional results?
        if (count($resultLines) > 0) {
            $leftPadding = $this->longestFilenameLength + strlen($filenameSeparator);
            foreach ($resultLines as $line) {
                //Pad our result filename so it lines up nicely with our first term result
                $output .= str_repeat(' ', $leftPadding);

                $output .= $line . PHP_EOL;
            }
        }

        return $output;
    }

    /**
     * Returns the formatted line number.
     *
     * @param string $lineNum
     * @return string
     */
    public function getLineNumber(string $lineNum): string
    {
        return str_pad($lineNum, $this->longestLineNum, ' ', STR_PAD_LEFT);
    }

    /**
     * Initializes variables we'll use in our final output.
     */
    public function init()
    {
        $this->longestFilenameLength = $this->resultCollection->getLongestFilenameLength();
        $this->longestLineNum = $this->resultCollection->getLongestLineNumInResults();
        if ($this->output->isDebug()) {
            $this->output->writeln(
                sprintf(
                    'Longest filename length: %d, Longest line number length: %d',
                    $this->longestFilenameLength,
                    $this->longestLineNum
                )
            );
        }
        $this->highlight();
    }
}
