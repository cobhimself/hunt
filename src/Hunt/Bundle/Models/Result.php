<?php

namespace Hunt\Bundle\Models;

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
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var array
     */
    private $matchingLines = [];

    /**
     * Result constructor.
     *
     * @param string      $term     the term which brought forth this result
     * @param string      $fileName the filename where the term was found
     * @param SplFileInfo $file     Symfony's SplFileInfo object of the file
     */
    public function __construct(string $term, string $fileName, SplFileInfo $file)
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
     * Get the length of the longest line number in the result's matching lines.
     */
    public function getLongestLineNumLength(): int
    {
        return count($this->matchingLines) > 0
            ? max(array_map('strlen', array_keys($this->matchingLines)))
            : 0;
    }

    /**
     * Return the file associated with this result.
     */
    public function getFile(): SplFileInfo
    {
        return $this->file;
    }

    /**
     * Set the matching lines for this result.
     */
    public function setMatchingLines(array $matchingLines): Result
    {
        $this->matchingLines = $matchingLines;

        return $this;
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
