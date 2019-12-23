<?php


namespace Hunt\Bundle\Templates;


use Hunt\Bundle\Models\Result;
use Hunt\Bundle\Models\ResultCollection;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTemplate
{
    const HIGHLIGHT_START = '*';
    const HIGHLIGHT_END = '*';

    protected $finalOutput = '';

    /**
     * @var ResultCollection $resultCollection
     */
    protected $resultCollection;
    /**
     * Whether or not to highlight the term in the rendered output.
     *
     * @var bool
     */
    protected $highlightTerm;

    /**
     * @var OutputInterface|null
     */
    protected $output;

    /**
     * AbstractTemplate constructor.
     *
     * @param ResultCollection $resultCollection
     * @param OutputInterface|null $output
     */
    public function __construct(ResultCollection $resultCollection, OutputInterface $output = null)
    {
        $this->resultCollection = $resultCollection;
        $this->output = $output;

        $this->init();
    }

    /**
     * Perform necessary actions before rendering the template.
     */
    public function init()
    {
        //nop
    }

    /**
     * Whether or not to highlight the term within the results.
     *
     * @param bool $highlight
     */
    public function highlight(bool $highlight = true)
    {
        $this->highlightTerm = $highlight;
    }

    /**
     * Get an array of rendered result lines.
     *
     * This method is useful when you want to change how the group of term results is rendered.
     *
     * @param Result $result
     * @return array
     */
    public function getTermResults(Result $result): array
    {
        $this->preGetTermResults($result);

        $lines = [];
        $term = $result->getTerm();

        foreach ($result->getMatches() as $lineNum => $line) {
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
     * @return mixed
     */
    public function getResultLine($lineNum, $line, $term)
    {
        $finalLine = $this->getLineNumber($lineNum) . ': ';
        if ($this->highlightTerm) {
            $finalLine .= str_replace(
                $term,
                self::HIGHLIGHT_START . $term . self::HIGHLIGHT_END,
                $line
            );
        } else {
            $finalLine .= $line;
        }

        return $finalLine;
    }

    /**
     * Return the line number formatted.
     *
     * @param string $lineNum
     * @return string
     */
    protected function getLineNumber(string $lineNum): string
    {
        return $lineNum;
    }

    /**
     * Returns the rendered filename.
     *
     * Override this method if you'd like to style the filename differently.
     *
     * @param Result $result
     * @return string
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
     * @param Result $result
     * @return mixed
     */
    abstract public function getResultOutput(Result $result);

    /**
     * If necessary, provide a header here.
     *
     * @return string
     */
    public function getHeader()
    {
        return '';
    }

    /**
     * If necessary, provide a footer here.
     *
     * @return string
     */
    public function getFooter()
    {
        return '';
    }

    /**
     * Generates the final output for the template.
     */
    public function renderAll()
    {
        foreach($this->resultCollection as $result) {
            $this->finalOutput .= $this->getResultOutput($result);
        }
    }

    public function renderResult(Result $result) {
        $this->finalOutput .= $this->getResultOutput($result);
    }

    /**
     * Returns the rendered template output.
     *
     * NOTE: You should render this template before attempting to get the output.
     *
     * @return string
     */
    public function getOutput()
    {
        if (null !== $this->output) {
            $this->output->write($this->getHeader());
            $this->output->write($this->finalOutput);
            $this->output->write($this->getFooter());
        }
    }

    /**
     * Run before we get the term results for the file.
     *
     * @param Result $result
     */
    protected function preGetTermResults(Result $result)
    {
        //nop
    }
}