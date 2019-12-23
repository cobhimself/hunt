<?php


namespace Hunt\Bundle\Models;


use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Result
 *
 * Represents a Hunter result
 * @package Hunt\Bundle\Models
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
     * @var bool
     */
    private $trimResults;

    /**
     * Result constructor.
     *
     * @param string $term      The term which brought forth this result.
     * @param string $fileName  The filename where the term was found.
     * @param SplFileInfo $file Symfony's SplFileInfo object of the file
     */
    public function __construct(string $term, string $fileName, SplFileInfo $file)
    {
        $this->term = $term;
        $this->fileName = $fileName;
        $this->file = $file;
    }

    public function setTrimResultSpacing(bool $trim = true)
    {
        $this->trimResults = $trim;
    }

    /**
     * Opens our file and filters the content down to what we're hunting for.
     */
    public function filter()
    {
        $file = $this->file->openFile();
        $file->setFlags(\SplFileObject::SKIP_EMPTY);

        foreach ($file as $num => $line) {
           if (strpos($line, $this->term) !== false) {
               $this->matchingLines[$num] = ($this->trimResults) ? ltrim($line) : $line;
           }
        }
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getMatches()
    {
        return $this->matchingLines;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getLongestLineNumLength()
    {
        return max(array_map('strlen', array_keys($this->matchingLines)));
    }
}