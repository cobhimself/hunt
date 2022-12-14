<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\ParsedLine;
use Hunt\Bundle\Models\Result;
use Hunt\Component\MatchContext\ContextCollectorFactory;

use Hunt\Component\Trimmer;
use InvalidArgumentException;
use RuntimeException;

abstract class AbstractGatherer implements GathererInterface
{
    /**
     * The term we are searching for.
     *
     * @var string
     */
    protected $term;

    /**
     * @var int
     *
     * @since 1.5.0
     */
    protected $numContextLines = 0;

    /**
     * A list of exclude terms.
     *
     * @var array
     */
    protected $exclude;

    /**
     * Whether or not to trim spaces from the beginning of matching lines.
     *
     * @var bool
     */
    protected $trimMatchingLines = false;

    /**
     * The line as we are working on it.
     *
     * @since 1.4.0
     *
     * @var Line|ParsedLine
     */
    protected $workingLine = '';

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $term, array $exclude = null)
    {
        if (empty($term)) {
            throw new InvalidArgumentException('You must specify a term!');
        }

        $this->term = $term;
        $this->exclude = (is_array($exclude)) ? $exclude : [];
    }

    /**
     * Whether or not the given line matches.
     *
     * @since 1.5.0
     *
     * @param string $line
     *
     * @return bool
     */
    abstract public function lineMatches(int $lineNum, string $line): bool;

    /**
     * Take our line and split it up into parts.
     *
     * @param Line $line The line we want to parse.
     *
     * @return ParsedLine
     */
    abstract public function getParsedLine(Line $line): ParsedLine;

    /**
     * Gather a set of matching lines from the Result's file.
     *
     * @throws RuntimeException
     *
     * @return bool true if we still have matches, false otherwise
     */
    public function gather(Result $result): bool
    {
        $matchingLines = [];
        $contextCollector = ContextCollectorFactory::get($this->getNumContextLines());

        foreach ($result->getFileIterator() as $num => $lineStr) {

            //Our code starts at line 1, unlike our arrays.
            $codeLineNum = $num + 1;
            $testLine    = $lineStr;

            if (null !== $this->exclude && is_array($this->exclude)) {
                foreach ($this->exclude as $excludeTerm) {
                    $testLine = str_replace($excludeTerm, '', $testLine);
                }
            }

            $lineMatches = $this->lineMatches($codeLineNum, $testLine);

            if ($this->doTrimMatchingLines()) {
                $lineStr = trim($lineStr);
            }

            $line = LineFactory::getLine($codeLineNum, $lineStr);

            if ($lineMatches) {
                $result->addMatchingLine($line);
            }

            $contextCollector->addLine($line, $lineMatches);
        }

        $contextCollector->finalize();

        $result->setContextCollection($contextCollector->getContextCollection());

        return $result->getNumMatches() > 0;
    }

    /**
     * For each result line, parse them into their final parts.
     *
     * @param Result $result
     */
    public function parseResultLines(Result $result)
    {
        $parsedLines = [];

        foreach($result->getMatchingLines() as $lineNum => $line) {
            $parsedLines[$lineNum] = $this->getParsedLine($line);
        }

        $result->setMatchingLines($parsedLines);
    }

    /**
     * @since 1.5.0
     *
     * @param int $numContextLines The number of lines, before and after, we want to provide alongside our matching lines.
     *
     * @return GathererInterface
     */
    public function setNumContextLines(int $numContextLines): GathererInterface
    {
        $this->numContextLines = $numContextLines;

        return $this;
    }

    /**
     * Get the number of lines we want to provide before and after our matching line.
     *
     * @since 1.5.0
     */
    public function getNumContextLines(): int
    {
        return $this->numContextLines;
    }

    /**
     * Set whether or not we should trim the whitespace around our matching lines.
     */
    public function setTrimMatchingLines(bool $trimMatchingLines): GathererInterface
    {
        $this->trimMatchingLines = $trimMatchingLines;

        return $this;
    }

    /**
     * @return bool
     */
    public function doTrimMatchingLines(): bool
    {
        return $this->trimMatchingLines;
    }
}
