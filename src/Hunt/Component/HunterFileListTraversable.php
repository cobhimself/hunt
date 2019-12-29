<?php

namespace Hunt\Component;


use Generator;
use Hunt\Bundle\Models\Result;
use IteratorAggregate;
use Symfony\Component\Finder\Finder;

class HunterFileListTraversable implements IteratorAggregate
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * The search term.
     *
     * @var string
     */
    private $term;

    public function __construct(array $baseDir, string $term, bool $recurse)
    {
        $finder = new Finder();
        $finder->files()->in($baseDir);

        if (!$recurse) {
            $finder->depth('== 0');
        }
        $finder->contains($term);

        $this->finder = $finder;
        $this->term = $term;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Generator An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        foreach ($this->finder as $file) {
            $path = $file->getRelativePath();

            yield new Result($this->term, $path, $file);
        }
    }
}
