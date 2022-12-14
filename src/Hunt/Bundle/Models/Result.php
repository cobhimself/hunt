<?php

namespace Hunt\Bundle\Models;

use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\MatchContext\DummyMatchContextCollection;
use Hunt\Bundle\Models\MatchContext\MatchContextCollectionInterface;
use Hunt\Component\Trimmer;
use SplFileObject;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Result.
 *
 * Represents a Hunter result
 */
class Result
{
    /**
     * @var string
     */
    private $term;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var \SplFileInfo
     */
    private $file;

    /**
     * @var MatchContextCollectionInterface
     * @since 1.5.0
     */
    private $contextCollection;

    /**
     * @var array
     */
    private $matchingLines = [];

    /**
     * Result constructor.
     *
     * @param string       $term     the term which brought forth this result
     * @param string       $fileName the filename where the term was found
     * @param \SplFileInfo $file     Symfony's SplFileInfo object of the file
     *
     * @codeCoverageIgnore
     */
    public function __construct(string $term, string $fileName, \SplFileInfo $file)
    {
        $this->term = $term;
        $this->fileName = $fileName;
        $this->file = $file;
    }

    /**
     * Return the filename associated with this result.
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Return a list of matching lines within the Result's file.
     */
    public function getMatchingLines(): array
    {
        return $this->matchingLines;
    }

    /**
     * Get the number of matching lines we have.
     *
     * @return int the number of matches
     */
    public function getNumMatches(): int
    {
        return count($this->matchingLines);
    }

    /**
     * Return the search term associated with this Result.
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * Trim all of the matching lines as well as context lines if applicable.
     *
     * @since 1.5.0
     */
    public function trimResults()
    {
        //Nothing to trim if we don't have matches.
        if (count($this->matchingLines) === 0) {
            return;
        }

        $contextCollection = $this->getContextCollection();
        //If we don't have context line, just trim our matching line.
        if (
            !$contextCollection->addsContext()
            || $contextCollection->getCollectionSize() === 0
        ) {
            foreach ($this->matchingLines as $lineNum => $line) {
                $this->matchingLines[$lineNum] = ltrim($line);
            }

            return;
        }

        //If we do have context lines, we need to attempt to keep the spacing the same between lines but remove a common
        //amount of spaces.
        foreach ($this->matchingLines as $lineNum => $line) {
            $context = $contextCollection->getContextForLine($lineNum);
            $tempLines = array_merge($context->getBefore(), [$lineNum => $line], $context->getAfter());
            $spacesToTrim = Trimmer::getShortestLeadingSpaces($tempLines);
            if ($spacesToTrim > 0) {
                $context->setBefore(Trimmer::trim($context->getBefore(), $spacesToTrim));
                $context->setAfter(Trimmer::trim($context->getAfter(), $spacesToTrim));
                $this->matchingLines[$lineNum] = Trimmer::trim($this->matchingLines[$lineNum], $spacesToTrim);
            }
        }
    }

    /**
     * Get the length of the longest line number in the result's matching lines.
     */
    public function getLongestLineNumLength(): int
    {
        if (count($this->matchingLines) === 0) {
            return 0;
        }

        $longestMatchLine = max(array_map('strlen', array_keys($this->matchingLines)));
        $longestContextLine = $this->getContextCollection()->getLongestLineNumberLength();

        return max($longestMatchLine, $longestContextLine);
    }

    /**
     * Return the file associated with this result.
     */
    public function getFile(): \SplFileInfo
    {
        return $this->file;
    }

    /**
     * Set the matching lines for this result.
     * @param Line[] $matchingLines
     *
     * @return Result
     */
    public function setMatchingLines(array $matchingLines): Result
    {
        $this->matchingLines = $matchingLines;

        return $this;
    }

    /**
     * Add a line which contains a match with our term.
     *
     * @param Line $line
     *
     * @return Result
     */
    public function addMatchingLine(Line $line): Result
    {
        $this->matchingLines[$line->getLineNumber()] = $line;

        return $this;
    }

    /**
     * Set the context collection for this result set.
     *
     * @since 1.5.0
     */
    public function setContextCollection(MatchContextCollectionInterface $contextCollection): Result
    {
        $this->contextCollection = $contextCollection;

        return $this;
    }

    /**
     * Get the context collection for this result set.
     *
     * @since 1.5.0
     */
    public function getContextCollection(): MatchContextCollectionInterface
    {
        return $this->contextCollection ?? new DummyMatchContextCollection();
    }

    /**
     * Return the Result's file for iteration after setting up the flags.
     */
    public function getFileIterator(): SplFileObject
    {
        $file = $this->getFile()->openFile();
        $file->setFlags(SplFileObject::SKIP_EMPTY);

        return $file;
    }
}
