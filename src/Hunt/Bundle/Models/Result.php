<?php


namespace Hunt\Bundle\Models;


use SplFileObject;
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

    /**
     * Whether or not to trim the left spaces from the result lines.
     *
     * @param bool $trim
     */
    public function setTrimResultSpacing(bool $trim = true)
    {
        $this->trimResults = $trim;
    }

    /**
     * Opens our file and filters the content down to what we're hunting for.
     * @param array|null $exclude If provided, contains an array of terms we do not want included, even if our main
     *                            term matches.
     *
     * @return bool True if we still have matches, false otherwise.
     */
    public function filter(array $exclude = null): bool
    {
        $file = $this->file->openFile();
        $file->setFlags(SplFileObject::SKIP_EMPTY);

        foreach ($file as $num => $line) {
            $testLine = $line;
            if ($exclude !== null && is_array($exclude)) {
                foreach ($exclude as $excludeTerm) {
                    $testLine = str_replace($excludeTerm, '', $testLine);
                }
            }

            if (strpos($testLine, $this->term) !== false) {
                $this->matchingLines[$num] = ($this->trimResults) ? ltrim($line) : $line;
            }
        }

        return count($this->matchingLines) > 0;
    }

    /**
     * Return the filename associated with this result.
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Return a list of matching lines within the Result's file.
     *
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matchingLines;
    }

    /**
     * Get the number of matching lines we have.
     *
     * @return int The number of matches.
     */
    public function getNumMatches(): int
    {
        return count($this->matchingLines);
    }

    /**
     * Return the search term associated with this Result.
     *
     * @return string
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * Get the length of the longest line number in the result's matching lines.
     *
     * @return int
     */
    public function getLongestLineNumLength(): int
    {
        return count($this->matchingLines) > 0
            ? max(array_map('strlen', array_keys($this->matchingLines)))
            : 0;
    }
}