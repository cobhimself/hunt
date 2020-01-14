<?php

namespace Hunt\Component\Gatherer;

use Hunt\Bundle\Models\Result;
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
     * @var string
     */
    protected $workingLine = '';

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $term, array $exclude = null)
    {
        if (empty($term)) {
            throw new \InvalidArgumentException('You must specify a term!');
        }

        $this->term = $term;
        $this->exclude = (is_array($exclude)) ? $exclude : [];
    }

    /**
     * Gather a set of matching lines from the Result's file.
     *
     * @throws RuntimeException
     *
     * @return bool true if we still have matches, false otherwise
     */
    public function gather(Result $result): bool
    {
        //replace with custom gather functionality
        throw new RuntimeException('The gather method must be extended!');
    }

    /**
     * Returns the given line with the term highlighted.
     *
     * Excluded terms are not highlighted.
     *
     * @throws RuntimeException
     */
    public function getHighlightedLine(string $line, string $highlightStart = '', string $highlightEnd = ''): string
    {
        throw new RuntimeException('The getHighlightedLine method must be extended!');
    }

    /**
     * Set whether or not we want this gatherer to trim whitespace from the beginning of matching lines.
     *
     * @return AbstractGatherer
     */
    public function setTrimMatchingLines(bool $trim = true): GathererInterface
    {
        $this->trimMatchingLines = $trim;

        return $this;
    }

    /**
     * Get whether or not we want to trim spaces from the beginning of the matching lines.
     */
    public function getTrimMatchingLines(): bool
    {
        return $this->trimMatchingLines;
    }

    /**
     * We replace our excluded words with placeholders so our highlight matches ignore them.
     *
     * @return array an array of translations we used with our placeholders
     */
    protected function removeExcludedTerms(): array
    {
        //Exit early if we can.
        if (0 === count($this->exclude)) {
            return [];
        }

        //We could use regex but it's possible the complexity would cause the search to take a long time. Therefore,
        //we are going to replace our exclude terms with placeholders, highlight our original term, and then put our
        //exclude terms back.
        static $placeholder = "\u{731f}\u{5e2b}";
        $counter = 0;

        //We need to build a translation between our exclude terms and the placeholders
        $translate = [];
        foreach ($this->exclude as $exclude) {
            ++$counter;
            $translate[$exclude] = str_repeat($placeholder, $counter);
        }
        $this->workingLine = strtr($this->workingLine, $translate);

        return $translate;
    }

    /**
     * Add our excluded terms back based on the given translation array.
     *
     * @param array $translate we flip this translation array since we assume it was generated by removeExcludedTerms
     */
    protected function addExcludedTermsBack(array $translate): string
    {
        //Exit early if we can
        if (0 === count($this->exclude)) {
            return $this->workingLine;
        }

        //Reverse our placeholder translation
        $translate = array_flip($translate);

        return strtr($this->workingLine, $translate);
    }
}
