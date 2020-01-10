<?php

namespace Hunt\Component;

use Generator;
use Hunt\Bundle\Models\Result;
use IteratorAggregate;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

    public function __construct(Hunter $hunter)
    {
        $files = [];
        $dirs = [];

        //Separate files and folders in our baseDir
        foreach ($hunter->getBaseDir() as $path) {
            if (is_dir($path)) {
                $dirs[] = $path;
            } elseif (is_file($path)) {
                $files[] = new \SplFileInfo($path);
            } else {
                throw new \InvalidArgumentException($path . ' is not a valid directory or file path');
            }
        }

        $finder = (new Finder())
            ->files()
            ->in($dirs)
            ->append($files);

        if (!$hunter->isRecursive()) {
            $finder->depth('== 0');
        }

        if (!empty($hunter->getExcludeDirs())) {
            foreach ($hunter->getExcludeDirs() as $dir) {
                $finder->notPath($dir);
            }
        }

        if (!empty($hunter->getExcludeFileNames())) {
            foreach ($hunter->getExcludeFileNames() as $name) {
                $finder->notName($name);
            }
        }

        if (!empty($hunter->getMatchPath())) {
            foreach ($hunter->getMatchPath() as $requiredMatch) {
                $finder->path($requiredMatch);
            }
        }

        $finder->contains($hunter->getTerm());

        $this->finder = $finder;
        $this->term = $hunter->getTerm();
    }

    /**
     * Retrieve an external iterator.
     *
     * @see https://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Generator An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        foreach ($this->finder as $file) {
            if ($file instanceof SplFileInfo) {
                /**
                 * @var SplFileInfo
                 */
                $path = $file->getRelativePathName();
            } else {
                /**
                 * @var \SplFileInfo
                 */
                $path = $file->getPathname();
            }

            yield new Result($this->term, $path, $file);
        }
    }
}
